<?php

namespace Packages\Product\Contracts\V3;
use Illuminate\Support\Facades\Log;
use Packages\Auth\Models\V3\DeviceToken;
use App\Services\SellerFcmService;
use App\Services\FCMService;
use Exception;
use Packages\Store\Models\V3\{Store,States};
use Packages\Order\Models\V3\{StoreOrder};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Packages\Product\Models\V3\{ProductAiTags, ProductTier};
use Packages\Product\Models\V3\{ProductTags,Product,Attachments,User,ProductVariation};
use Packages\Review\Models\V3\{Review,SuperadminReview};
use Packages\Discount\Models\V3\{Discount, DiscountApplicable,CustomerCoupon,DiscountUsage};
use Packages\Core\Contracts\V3\CoreHelpers;
trait ProductHelpers
{

    protected function convert_to_slug($str)
    {


        $str = strtolower(trim($str));
        $str = preg_replace('/[^a-z0-9-]/', '-', $str);
        $str = preg_replace('/-+/', "-", $str);
        $slug = rtrim($str, '-');
        $lastProductId = Product::max('product_id');
        $currentProductId = $lastProductId ? $lastProductId + 1 : 1;
        $result_slug = Product::where('slug', $slug)->get()->toArray();
        $getRecord = count($result_slug);
        if ($getRecord > 0) {
            // $slug = $slug . '-' . rand(45, 568522);
            $slug = $slug . '-' . $currentProductId;
        }
        return $slug;
    }
    protected function convert_to_slug_for_update($str, $pid)
    {
        // this is only for update case beacuse we have to attach product id with slug if it is not unique to make it unique
        $str = strtolower(trim($str));
        $str = preg_replace('/[^a-z0-9-]/', '-', $str);
        $str = preg_replace('/-+/', "-", $str);
        $slug = rtrim($str, '-');

        $result_slug = Product::where('slug', $slug)->where('product_id', '!=', $pid)->get()->toArray();
        $getRecord = count($result_slug);
        if ($getRecord > 0) {
            $slug = $slug . '-' . $pid;
        }
        return $slug;
    }
    function getAttachmentUrlSmallById($id, $size = 'small')
    {
        $int = (int)$id;
        $data = Attachments::find($id);
        if ($data != '') {
            return url('/uploads') . '/' . $size . '/' . $data->fileName . '_' . $id . '.' . $data->fileExtension;
        }
    }
    function generateRandomString($length)
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxy';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    function getStatebyId($stateId = '')
    {
        $query = States::where('state_id', $stateId)->first();
        if (!empty($query)) {
            return $this->objToArray($query);
        } else {
            return false;
        }
    }

    function sanitizeDescription($description)
    {
        if (!$description) return '';
        return str_replace(["\r", "\n"], '', html_entity_decode(strip_tags($description)));
    }

    function getAttachmentUrlById($id, $size = 'media')
    {

        if (isset($id)) $id = json_decode($id);

        if ($id != '' && is_int($id)) {

            $data = Attachments::find($id);
            if ($data) {

                if(strtolower($data->fileExtension) == 'png' || strtolower($data->fileExtension) == 'jpg' || strtolower($data->fileExtension) == 'webp' || strtolower($data->fileExtension) == 'jpeg' || strtolower($data->fileExtension) == 'gif' || strtolower($data->fileExtension) == 'jfif'){
                    return url('/uploads') . '/' . $size . '/' . $data->fileName . '_' . $id . '.' . $data->fileExtension;
                }
            }
        }
    }
    function getStoreInfoByUserid($userID)
    {
        $res = DB::table('stores')->select('store_id', 'store_name', 'banner_image_id', 'campaign_id', 'store_status', 'super_status')->where('user_id', $userID)->first();

        if ($res) {
            return $res;
        } else {
            return null;
        }
    }
    function getTagsByProductId($productId = "")
    {
        $data = array();
        $query = ProductTags::where('product_id', $productId)->get();
        if (!empty($query)) {
            $resutlt = $this->objToArray($query);
            foreach ($resutlt as $res) {
                $data[] = $res['tag_title'];
            }
        }
        return $data;
    }
    function getStoreNameInfoById($storeID)
    {
        $res = Store::select('store_id','store_name','store_slug')->where('store_id', $storeID)->first();

        if ($res) {
            return $res;
        } else {
            return null;
        }
    }
    function getStoreInfoById($storeID)
    {
        $res = Store::find($storeID);

        $storeData = $res->only(['store_id', 'store_name', 'store_slug', 'store_logo', 'banner_image_id', 'store_state', 'store_city', 'country', 'about_store']);

        if ($storeData) {
            return $storeData;
        } else {
            return null;
        }
    }

