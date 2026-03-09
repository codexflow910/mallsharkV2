<?php
namespace Packages\Discount\Contracts\V4;

use Illuminate\Support\Facades\DB;
use Packages\Store\Models\V4\Store;
use Packages\Core\Models\V4\category;
use Packages\Product\Models\V4\{Product,Attachments,Favourites};
use Packages\Product\Models\V4\ProductVariation;
use Packages\Discount\Models\V4\DiscountApplicable;
use App\Services\FCMService;
use App\Services\SellerFcmService;
use Illuminate\Support\Facades\Log;
use Exception;
use Packages\Auth\Models\V4\DeviceToken;

trait DiscountHelpers
{

    protected function getStoreProducts($store_id)
    {
        return Product::where('store_id', $store_id)->with('variations')->get();
    }

    protected function getDiscountedProducts($store_id, $discountId)
    {
        return Product::where('store_id', $store_id)->where('discount_id', $discountId)->with('variations')->get();
    }

    public function getApplicableProducts($discountId)
    {
        return Product::whereIn('product_id', function ($query) use ($discountId) {
            $query->from('discount_applicables')
                ->where('discount_id', $discountId)
                ->where('applicable_type', 'product')
                ->where('excluded', false)
                ->select('applicable_id');//Applicable_id is product_id
        })->with('variations')->get();
    }

    public function getApplicableVariations($discountId)
    {
        return ProductVariation::whereIn('variation_id', function ($query) use ($discountId) {
            $query->from('discount_applicables')
                ->where('discount_id', $discountId)
                ->where('applicable_type', 'variation')
                ->where('excluded', false)
                ->select('applicable_id');//Applicable_id is variation_id
        })->get();
    }

    public function getApplicableCategories($discountId)
    {
        return Category::whereIn('id', function ($query) use ($discountId) {
            $query->select('applicable_id')
                ->from('discount_applicables')
                ->where('discount_id', $discountId)
                ->where('applicable_type', 'category')
                ->where('excluded', false);
        })->get();
    }

    public function getCustomDiscount($discountId, $type, $applicableId)
    {
        $customDiscount = DiscountApplicable::where('discount_id', $discountId)
            ->where('applicable_type', $type)
            ->where('applicable_id', $applicableId)
            ->select('custom_discount_type', 'custom_discount_value')
            ->first();

        return $customDiscount
            ? ['type' => $customDiscount->custom_discount_type, 'value' => $customDiscount->custom_discount_value]
            : null;
    }

    public function getExcludedProducts($discountId)
    {
        return Product::whereIn('product_id', function ($query) use ($discountId) {
            $query->select('applicable_id')
                ->from('discount_applicables')
                ->where('discount_id', $discountId)
                ->where('applicable_type', 'product')
                ->where('excluded', true);
        })->with('variations')->get();
    }

    public function getProductsByCategories($storeId, $categoryIds)
    {
        return Product::where('store_id', $storeId)
            ->whereIn('product_id', function ($query) use ($categoryIds) {
                $query->select('product_id')
                    ->from('product_category')
                    ->whereIn('category_id', $categoryIds);
            })->with('variations')->get();
    }

    public function findProductById($productId)
    {
        return Product::find($productId);
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
    public static function getAttachmentUrlById($id, $size = 'media') 
    {
        $global_obj = new Attachments();
        if(isset($id))
        $id=json_decode($id);

        if ($id != ''){
            if(is_int($id)) {
                $global_obj->id = $id;
                $global_obj->size = $size;
                
                return $global_obj->getAttachmentUrlById();
            }else {
                return false;
            }
        } else {
            return 'Please add a valid id';
        }
    }
    public function getFavoriteUsersByProductId ($productId) 
    {
        return Favourites::where('product_id', $productId)->pluck('user_id');
    }
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
}