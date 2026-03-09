<?php

namespace Packages\Discount\Repositories\Eloquent\V3;


use stdClass;
use Exception;
use InvalidArgumentException;
use App\Models\AttachmentSizes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Packages\Product\Models\V3\{ProductVariation,Product};
use Intervention\Image\Facades\Image;
use Packages\Discount\Models\V3\Discount;
use Packages\Discount\Models\V3\DiscountUsage;
use Illuminate\Support\ItemNotFoundException;
use Packages\Discount\Contracts\V3\BaseApiResponse;
use Packages\Discount\Models\V3\DiscountApplicable;
use \Packages\Discount\Contracts\V3\DiscountHelpers;
use Illuminate\Support\Facades\Auth;
use Packages\Discount\Contracts\V3\DiscountRepositoryInterface;
use Packages\Discount\Models\V3\CustomerCoupon;

class DiscountRepository implements DiscountRepositoryInterface
{
    use BaseApiResponse;
    use DiscountHelpers;

    protected $model;

    public function __construct(Discount $model,)
    {
        $this->model = $model;
    }

    public function saveDiscount($data)
    {
        try {

            $discount = $this->model->fill($data);
            $discount->save();
            return $discount;
        } catch (Exception $e) {
            throw new Exception("Error saving discount: " . $e->getMessage());
        }
    }

    public function saveDiscountApplicables($data)
    {
        try {
            // return $this->model->discountApplicables()->createMany($data);
            return DiscountApplicable::insert($data);
        } catch (Exception $e) {
            throw new Exception("Error saving discount applicables: " . $e->getMessage());
        }
    }

    public function deleteDiscountApplicables($discountId)
    {
        try {
            return DiscountApplicable::where('discount_id', $discountId)->delete();
        } catch (\Exception $e) {
            throw new \Exception("Error deleting old discount applicables: " . $e->getMessage());
        }
    }

    public function updateDiscount(array $data, $id)
    {
        try {
            $discount = $this->model->findOrFail($id);

            if (!$discount) {
                throw new \Exception('Discount not found');
            }

            $discount->fill($data);
            $discount->save();

            return $discount;
        } catch (\Exception $e) {
            throw new \Exception("Error updating discount: " . $e->getMessage());
        }
    }

    public function getAllDiscounts($storeId)
    {
        try {
            return $this->model->where('store_id', $storeId)
                ->where('archive', false)
                ->get();
        } catch (\Exception $e) {
            throw new ItemNotFoundException("Discounts not found.");
        }
    }

    public function getArchivedDiscountsByStore($storeId)
    {
        try {
            return $this->model->where('store_id', $storeId)
                ->where('archive', true)
                ->get();
        } catch (Exception $e) {
            throw new ItemNotFoundException("Discounts not found.");
        }
    }

    public function getDiscount($id)
    {
        try {
            return $this->model->with('discountApplicables')->findOrFail($id);
        } catch (Exception $e) {
            throw new ItemNotFoundException("Discount not found.");
        }
    }

    public function findById($id)
    {
        try {
            return $this->model->findOrFail($id);
        } catch (Exception $e) {
            throw new ItemNotFoundException("Discount not found.");
        }
    }

    public function findActiveDiscounts($storeId, $excludeId,$discountType)
    {
        return $this->model->where('store_id', $storeId)
            ->where('status', 'active')
            ->where('type', $discountType)
            ->where('id', '!=', $excludeId)
            ->get();
    }

    public function save($discount)
    {
        try{
            return $discount->save();
        } catch (Exception $e) {
            throw new Exception("Error saving discount: " . $e->getMessage());
        }
    }
    
    public function saveProducts($product)
    {
        try{
            // dd($product);
            return $product->save();
        } catch (Exception $e) {
            throw new Exception("Error updating product: " . $e->getMessage());
        }
    }
    