    public function get_reviews($p_id = '')
    {
        if ($p_id == '') {
            return 'No record found';
        }

        // Fetch user reviews
        $userReviews = Review::where('product_id', $p_id)
            ->orderBy('created_at', 'DESC')
            ->get();

        // Fetch super admin reviews
        $superAdminReviews = SuperadminReview::where('product_id', $p_id)
            ->orderBy('created_at', 'DESC')
            ->get();

        // Combine and sort all reviews by creation date
        $allReviews = $userReviews->concat($superAdminReviews)
            ->sortByDesc('created_at')
            ->values() // Re-index the collection
            ->toArray();

        return $allReviews;
    }

    function getYoutubeEmbedUrl($videoUrl)
    {

        $shortUrlRegex = '/youtu.be\/([a-zA-Z0-9_]+)\??/i';
        $longUrlRegex = '/youtube.com\/((?:embed)|(?:watch))((?:\?v\=)|(?:\/))(\w+)/i';

        if (preg_match($longUrlRegex, $videoUrl, $matches) || preg_match($shortUrlRegex, $videoUrl, $matches)) {
            $youtube_id = $matches[1];
            // return 'https://www.youtube.com/embed/' . $youtube_id;
            return $videoUrl;
        } else {
            // Handle case when video URL doesn't match any pattern
            $youtube_id = null;
            return $youtube_id;
        }

        // if (preg_match($shortUrlRegex, $videoUrl, $matches)) {
        //     $youtube_id = $matches[count($matches) - 1];
        // }

    }

    public function avaiableForFlashSale($productId = '')
    {
        $flashsalequery = Product::where('product_id', $productId)
            ->where('flashsale_status', 'choosen')->first();
        if (!empty($flashsalequery)) {
            $currentTimeDate = date('Y-m-d H:i:s');
            $constants = config('constants.shopzit_gamification');
            $flashSaleDuration = $constants['CHOOSEN_CASHBACK_LIMIT'];
            $query = DB::table('store_campaigns')
                ->select('flash_sale_start')->where('product_id', $productId)->where('status', 'active')->first();


            $flashEndate = date('Y-m-d H:i:s', strtotime("+$flashSaleDuration hours", strtotime($query->flash_sale_start)));


            if ($flashEndate < $currentTimeDate) {
                Product::where('product_id', $productId)
                    ->update(['flashsale_status' => 'standard', 'home_flash_sale' => 0]);
            }
        }
    }

    function getUserById($user_id = '')
    {
        if ($user_id != '') {
            return $result = User::where('user_id', '=', $user_id)->first();
        }
    }

    function objToArray($arr)
    {

        $arr = json_encode($arr);
        $res = json_decode($arr, true);
        return $res;
    }

    function getProductRatingById($product_id = null)
    {

        $reviews = Review::where('product_id', $product_id)->pluck('rating');
        $average = round($reviews->avg());

        return [
            'average' => $average,
            'total' => $reviews->count()
        ];
    }
    public function getUsersByIds(array $userIds)
    {
        return User::whereIn('user_id', $userIds)->get()->keyBy('user_id');
    }

    public function createSlug($sellerId, $categoryId, $productId)
    {
        $sellerId = str_pad($sellerId, 5, '0', STR_PAD_LEFT);
        $categoryId = str_pad($categoryId, 3, '0', STR_PAD_LEFT);
        $productId = str_pad($productId, 8, '0', STR_PAD_LEFT);
        
        return 'V' . $sellerId . 'C' . $categoryId . 'P' . $productId;
    }

