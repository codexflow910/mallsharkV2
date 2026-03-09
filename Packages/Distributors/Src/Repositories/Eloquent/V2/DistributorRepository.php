<?php

namespace Packages\Distributors\Repositories\Eloquent\V2;

use Exception;
use Illuminate\Support\Facades\File;
use Packages\Distributors\Models\V2\Store;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Packages\Distributors\Contracts\V2\DistributorHelpers;
use Packages\Distributors\Models\V2\{Attachments, Brand, BrandDistributor, DistributorDocument, DistributorDocumentsStatuses};
use Packages\Distributors\Contracts\V2\DistributorRepositoryInterface;

class DistributorRepository implements DistributorRepositoryInterface
{
	use DistributorHelpers;
	
	protected $brandDistributor;
	protected $store;

	public function __construct(BrandDistributor $brandDistributor, Store $store)
	{
		$this->brandDistributor = $brandDistributor;
		$this->store = $store;
	}

	public function uploadImage($image)
	{
		// Ensure the File facade is imported
		if (!class_exists('File')) {
			throw new \RuntimeException('File facade not available');
		}

		$destinationPath = public_path('uploads/distributors-documents');
		if (!File::exists($destinationPath)) {
			File::makeDirectory($destinationPath, 0775, true);
		}

		$originalFileName = $image->getClientOriginalName();
		$nameOnly = pathinfo($originalFileName, PATHINFO_FILENAME);
		$fileExtension = strtolower($image->getClientOriginalExtension());

		$fileName = DistributorHelpers::generateRandomString(15) . '--_--' . $nameOnly;
		$fullFileName = $fileName . '.' . $fileExtension;

		$image->move($destinationPath, $fullFileName);

		$attachment = new Attachments();
		$attachment->fileName = $fileName;
		$attachment->fileExtension = $fileExtension;
		$attachment->save();

		return $attachment->attachment_id;
	}

	public function create(array $data, $storeId, $logoId = null, array $documents = [])
    {
        try {
            $distributorDocs = new DistributorDocument();

            $distributorDocs->fill([
                'distributor_id'    => $storeId,
                'brand_name'        => $data['brand_name'] ?? null,
                'brand_url'         => $data['brand_url'] ?? null,
                'expiry_date'       => $data['expiry_date'] ?? null,
                'distributor_logo'  => $logoId ?? null,
                'documents'         => !empty($documents) ? serialize($documents) : null,
                'status'            => 0
            ]);

            $distributorDocs->save();
            foreach ($documents as $documentId) {
                DistributorDocumentsStatuses::create([
                    'distributor_document_id' => $distributorDocs->id,
                    'attachment_id'           => $documentId,
                    'status'                  => 0,
                ]);
            }

            return $distributorDocs->load('distributorDocumentsStatuses');
        } catch (Exception $e) {
            throw new Exception('Failed to create distributor: ' . $e->getMessage());
        }
    }

    public function update($id, array $data, $storeId, $logoId = null, array $documents = [])
    {
        try {
            $distributor = DistributorDocument::findOrFail($id);

            $updateData = [
                'distributor_id'       => $storeId,
                'brand_name'           => $data['brand_name'] ?? $distributor->brand_name,
                'brand_url'            => $data['brand_url'] ?? $distributor->brand_url,
                'expiry_date'          => $data['expiry_date'] ?? $distributor->expiry_date,
                'distributor_logo'     => $logoId ?? $distributor->distributor_logo,
            ];

            // If documents are passed, overwrite. If not, keep old ones.
            if (!empty($documents)) {
                $updateData['documents'] = serialize(array_unique($documents));
            }

            $distributor->update($updateData);
            return $distributor;

        } catch (ModelNotFoundException $e) {
            throw new ItemNotFoundException('Distributor record not found.');
        } catch (Exception $e) {
            throw new Exception('Failed to update distributor: ' . $e->getMessage());
        }
    }

    public function listDocumentsByStore($storeId, $id = null)
    {
        $query = DistributorDocument::with('distributorDocumentsStatuses', 'brandDistributor.brand')->where('distributor_id', $storeId);
        if ($id) {
            $query->where('id', $id);
        }
        return $query->get();
    }

    public function changeDistributorStatus($distributorDocumentId, $status, $note = null)
    {
        $distributorDoc = DistributorDocument::find($distributorDocumentId);
        $distributorDoc->update([
            'status' => $status,
            'note'   => $note
        ]);

        return $distributorDoc;
    }

    public function changeDocumentStatus($documentId, $status)
    {
        $distributorDoc = DistributorDocumentsStatuses::find($documentId);
        $distributorDoc->update([
            'status' => $status
        ]);
        
        return $distributorDoc->load('distributorDocument');
    }

    public function getAllDistributors($status = null)
    {
        $query = DistributorDocument::with('store');
        if (!is_null($status) && is_int($status)) {
            $query->where('status', $status);
        }
        
        return $query->get();
    }

    public function getDistributorDetail($id)
    {
        return DistributorDocument::with('distributorDocumentsStatuses')->find((int)$id);
    }

    public function assignBrandToDistributor($distributorDocumentId, $brandId)
    {
        $doc = DistributorDocument::findOrFail($distributorDocumentId);

        if (!empty($doc->brand_distributor_id)) {
            BrandDistributor::where('id', $doc->brand_distributor_id)->delete();
        }

        // Create new relationship
        $brandDistributor = BrandDistributor::create([
            'distributor_id' => $doc->distributor_id,
            'brand_id'       => $brandId,
        ]);

        $brand = DistributorHelpers::getBrandById($brandId);

        // Update document with new assigned brand
        $doc->brand_distributor_id = $brandDistributor->id;
        $doc->brand_name = $brand->name;
        $doc->brand_url = $brand->url;
        $doc->save();

        return $doc;
    }

    public function registerBrand($name, $logoId, $url)
    {
        return Brand::create([
            'name' => $name,
            'logo' => $logoId,
            'url'  => $url,
        ]);
    }

    public function getBrandDistributors($brandId = null)
    {
        $query = Brand::with('distributors');

        if (!is_null($brandId) && is_int($brandId)) {
            $query->where('id', $brandId);
        }

        return $query->get();
    }

	public function getAssignedBrandsByStoreId($distributorId)
    {
        return BrandDistributor::with('distributorDocuments')->where('distributor_id', $distributorId)->get();
    }
     
    public function getDocsByBrandAndDistributorId($brandId, $distributorId)
    {
        $brandDistributor = BrandDistributor::where('distributor_id', $distributorId)
            ->where('brand_id', $brandId)
            ->latest()
            ->first();

        if (!$brandDistributor) {
            return collect(); // return empty collection instead of error
        }

        return DistributorDocument::where('brand_distributor_id', $brandDistributor->id)->first();
    }

}
