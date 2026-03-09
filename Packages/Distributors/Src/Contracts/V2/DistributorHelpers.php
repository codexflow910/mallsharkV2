<?php

namespace Packages\Distributors\Contracts\V2;

use App\Services\FCMService;
use App\Services\SellerFcmService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Packages\Distributors\Models\V2\Store;
use Packages\Distributors\Models\V2\DistributorDocument;
use Packages\Distributors\Models\V2\{Attachments, AttachmentSizes, Brand, DeviceToken, DistributorDocumentsStatuses, Product, User};

trait DistributorHelpers
{

	public static function generateRandomString($length)
	{
		$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxy';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}
	
 	public static function getAllAttachmentSizes()
	{
		$AllSize = new AttachmentSizes();
		$getDirectorywithSizes = $AllSize->all()->toArray();
		if (!empty($getDirectorywithSizes)) {
			foreach ($getDirectorywithSizes as $key => $value) {

				$directoryName = $value['name'];
				$path = public_path() . '/uploads/' . $directoryName;
				if (!File::exists($path)) {
					File::makeDirectory($path, 0775, true);
				}
			}
		}
		return $getDirectorywithSizes;
	}

	public static function getAttachmentUrl($id, $size = 'distributors-documents')
	{
		$attachment = new Attachments();
		if ($id != '' && is_int($id)) {
			$attachment->id = $id;
			$attachment->size = $size;

			return $attachment->getAttachmentUrlById();
		} else {
			return 'Please add a valid id';
		}
	}

	public static function deleteDocuments($documentIds)
	{
		try {
			if ($documentIds) {
				foreach ($documentIds as $documentId) {
					$document = Attachments::where('attachment_id', (int) $documentId)->first();
					if ($document) {
						$documentPath = public_path('uploads/distributors-documents/' . $document->fileName . '.' . $document->fileExtension);
						if (file_exists($documentPath)) {
							unlink($documentPath);
						}
						$document->delete();
					}
				}
				return true;
			}
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}

	public static function getAccessibleStore($userId)
	{
		$store = Store::where('user_id', $userId)->first();
		if (!$store) {
			$pivot = DB::table('store_user')->where('user_id', $userId)->first();

			if ($pivot) {
				$store = Store::find($pivot->store_id);
			}
		}
		return $store;
	}

	public static function getDistributorDocs($id) {
		return DistributorDocument::find($id);
	}

	public static function getDocumentsUrls($documentIds)
	{
		$urls = [];
		if (!empty($documentIds)) {
			foreach ($documentIds as $documentId) {
				$url = self::getAttachmentUrl($documentId, 'distributors-documents');
				$urls[] = $url;
			}
		}
		return $urls;
	}

	public static function updateDistributorDocumentStatus($distributorDocId, $attachmentId, $status = 0)
	{
		return DistributorDocumentsStatuses::updateOrCreate(
			[
				'distributor_document_id' => $distributorDocId,
				'attachment_id' => $attachmentId,
			],
			[
				'status' => $status,
			]
		);
	}

	public static function deleteDocumentsStatuses(array $deletedDocs)
	{
		if (empty($deletedDocs)) {
			return false;
		}

		return DistributorDocumentsStatuses::whereIn('attachment_id', $deletedDocs)->delete();
	}

	public static function getAttachmentUrlBySize($id, $size = 'media')
	{
		$attachment = new Attachments();
		if ($id != '' && is_int($id)) {
			$attachment->id = $id;
			$attachment->size = $size;

			return $attachment->getAttachmentUrlBySize();
		} else {
			return 'Please add a valid id';
		}
	}

	public static function getBrands()
	{
		try {
			$brands = Brand::all();
			if ($brands->isEmpty()) {
				return $brands;
			}
			return $brands;
		} catch (Exception $e) {
			throw new Exception('Failed to retrieve brands: ' . $e->getMessage());
		}
	}

	public static function getBrandProductsCount($brandId, $storeId = null)
	{
		return Product::where('brand_id', $brandId)
			->when($storeId, fn($query) => $query->where('store_id', $storeId))
			->count();
	}

	/**
    * Push notifications
    */
    public static function pushNotification($userId, $message, $appFlag = null, $url = null)
    {
        $deviceTokens = DeviceToken::where('user_id', $userId)
            ->pluck('fcm_token');
    
        if ($deviceTokens->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'No device tokens found.'], 404);
        }
    
        $service = ($appFlag == 'seller') ? SellerFcmService::class : FCMService::class;
        $failedTokens = [];
        $successTokens = [];
    
        $deviceTokens->each(function ($token) use ($service, $message, $url, &$failedTokens, &$successTokens) {
            try {
                $notificationData = [
                    'title' => $message,
                ];

                if ($url) {
                    $notificationData['url'] = $url;
                }

                $service::send($token, $notificationData);
                $successTokens[] = $token; // Track successful tokens
            } catch (\Exception $e) {
                Log::error("FCM Notification Failed for Token: $token | Error: " . $e->getMessage());
                $failedTokens[] = $token; // Track failed tokens
            }
        });
    
        return response()->json([
            'status' => true,
            'message' => 'Push notifications processed.',
            'success_tokens' => $successTokens,
            'failed_tokens' => $failedTokens,
        ]);
    }

	public static function getUserById($userId) {
        return  User::where('user_id', '=', $userId)->first();
    }

	public static function getUserByStoreId($storeId) {
		$store = Store::where('store_id', $storeId)->first();
		if ($store) {
			return User::where('user_id', '=', $store->user_id)->first();
		}
		return null;
	}

	public static function getBrandById($id)
	{
		return Brand::find($id);
	}
	public static function cleanDomain($url)
	{
		$host = parse_url($url, PHP_URL_HOST) ?? $url;

		return preg_replace('/^www\./', '', $host);
	}
}