    public function savevariations($variation)
    {
        try{
            return $variation->save();
        } catch (Exception $e) {
            throw new Exception("Error updating variation: " . $e->getMessage());
        }
    }

    public function findActiveDiscountByStore($storeId, $excludeId,$discountType)
    {
        return Discount::where('store_id', $storeId)
            ->where('status', 'active')
            ->where('type', $discountType)
            ->where('id', '!=', $excludeId)
            ->first();
    }

    public function getProductsByDiscount($discountId)
    {
        try {
            return Product::where('discount_id', $discountId)->with('variations')->get();
        } catch (Exception $e) {
            throw new ItemNotFoundException("Products not found.");
        }
    }

    // public function getCouponByCode($couponCode)
    // {
    //     return new stdClass();
    // }

    // public function checkUserCouponUsage($userId, $couponId)
    // {
        
    // }

    public function productSave($id, $discount_value = null, $discount_percentage = null, $on_sale, $discount_id = null,$actualPrice = null)
    {

        $updateData = [
            'sale_price' => $discount_value,
            'discount_percentage' => $discount_percentage,
            'on_sale' => $on_sale,
            'discount_id' => $discount_id,
        ];

        if (!is_null($actualPrice)) {
            $updateData['price'] = $actualPrice;
        }

        $affectedRows = DB::table('products')
            ->where('product_id', $id)
            ->update($updateData);

        if ($affectedRows === 0) {
            throw new \Exception("Product with ID {$id} not found or no changes made");
        }

        return $affectedRows;
    }

    public function updateParentProductOnSale($data)
    {
        try {
            $onSale = $data['on_sale'];
            $discountValue = $data['discount_value'];
            $discountId = $data['discount_id'];
            $productId = $data['product_id'];
            $discountPercentage = $data['discount_percentage'];
            $actualPrice = $data['actual_price'];

            DB::update("
                UPDATE products 
                SET on_sale = ?, 
                discount_id = ?,
                discount_percentage = ?,
                sale_price = ?,
                price = ?
                WHERE product_id = ?",
                [$onSale, $discountId,$discountPercentage,$discountValue,$actualPrice, $productId]
            );
        } catch (\Exception $e) {
            throw new \Exception("Error saving product: " . $e->getMessage());
        }
    }

