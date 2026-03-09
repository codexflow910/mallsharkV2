<?php

namespace Packages\Distributors\Services\V3;

use App\Http\Controllers\MailgunController;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Auth\Access\AuthorizationException;
use Packages\Distributors\Config\DistributorsConstants;
use Packages\Distributors\Contracts\V3\DistributorHelpers;
use Packages\Distributors\Models\V3\BrandDistributor;
use Packages\Distributors\Repositories\Eloquent\V3\DistributorRepository;

class DistributorService
{	
	use DistributorHelpers;

	protected $distributorRepository;

	public function __construct(DistributorRepository $distributorRepository)
	{
		$this->distributorRepository = $distributorRepository;
	}

	
	public function list($id = null)
	{
		try {
			$userId = auth()->id();
			$store = DistributorHelpers::getAccessibleStore($userId);
			if (!$store) {
				throw new ItemNotFoundException('User does not belong or have access to store.');
			}

			$storeId = $store->store_id;
			$distributors = $this->distributorRepository->listDocumentsByStore($storeId, $id);

			if (!$distributors) {
				throw new ItemNotFoundException('Distributor documents not found for this store.');
			}

			foreach ($distributors as $distributor) {
				$documentIds = unserialize($distributor->documents) ?? [];

				// Fetch all document URLs in order
				$documentsUrls = DistributorHelpers::getDocumentsUrls($documentIds);

				// Combine id, status, and url in a structured array
				$documentsData = [];

				foreach ($distributor->distributorDocumentsStatuses as $index => $statusRecord) {
					$documentsData[] = [
						'id'     => $statusRecord->id,
						'status' => (int) $statusRecord->status,
						'url'    => $documentsUrls[$index] ?? null,
						'attachment_id'	 => (int) $statusRecord->attachment_id ?? null
					];
				}

				$distributor->documents = $documentsData;
				$distributor->brand_id = $distributor->brandDistributor->brand->id ?? null;
				$distributor->distributor_logo_url = DistributorHelpers::getAttachmentUrl((int) $distributor->distributor_logo);

				unset($distributor->distributorDocumentsStatuses, $distributor->brandDistributor);
			}

			return $distributors;
		} catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException($e->getMessage());
        } catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}

	public function create($data)
	{
		try {
			DB::beginTransaction();
			
			$userId = auth()->id();
			$store = DistributorHelpers::getAccessibleStore($userId);
			if (!$store) {
				throw new ItemNotFoundException('User does not belong or have access to store.');
			}

			$storeId = $store->store_id;
			$sellerId = $store->user_id;
			$storeName = $store->store_name ?? 'Mallshark';
			$brandUrl = strtolower(trim(DistributorHelpers::cleanDomain($data['brand_url'])));

			$assignedBrands = $this->distributorRepository->getAssignedBrandsByStoreId($storeId);

			// Get pending URLs from store’s submitted docs
			$pendingBrandsUrls = $this->distributorRepository
				->listDocumentsByStore($storeId)
				->filter(fn ($doc) => $doc->status == DistributorsConstants::PENDING)
				->pluck('brand_url')
				->map(fn ($url) => strtolower(trim($url)))
				->toArray();

			// Assigned (or approved) brand URLs
			$assignedBrandUrls = $assignedBrands
				->pluck('distributorDocuments')
				->flatten()
				->filter(fn ($doc) => $doc && $doc->status != DistributorsConstants::REJECTED)
				->pluck('brand_url')
				->map(fn ($url) => strtolower(trim($url)))
				->toArray();

			// Merge both lists (pending + assigned but not rejected)
			$allAppliedUrls = array_unique(array_merge($assignedBrandUrls, $pendingBrandsUrls));

			if (in_array(strtolower(trim($brandUrl)), $allAppliedUrls)) {
				throw new Exception("You are already a distributor of this brand or have already applied.");
			}

			$logoId = null;
			$documentIds = [];

			// ✅ Handle logo upload
			if (!empty($data['distributor_logo'])) {
				$logoId = $this->distributorRepository->uploadImage($data['distributor_logo']);
			}

			// ✅ Handle document uploads
			if (!empty($data['documents'])) {
				$documents = is_array($data['documents']) ? $data['documents'] : [$data['documents']];
				foreach ($documents as $document) {
					$documentId = $this->distributorRepository->uploadImage($document);
					if ($documentId) {
						$documentIds[] = $documentId;
					}
				}
			}

			// ✅ Create record
			$result = $this->distributorRepository->create($data, $storeId, $logoId, $documentIds);

			if (!$result) {
				throw new Exception('Distributor data has not been saved.');
			}

			// ========== Notification ==========
			$subject = DistributorsConstants::DISTRIBUTOR_CREATE_REQUEST;
            DistributorHelpers::pushNotification($sellerId, $subject, 'seller');

			$requestId = $result->id;
			$distributorUrl = env('APP_URL') . '/admin/distributor/detail/' . $requestId;
			$this->sendDistributorRequestEmail($subject, $distributorUrl, $storeName);
			
			DB::commit();

			return $result;

		} catch (ItemNotFoundException $e) {
			DB::rollBack();
			throw new ItemNotFoundException($e->getMessage());
		} catch (Exception $e) {
			DB::rollBack();
			throw new Exception($e->getMessage());
		}
	}

	public function update($data, $id)
	{
		DB::beginTransaction();
		try {
			$userId = auth()->id();
			$store = DistributorHelpers::getAccessibleStore($userId);
			if (!$store) {
				throw new ItemNotFoundException('User does not belong or have access to store.');
			}

			$storeId = $store->store_id;
			$sellerId = $store->user_id;
			$storeName = $store->store_name ?? 'Mallshark';
			$brandUrl = strtolower(trim(DistributorHelpers::cleanDomain($data['brand_url'])));

			$assignedBrands = $this->distributorRepository->getAssignedBrandsByStoreId($storeId);

			// Get assigned + approved (skip rejected and skip current doc)
			$assignedBrandUrls = $assignedBrands
				->pluck('distributorDocuments')
				->flatten()
				->filter(function ($doc) use ($id) {

					// Skip the current document during update
					if ($doc && $doc->id == $id) {
						return false;
					}

					// Skip REJECTED documents only
					return $doc && $doc->status != DistributorsConstants::REJECTED;
				})
				->pluck('brand_url')
				->map(fn ($url) => strtolower(trim($url)))
				->toArray();

			// Get pending brand URLs (also skip current doc)
			$pendingBrandsUrls = $this->distributorRepository
				->listDocumentsByStore($storeId)
				->filter(function ($doc) use ($id) {
					return $doc->status == DistributorsConstants::PENDING
						&& $doc->id != $id;  
				})
				->pluck('brand_url')
				->map(fn ($url) => strtolower(trim($url)))
				->toArray();

			// Merge assigned + pending
			$allAppliedUrls = array_unique(array_merge($assignedBrandUrls, $pendingBrandsUrls));

			$brandUrl = strtolower(trim($data['brand_url']));

			if (in_array($brandUrl, $allAppliedUrls)) {
				throw new Exception("You are already a distributor of this brand or have already applied.");
			}

			$document = DistributorHelpers::getDistributorDocs($id);
			if (!$document) {
				throw new ItemNotFoundException('Distributor documents not found');
			}

			if ($document->distributor_id != $storeId) {
				throw new AuthorizationException('Not authorized to process this action');
			}

			$oldLogoId     = $document->distributor_logo;
			$oldDocuments  = unserialize($document->documents) ?? [];
			$deletedDocs   = $data['deleted_documents'] ?? [];

			// --- Handle Logo Upload ---
			$uploadedLogoId = null;
			$logoId = $data['distributor_logo'] ?? null;
			if ($logoId && !is_numeric($logoId)) {
				$uploadedLogoId = $this->distributorRepository->uploadImage($logoId);
				if (!$uploadedLogoId) {
					throw new Exception('Logo upload failed');
				}
				$logoId = $uploadedLogoId;
			}

			// --- Handle Documents ---
			$newDocuments = [];
			if (!empty($data['documents'])) {
				foreach ($data['documents'] as $doc) {
					if ($doc instanceof \Illuminate\Http\UploadedFile) {
						$docId = $this->distributorRepository->uploadImage($doc);
						if (!$docId) {
							throw new Exception('Document upload failed');
						}
						$newDocuments[] = $docId;
						DistributorHelpers::updateDistributorDocumentStatus($id, $docId);
					} elseif (is_numeric($doc)) {
						$newDocuments[] = (int) $doc;
					}
				}
			}
			
			// --- Update Record ---
			$updatedDistributor = $this->distributorRepository->update($id, $data, $storeId, $logoId, $newDocuments);

			// ========== Notification ==========
			$subject = DistributorsConstants::DISTRIBUTOR_UPDATE_REQUEST;
            DistributorHelpers::pushNotification($sellerId, $subject, 'seller');

			$distributorUrl = env('APP_URL') . '/admin/distributor/detail/' . $id;
			$updated = true;

			$this->sendDistributorRequestEmail($subject, $distributorUrl, $storeName, $updated);

			DB::commit();
			
			// --- Delete old files after commit ---
			$deletedDocs = array_diff($oldDocuments, $newDocuments);
			if (!empty($deletedDocs)) {
				DistributorHelpers::deleteDocuments($deletedDocs);
				DistributorHelpers::deleteDocumentsStatuses($deletedDocs);
			}

			if ($uploadedLogoId && $oldLogoId && $oldLogoId != $uploadedLogoId) {
				DistributorHelpers::deleteDocuments([$oldLogoId]);
			}

			return $updatedDistributor->load('distributorDocumentsStatuses');

		} catch (Exception $e) {
			DB::rollBack();
			throw new Exception('Update failed: ' . $e->getMessage());
		}
	}

	public function delete($id)
	{
		DB::beginTransaction();

		try {
			$doc = $this->distributorRepository->getDistributorDetail($id);
			if (!$doc) {
				throw new \Exception("Distributor document not found.");
			}

			// Extract attachment IDs
			$documentIds = $doc->documents
				? unserialize($doc->documents)
				: [];

			// 1. Delete statuses
			DistributorHelpers::deleteDocumentsStatuses($documentIds);

			// 2. Delete pivot BrandDistributor if exists
			if (!empty($doc->brand_distributor_id)) {
				BrandDistributor::where('id', $doc->brand_distributor_id)->delete();
			}

			// 3. Delete main distributor document record
			$doc->delete();

			$filesDeleted = DistributorHelpers::deleteDocuments($documentIds);
			if (!$filesDeleted) {
				throw new \Exception("Failed to delete document files.");
			}

			DB::commit();
			return true;
		} catch (\Exception $e) {
			DB::rollBack();
			throw new \Exception("Distributor delete failed → " . $e->getMessage());
		}
	}

	public function changeDistributorStatus($distributorDocumentId, $status, $note)
	{
		try {
			$statusMap = [
				'pending'  => DistributorsConstants::PENDING,
				'approved' => DistributorsConstants::APPROVED,
				'rejected' => DistributorsConstants::REJECTED,
			];
			$status = $statusMap[strtolower($status)];

            $response = $this->distributorRepository->changeDistributorStatus($distributorDocumentId, $status, $note);

            return $response;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
	}

	public function changeDocumentStatus($documentId, $status)
	{
		try {
			DB::beginTransaction();
			$statusMap = [
				'pending'  => DistributorsConstants::PENDING,
				'approved' => DistributorsConstants::APPROVED,
				'rejected' => DistributorsConstants::REJECTED,
			];
			$status = $statusMap[strtolower($status)];

            $response = $this->distributorRepository->changeDocumentStatus($documentId, $status);

			if ($response) {

				$storeId = $response->distributorDocument->distributor_id ?? null;
				$documentUrl = $response->attachment_id ? DistributorHelpers::getAttachmentUrl((int) $response->attachment_id) : null;
				$resubmitUrl = env('SELLER_END_URL') . '/distributor/list/' . $response->distributor_document_id;
				$user = DistributorHelpers::getUserByStoreId($storeId);
				$sellerId = $user->user_id ?? null;

				if ($sellerId && $status == DistributorsConstants::REJECTED) {
					// ========== Notification ==========
					$subject = DistributorsConstants::DISTRIBUTOR_DOCUMENT_REJECT;
					DistributorHelpers::pushNotification($sellerId, $subject, 'seller');

					$sellerName = $user->name ?? 'Seller';
					$from = env('CARE_EMAIL');
					$htmlContent = view('mail.distributor-request-update', compact('documentUrl', 'sellerName', 'resubmitUrl'))->render();

					$params = [
						'from'    => $from,
						'to'      => $user->email,
						'subject' => $subject,
						'html'    => $htmlContent, 
					];
					$mailgun = new MailgunController();
				    $mailgun->SendMailgunMail($params);
				}
			}
			
			DB::commit();

            return $response;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
	}

	public function getAllDistributors($status = null)
	{
		try {
			$statusMap = [
				'pending'  => DistributorsConstants::PENDING,
				'active' => DistributorsConstants::APPROVED,
				'rejected' => DistributorsConstants::REJECTED,
			];
			if ($status && isset($statusMap[strtolower($status)])) {
				$status = $statusMap[strtolower($status)];
			} else {
				$status = DistributorsConstants::PENDING;
			}

			$distributors = $this->distributorRepository->getAllDistributors($status);

			$grouped = [];

			if ($status === DistributorsConstants::APPROVED) {
				foreach ($distributors as $distributor) {
					$store = $distributor->store;
					$storeName = $store->store_name ?? 'Mallshark';
					$storeLogoUrl = DistributorHelpers::getAttachmentUrlBySize((int) $store->store_logo, 'media');

					$brand = [
						'id'		  => $distributor->id,
						'brand_name'  => $distributor->brand_name,
						'brand_url'   => $distributor->brand_url,
						'logo_url'    => DistributorHelpers::getAttachmentUrl((int) $distributor->distributor_logo),
						'expiry_date' => $distributor->expiry_date,
						'status'      => (int) $distributor->status,
					];

					if (!isset($grouped[$storeName])) {
						$grouped[$storeName] = [
							'store_name'     => $storeName,
							'store_logo_url' => $storeLogoUrl,
							'brands'         => [],
						];
					}

					$grouped[$storeName]['brands'][] = $brand;
				}
				return array_values($grouped);
			} else {
				$formatted = [];
				foreach ($distributors as $distributor) {
					$store = $distributor->store;

					$formatted[] = [
						'store_name'     => $store->store_name ?? 'Mallshark',
						'store_logo_url' => DistributorHelpers::getAttachmentUrlBySize((int) $store->store_logo, 'media'),
						'brand' => [
							'id'          => $distributor->id,
							'brand_name'  => $distributor->brand_name,
							'brand_url'   => $distributor->brand_url,
							'logo_url'    => DistributorHelpers::getAttachmentUrl((int) $distributor->distributor_logo),
							'expiry_date' => $distributor->expiry_date,
							'status'      => (int) $distributor->status,
							'updated_at'  => $distributor->updated_at,
						],
					];
				}
				return $formatted;
			}
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}

	public function getDistributorDetail($id)
	{
		try {
			$distributor = $this->distributorRepository->getDistributorDetail($id);

			if (!$distributor) {
				throw new ItemNotFoundException('Distributor documents not found.');
			}

			// Unserialize the stored document IDs (attachments)
			$documentIds = unserialize($distributor->documents) ?? [];

			// Fetch all document URLs
			$documentsUrls = DistributorHelpers::getDocumentsUrls($documentIds);

			// Get status records mapped by attachment_id (so we match properly)
			$statusRecords = $distributor->distributorDocumentsStatuses
				->keyBy('attachment_id'); // ensures correct linking by document id

			$documentsData = [];
			foreach ($documentIds as $index => $docId) {
				$statusRecord = $statusRecords->get($docId);

				$documentsData[] = [
					'id'     => $statusRecord->id ?? null,
					'status' => isset($statusRecord) ? (int) $statusRecord->status : null,
					'url'    => $documentsUrls[$index] ?? null,
				];
			}

			// Add combined documents data
			$distributor->documents_data = $documentsData;

			// Add distributor logo
			$distributor->distributor_logo_url = DistributorHelpers::getAttachmentUrl((int) $distributor->distributor_logo);

			// Remove unnecessary relation data
			unset($distributor->distributorDocumentsStatuses);

			return $distributor;

		} catch (ItemNotFoundException $e) {
			throw new ItemNotFoundException($e->getMessage());
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}

	public function assignBrandToDistributor($distributorDocumentId, $brandId)
	{
		try {
			return $this->distributorRepository->assignBrandToDistributor($distributorDocumentId, $brandId);
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}

	public function registerBrand($name, $logo, $url)
	{
		try {
			
			if ($logo instanceof \Illuminate\Http\UploadedFile) {
				$logoId = $this->distributorRepository->uploadImage($logo);
				if (!$logoId) {
					throw new Exception('Logo upload failed');
				}
			} else {
				$logoId = $logo;
			}
					
			return $this->distributorRepository->registerBrand($name, $logoId, $url);
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}

	public function getBrands($distributorId = null)
	{
		try {
			$brands = DistributorHelpers::getBrands();

			if ($brands->isEmpty()) {
				return $brands;
			}

			$brandDistributors = null;

			if ($distributorId) {
				$brandDistributors = $this->distributorRepository->getAssignedBrandsByStoreId($distributorId);

				if ($brandDistributors->isNotEmpty()) {

					// Filter only brand_distributors that are NOT rejected
					$validAssignedBrandIds = $brandDistributors
						->filter(function ($bd) {
							$doc = $bd->distributorDocuments->first();
							return $doc && $doc->status != DistributorsConstants::REJECTED;
						})
						->pluck('brand_id')
						->toArray();

					// Remove only brands with valid (not rejected) assignments
					$brands = $brands->reject(function ($brand) use ($validAssignedBrandIds) {
						return in_array($brand->id, $validAssignedBrandIds);
					});

					// Reindex keys
					$brands = $brands->values();
				}
			}

			foreach ($brands as $brand) {
				$logoUrl = DistributorHelpers::getAttachmentUrl((int) $brand->logo);
				$brand->logo_url = $logoUrl != false ? $logoUrl : null;
			}

			return $brands;
		} catch (ItemNotFoundException $e) {
			throw new ItemNotFoundException($e->getMessage());
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}

	public function getBrandDistributors($brandId = null)
	{
		try {
			$brandDistributors = $this->distributorRepository->getBrandDistributors($brandId);
			foreach ($brandDistributors as $distributor) {

				$distributor->logo_url = DistributorHelpers::getAttachmentUrl((int) $distributor->logo);
				$currentBrandId = $distributor->id;

				$distributors = [];

				foreach ($distributor->distributors as $store) {

					$distributorId = $store->store_id;

					// Get approved docs
					$docs = $this->distributorRepository
						->getDocsByBrandAndDistributorId($currentBrandId, $distributorId);

					if (empty($docs) || $docs->status != DistributorsConstants::APPROVED) {
						continue; // skip unapproved
					}

					// Convert to array
					$storeArray = $store->toArray();

					// Add calculated fields
					$storeArray['distributor_document_id'] = $docs->id;
					$storeArray['store_logo_url'] = DistributorHelpers::getAttachmentUrlBySize(
						(int) $storeArray['store_logo'], 'media'
					) ?: null;
					$storeArray['brand_products_count'] = DistributorHelpers::getBrandProductsCount(
						$currentBrandId,
						$storeArray['store_id']
					);

					// Keep only allowed fields
					$allowedFields = [
						'store_id', 'store_name', 'store_slug',
						'store_logo_url', 'brand_products_count',
						'distributor_document_id'
					];

					$storeFiltered = array_intersect_key($storeArray, array_flip($allowedFields));
					$distributors[] = $storeFiltered;
				}
				// Replace distributors with re-indexed array
				unset($distributor->distributors);
				$distributor->distributors = $distributors;
			}
			return $brandDistributors;
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}

	public function sendDistributorRequestEmail($subject, $distributorUrl, $storeName, $updated = false)
	{
		try {

			$from = env('CARE_EMAIL');
            $htmlContent = view('mail.distributor-request', compact('distributorUrl', 'storeName', 'updated'))->render();

            $params = [
                'from'    => $from,
                'to'      => $from,
                'subject' => $subject,
                'html'    => $htmlContent, 
            ];
            $mailgun = new MailgunController();
			$mailgun->SendMailgunMail($params);

			return true;
		} catch (Exception $e) {
			throw new Exception('Failed to send email: ' . $e->getMessage());
		}
	}
}