    function hasUserBoughtProduct($product, $userId)
    {
        if (!$product || $product->orderItems->isEmpty()) {
            return false;
        }

        // Step 1: Collect order IDs from orderItems
        $orderIds = $product->orderItems->pluck('order_id')->unique();
        if ($orderIds->isEmpty()) {
            return false;
        }

        // Step 2: Check if any of these orders belong to the given user
        return StoreOrder::whereIn('order_id', $orderIds)
            ->where('buyer_id', $userId)
            ->exists();
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
    public static function generateAiTagForProduct($product)
    {
        $imageUrl = getAttachmentUrlById($product->feature_image);
        $relative = ltrim(str_replace(url('/'), '', $imageUrl), '/');
        $localPath = public_path($relative);

        if (!file_exists($localPath)) {
            return null;
        }

        $imageBase64 = base64_encode(file_get_contents($localPath));

        $clarifaiResponse = Http::withHeaders([
            'Authorization' => 'Key ' . env('CLARIFAI_API_KEY'),
            'Content-Type' => 'application/json',
        ])->post(
            'https://api.clarifai.com/v2/models/general-image-detection/versions/1580bb1932594c93b7e2e04456af7c6f/outputs',
            [
                'inputs' => [[
                    'data' => [
                        'image' => ['base64' => $imageBase64]
                    ]
                ]]
            ]
        );

        $responseData = $clarifaiResponse->json();
        $regions = $responseData['outputs'][0]['data']['regions'] ?? [];

        $scoredTags = collect($regions)->map(function ($region) {
            $concepts = $region['data']['concepts'] ?? [];
            $box = $region['region_info']['bounding_box'] ?? null;

            $area = 0;
            if ($box) {
                $width = $box['right_col'] - $box['left_col'];
                $height = $box['bottom_row'] - $box['top_row'];
                $area = $width * $height;
            }

            return collect($concepts)->map(function ($concept) use ($area) {
                return [
                    'name' => $concept['name'],
                    'value' => $concept['value'],
                    'score' => $concept['value'] * $area
                ];
            });
        })->flatten(1);

        $topConcept = $scoredTags
            ->sortByDesc('score')
            ->unique('name')
            ->first();

        if ($topConcept) {
            ProductAiTags::updateOrCreate(
                ['product_id' => $product->product_id],
                ['tag_title' => $topConcept['name']]
            );
            return $topConcept['name'];
        }

        return null;
    }

    public static function getProductTiersByProductId($productId)
    {
        return ProductTier::where('product_id', $productId)->get();
    }
   public static function checkCouponExists($product, $type, $variation = null, $userId = null)
    {
        // -----------------------------
        // 1. Resolve product/variation
        // -----------------------------
        if ($type === 'variant') {
            $productModel = Product::find($product);
            $itemType = 'variation';
            $itemId = $variation['variation_id'];
            $unitPrice = $variation['price'];
            $quantity = $variation['variation_quantity'] ?? 1;
        } else {
            $productModel = (object)$product;
            $itemType = 'product';
            $itemId = $product['product_id'];
            $unitPrice = $product['price'];
            $quantity = $product['quantity'] ?? 1;
        }

        $storeId = $productModel->store_id;

        // -----------------------------
        // 2. CHECK SALE DISCOUNT (priority)
        // -----------------------------
        $saleId = $productModel->discount_id;
        $saleActive = false;
        $unitSalePrice = null;
        $totalAfterSale = null;

        if ($saleId) {
            // Check custom discount first
            $discount = Discount::find($saleId);
            $saleApplicable = DiscountApplicable::where([
                'applicable_type' => $itemType,
                'discount_id' => $saleId,
                'applicable_id' => $itemId
            ])->first();

            if ($saleApplicable) {
                $saleActive = true;
                $saleType = $saleApplicable->custom_discount_type ?? $discount->discount_value_type;
                $saleValue = $saleApplicable->custom_discount_value ?? $discount->discount_value;
            }

            // Apply sale to unit price
            if ($saleActive) {
                $unitSalePrice = $saleType === 'percent'
                    ? $unitPrice - ($unitPrice * ($saleValue / 100))
                    : $unitPrice - $saleValue;

                $unitSalePrice = max($unitSalePrice, 0);
            }
        }

        // -----------------------------
        // 3. CHECK USER-SPECIFIC COUPON
        // -----------------------------
        $couponActive = false;
        $couponDiscountValue = 0;
        $couponDiscountType = null;
        $couponId = null;
        $couponCode = null;
        $couponCollected = false;

        if ($userId) {
            $userCouponIds = CustomerCoupon::where('user_id', $userId)
                ->where('status', 1)
                ->pluck('coupon_id');

            if ($userCouponIds->isNotEmpty()) {
                $coupon = Discount::whereIn('id', $userCouponIds)
                    ->where('store_id', $storeId)
                    ->where('status', 'active')
                    ->where('state', 'live')
                    ->where('type', 'coupon')
                    ->first();

                if ($coupon) {
                    // Check quota / one-use-per-customer
                    $alreadyUsed = CoreHelpers::isCouponUsageExceeded($coupon, $userId);

                    if (!$alreadyUsed) {
                        // Check if product/variation exists in DiscountApplicable for this coupon
                        $applicable = DiscountApplicable::where([
                            'applicable_type' => $itemType,
                            'discount_id' => $coupon->id,
                            'applicable_id' => $itemId
                        ])->exists();

                        if ($applicable) {
                            // Fetch coupon discount from default Discount table
                            $couponDiscountType = $coupon->discount_value_type;
                            $couponDiscountValue = $coupon->discount_value;
                            $couponActive = true;
                            $couponId = $coupon->id;
                            $couponCode = $coupon->code;
                            $couponCollected = true;
                        }
                    }
                }
            }
        }

        // -----------------------------
        // 4. APPLY COUPON ON TOTAL AFTER SALE
        // -----------------------------
        $effectiveUnitPrice = $totalAfterSale ?? $unitPrice;
        $finalTotal = $totalAfterSale;
        if ($couponCollected && $couponDiscountValue !== null) {
            $finalTotal = $couponDiscountType === 'percent'
                ? $totalAfterSale - ($totalAfterSale * ($couponDiscountValue / 100))
                : $totalAfterSale - $couponDiscountValue;

            $finalTotal = max($finalTotal, 0);
        }
        if (!$couponCollected) {
            $coupon = Discount::where([
                'store_id' => $storeId,
                'status' => 'active',
                'state' => 'live',
                'type' => 'coupon',
                'archive' => 0
            ])->first();

            if ($coupon) {
                $couponApplicable = DiscountApplicable::where([
                    'applicable_type' => $itemType,
                    'discount_id' => $coupon->id,
                    'applicable_id' => $itemId
                ])->first();

                if ($couponApplicable) {
                    $couponActive = true;
                    $couponId = $coupon->id;
                }
            }
        }
        // -----------------------------
        // 5. PREPARE FINAL RESULT
        // -----------------------------
        $final = [
            'on_sale' => $saleActive ? 1 : 0,
            'coupon' => $couponActive ? 1 : 0,
            'discount_id' => $saleActive ? $saleId : null,
            'coupon_id' => $couponId,
            'coupon_collected_code' => $couponCode,
            'coupon_collected' => $couponCollected ? 1 : 0,
            'unit_original_price' => $unitPrice,
            'unit_sale_price' => round($unitSalePrice ?? 0, 2),
        ];

        if ($type === 'variant') {
            return array_merge($variation, $final);
        }

        return array_merge((array)$product, $final);
    }



    public static function getImageAltById($id)
    {
        $attachment = Attachments::find($id);
        if ($attachment) {
            return $attachment->alt ?? '';
        }
        return '';
    }

    public static function getGalleryAlt($gallery)
    {
        $gallery = unserialize($gallery);
        $galleryAlt = [];
        if (is_array($gallery) && count($gallery) > 0) {
            foreach ($gallery as $imageId) {
                $attachment = Attachments::find($imageId);
                $galleryAlt[] = $attachment ? $attachment->alt : '';
            }
        }
        return $galleryAlt;
    }

    public static function updateImageAlt($id, $altText)
    {
        $attachment = Attachments::find($id);
        if ($attachment) {
            $attachment->alt = $altText;
            $attachment->save();
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
    public static function getProductTypeById($productId)
    {
        $product = Product::find($productId);
        return $product ? $product->product_type : null;
    }
    public static function deleteVariationsData($productId)
    {
        return DB::transaction(function () use ($productId) {

            // 1. Get all variations for this product
            $variations = ProductVariation::where('product_id', $productId)->get();

            if ($variations->isEmpty()) {
                return true; // no variations, nothing to delete
            }

            // Extract all variation IDs
            $variationIds = $variations->pluck('variation_id')->toArray();

            // 2. Delete DiscountApplicable rows where applicable_id is variation_id
            DiscountApplicable::where('applicable_type', 'variation')
                ->whereIn('applicable_id', $variationIds)
                ->delete();

            // 3. Delete Product Tiers for these variations
            ProductTier::whereIn('variation_id', $variationIds)->delete();

            // 4. Delete Variation Records
            ProductVariation::whereIn('variation_id', $variationIds)->delete();

            return true;
        });
    }
    public static function deleteSimpleProductData($productId)
    {
        return DB::transaction(function () use ($productId) {

            // 1. Delete DiscountApplicable rows where applicable_id is product_id
            DiscountApplicable::where('applicable_type', 'product')
                ->where('applicable_id', $productId)
                ->delete();

            // 2. Delete Product Tiers for this product
            ProductTier::where('product_id', $productId)->delete();

            return true;
        });
    }
    public static function deleteVariableProductTiersData($productId)
    {
        return DB::transaction(function () use ($productId) {

            // 1. Get all variations for this product
            $variations = ProductVariation::where('product_id', $productId)->get();

            if ($variations->isEmpty()) {
                return true; // no variations, nothing to delete
            }

            // Extract all variation IDs
            $variationIds = $variations->pluck('variation_id')->toArray();

            // 2. Delete Product Tiers for these variations
            ProductTier::whereIn('variation_id', $variationIds)->delete();

            return true;
        });
    }
    public static function deleteSimpleProductTiersData($productId)
    {
        return DB::transaction(function () use ($productId) {

            // 1. Delete Product Tiers for this product
            ProductTier::where('product_id', $productId)->delete();

            return true;
        });
    }
}