   public function coupons($data)
    {
        $requestedPercent = $data['discount'] ?? null;
        $requestedFixed = $data['fixed'] ?? null;
        $requestedEndAt = isset($data['expire']) ? strtolower($data['expire']) : null;
        $userId = $data['user_id'] ?? null;
        $now = \Carbon\Carbon::now();
        $result = [];
        $discounts = Discount::where('status', 'active')
            ->where('state', 'live')
            ->where('type', 'coupon')->where(function ($query) use ($now) {
            $query->whereNull('end_time')
                ->orWhere('end_time', '0000-00-00 00:00:00')
                ->orWhere('end_time', '>=', $now);
            })
            ->with(['store', 'applicable' => function ($q) {
                $q->select('id', 'discount_id', 'applicable_id', 'applicable_type', 'custom_discount_type', 'custom_discount_value');
            }])->get();
        $allApplicables = DiscountApplicable::whereIn('discount_id',$discounts->pluck('id'))
            ->select('discount_id', 'applicable_id', 'applicable_type')
            ->get();

        $productIds = $allApplicables->where('applicable_type', 'product')->pluck('applicable_id')->unique()->toArray();
        $variationIds = $allApplicables->where('applicable_type', 'variation')->pluck('applicable_id')->unique()->toArray();

        $productPrices = Product::whereIn('product_id', $productIds)->pluck('price', 'product_id');
        $variationPrices = ProductVariation::whereIn('variation_id', $variationIds)->pluck('price', 'variation_id');
        foreach ($discounts as $discount) {
            if ($discount->applicable->isEmpty() && !empty($discount->store)) {
                continue;
            }
            // check usage limit
            if($userId){
                if($discount->one_use_per_customer == 1){
                    $usageLimit = DiscountUsage::where('discount_id', $discount->id)->where('user_id',$userId)->where('usage_count',1)->exists();
                    if($usageLimit){
                        continue;
                    } 
                }  
            }
            $maxPercent = 0;
            $discountFlag = null;
            foreach ($discount->applicable as $applicable) {

                $id = $applicable->applicable_id;
                $type = $applicable->applicable_type;
                $price = $type === 'product'
                    ? ($productPrices[$id] ?? 0)
                    : ($variationPrices[$id] ?? 0);

                if ($price <= 0) continue;
                
                $value = ($applicable->custom_discount_value && $applicable->custom_discount_value > $discount->discount_value)
                    ? $applicable->custom_discount_value
                    : $discount->discount_value;

                $discountType = $applicable->custom_discount_type ?? $discount->discount_value_type;

                if ($discountType === 'fixed') {
                        if ($value < $price) {
                            $discountFlag = 'fixed';
                        }
                        $percent = ($value / $price) * 100;
                    } else {
                        $discountFlag = 'percentage';
                        $percent = $value;
                    }
                    if ($percent > $maxPercent) {
                        $maxPercent = round($percent, 2);
                    }
                }

                if (!is_null($requestedFixed) && $requestedFixed) {
                    if ($discountFlag !== 'fixed') {
                        continue; 
                    }
                }
            
            if (!is_null($requestedPercent)) {
                if ($discountFlag === 'fixed') {
                    continue;
                }
                if ($requestedPercent == 30 && $maxPercent > 30) {
                    continue; 
                }

                if ($requestedPercent == 50 && ($maxPercent <= 30 || $maxPercent > 50)) {
                    continue; 
                }

                if ($requestedPercent == 70 && ($maxPercent <= 50 || $maxPercent > 70)) {
                    continue; 
                }

                if ($requestedPercent == 100 && $maxPercent <= 70) {
                    continue; //
                }
            }
            if (!is_null($requestedEndAt)) {
                // If end_time is invalid/null, skip
                if (is_null($discount->end_time) || $discount->end_time === '' || $discount->end_time === '0000-00-00 00:00:00') {
                    continue; // skip coupon
                }

                $endTime = \Carbon\Carbon::parse($discount->end_time);
                $hoursLeft = $now->diffInHours($endTime, false);
                $daysLeft  = $now->diffInDays($endTime, false);

                if ($hoursLeft <= 0) continue;

                if (str_contains($requestedEndAt, 'h')) {
                    $requestHours = (int) str_replace('h', '', $requestedEndAt);
                    if (!($hoursLeft > 0 && $hoursLeft <= $requestHours)) {
                        continue;
                    }
                } elseif (str_contains($requestedEndAt, 'd')) {
                    $requestDays = (int) str_replace('d', '', $requestedEndAt);
                    if ($hoursLeft < 24) continue;
                    if ($daysLeft !== $requestDays) continue;
                }
            }else {
                // No requestedEndAt filter, can still check end_time to skip expired coupons
                if (!is_null($discount->end_time) && $discount->end_time !== '' && $discount->end_time !== '0000-00-00 00:00:00') {
                    $endTime = \Carbon\Carbon::parse($discount->end_time);
                    $hoursLeft = $now->diffInHours($endTime, false);
                    if ($hoursLeft <= 0) continue;
                }
            }
            $storeLogo = null;
            if($discount->store->store_logo){
                $storeLogo = DiscountHelpers::getAttachmentUrlById((int)$discount->store->store_logo);
            }
            $couponCollected = false;
            if($userId){
                $couponCollected = CustomerCoupon::where('user_id', $userId)
                ->where('coupon_id', $discount->id)
                ->exists();
            }
            $result[] = [
                'discount_id' => $discount->id,
                'title' => $discount->title,
                'code' => $discount->code,
                'highest_discount_percent' => $maxPercent,
                'store_slug' => $discount->store->store_slug ?? null,
                'store_name' => $discount->store->store_name ?? null,
                'expiry_date' => $discount->end_time,
                'coupon_logo' => $storeLogo,
                'discount_type_flag' => $discountFlag, 
                'coupon_collected' => $couponCollected
            ];
        }
        return $result;
    }
    public function getProductsByCoupon($id)
    {
        $discount = Discount::where('status', 'active')
            ->where('state', 'live')
            ->where('type', 'coupon')
            ->where('id', $id)
            ->first();
        if (!$discount) {
            return response()->json([
                'message' => 'Coupon not found or inactive',
            ], 404);
        }

        $applicables = DiscountApplicable::where('discount_id', $discount->id)
            ->get();

        $productIds = $applicables->where('applicable_type', 'product')->pluck('applicable_id')->unique()->toArray();
        $variationIds = $applicables->where('applicable_type', 'variation')->pluck('applicable_id')->unique()->toArray();
       
        $products = Product::with('variations', 'labels') 
            ->whereIn('product_id', $productIds)
            ->get();

        foreach ($products as $product) {
            $labelSlugs = $product->labels->pluck('slug')->toArray();
            unset($product->labels);
            $product->labels = $labelSlugs;
        }

        $variations = ProductVariation::whereIn('variation_id', $variationIds)
            ->get();
        foreach ($products as $product) {
            $product->feature_image = DiscountHelpers::getAttachmentUrlById((int)$product->feature_image);
        }
        foreach ($variations as $variation) {
            $parentId = $variation->product_id;
            if (!$products->where('product_id', $parentId)->first()) {
                $product = Product::where('product_id', $parentId)->first();
                if ($product) {
                    $product->feature_image = DiscountHelpers::getAttachmentUrlById((int)$product->feature_image);
                    $variation->product_variation_image = $variation->product_variation_image ? DiscountHelpers::getAttachmentUrlById($variation->product_variation_image) : '';
                    $product->variation_attached = $variation;
                    $products->push($product);
                }
            }
        }


        return([
            'coupon' => $discount,
            'products' => $products
        ]);
      
    }
   public function storeCustomerCoupons($data)
    {
        try {
            $userId = Auth::id();
            return CustomerCoupon::create([
                'user_id'   =>$userId,
                'coupon_id' => $data['coupon_id'],
                // 'status'    => $data['status'] ?? '1',
            ]);
        } catch (Exception $e) {
            throw new Exception("Error saving customer coupon: " . $e->getMessage());
        }
    }
    public function getCustomerCouponsDashboard($data)
    {
        try {
            $userId = $data->user_id ?? null;
            if (!$userId) {
                throw new InvalidArgumentException("User ID is required.");
            }
           $customerCoupons = CustomerCoupon::where('user_id', $userId)
            ->whereHas('coupon', function ($q) use ($userId) {
                $q->where('type', 'coupon')
                ->where('status', 'active')
                ->where('state', 'live')
                ->where(function ($cq) use ($userId) {
                    // If coupon is NOT limited to one use per customer, include it
                    $cq->where('one_use_per_customer', 0)
                    // Otherwise (one_use_per_customer == 1) include only when
                    // there is no usage by this user OR the usage_count for this user is 0
                    ->orWhere(function ($cq2) use ($userId) {
                        $cq2->where('one_use_per_customer', 1)
                            ->where(function ($cq3) use ($userId) {
                                $cq3->whereDoesntHave('usages', function ($u) use ($userId) {
                                    $u->where('user_id', $userId);
                                })
                                ->orWhereHas('usages', function ($u2) use ($userId) {
                                    $u2->where('user_id', $userId)
                                        ->where('usage_count', 0);
                                });
                            });
                    });
                });
            })
            ->with('coupon')
            ->get();
              return $customerCoupons;
        } catch (Exception $e) {
            throw new Exception("Error fetching customer coupons: " . $e->getMessage());
        }
    }

}
