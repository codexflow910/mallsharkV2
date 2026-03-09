<?php

namespace Packages\Discount\Repositories\Eloquent;


use stdClass;
use Exception;
use InvalidArgumentException;
use App\Models\AttachmentSizes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Packages\Product\Models\Product;
use Intervention\Image\Facades\Image;
use Packages\Discount\Models\Discount;
use Packages\Discount\Models\DiscountUsage;
use Illuminate\Support\ItemNotFoundException;
use Packages\Discount\Contracts\BaseApiResponse;
use Packages\Discount\Models\DiscountApplicable;
use Packages\Discount\Contracts\DiscountRepositoryInterface;

class DiscountRepository implements DiscountRepositoryInterface
{
    use BaseApiResponse;
    protected $model;

    public function __construct(Discount $model)
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

    public function findActiveDiscounts($storeId, $excludeId)
    {
        return $this->model->where('store_id', $storeId)
            ->where('status', 'active')
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

    public function findActiveDiscountByStore($storeId, $excludeId)
    {
        return Discount::where('store_id', $storeId)
            ->where('status', 'active')
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

    public function productSave($id, $discount_value = null, $discount_percentage = null, $on_sale, $discount_id = null)
    {
        // try {
        //     $product = Product::findOrFail($id);
        //     $product->sale_price = $discount_value;
        //                 $product->discount_percentage = $discount_percentage ?? null;
        //                 $product->on_sale = $on_sale;
        //                 $product->discount_id = $discount_id ?? null;
        //                 // dd($product);
        //      $product->save();
        // } catch (Exception $e) {
        //     throw new Exception("Error saving product: " . $e->getMessage());
        // }

        try {
            DB::update("
                UPDATE products 
                SET sale_price = ?, 
                    discount_percentage = ?, 
                    on_sale = ?, 
                    discount_id = ? 
                WHERE product_id = ?",
                [$discount_value, $discount_percentage, $on_sale, $discount_id, $id]
            );
        } catch (\Exception $e) {
            throw new \Exception("Error saving product: " . $e->getMessage());
        }
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
}
