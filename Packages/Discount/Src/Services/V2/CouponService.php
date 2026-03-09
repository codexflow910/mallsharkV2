<?php

namespace Packages\Discount\Services\V2;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ItemNotFoundException;
use Packages\Discount\Services\V2\DiscountService;
use Packages\Discount\Contracts\V2\DiscountHelpers;
use Packages\Discount\Repositories\Eloquent\V2\CouponRepository;
use Packages\Discount\Repositories\Eloquent\V2\DiscountRepository;

class CouponService extends DiscountService

{
    use DiscountHelpers;
    protected $couponRepository;

    public function __construct(CouponRepository $couponRepository,DiscountRepository $discountRepository)
    {
        parent::__construct($discountRepository);
        $this->couponRepository = $couponRepository;
    }


    public function applyCoupon($data)
    {
        $couponCode = $data['coupon_code'];
        $storeId = $data['store_id'];
        $cartTotal = $data['cart_total'];
        $cartQuantity = $data['cart_quantity'];
        $userId = auth()->id();

        if(!$userId) {
            throw new ItemNotFoundException('User not found');
        }

        $coupon = $this->couponRepository->getCouponByCode($couponCode);

        if (!$coupon) {
            throw new ItemNotFoundException('Coupon not found');
        }
        
        if ($coupon->store_id !== (int)$storeId) {
            throw new \Exception('Coupon is not valid for this store.');
        }

        if ($coupon->status !== 'active' || $coupon->state !== 'live') {
            throw new \Exception('Coupon is not active.');
        }

        if ($coupon->usage_count >= $coupon->max_usage_limit) {
            throw new \Exception('Coupon usage limit has been reached.');
        }

        if ($coupon->one_use_per_customer && $userId) {
            $alreadyUsed = $this->couponRepository->checkUserCouponUsage($userId, $coupon->id);

            if ($alreadyUsed) {
                throw new \Exception('You have already used this coupon.');
            }
        }

        if ($cartTotal < ($coupon->minimum_purchase_amount ?? 0)) {
            throw new \Exception("Minimum purchase amount is {$coupon->minimum_purchase_amount}.");
        }
        if (!is_null($coupon->maximum_purchase_amount) && $cartTotal > $coupon->maximum_purchase_amount) {
            throw new \Exception("Maximum purchase amount is {$coupon->maximum_purchase_amount}.");
        }
        if ($cartQuantity < ($coupon->minimum_quantity_items ?? 0)) {
            throw new \Exception("Minimum purchase quantity is {$coupon->minimum_quantity_items} items.");
        }
        if (!is_null($coupon->maximum_quantity_items) && $cartQuantity > $coupon->maximum_quantity_items) {
            throw new \Exception("Maximum purchase quantity is {$coupon->maximum_quantity_items} items.");
        }

        $discountAmount = $coupon->discount_value_type === 'percent'
            ? ($cartTotal * $coupon->discount_value) / 100
            : $coupon->discount_value;
            
        // Ensure discount does not exceed cart total
        $discountAmount = min(intval($discountAmount), $cartTotal);

        return [
            'success' => true,
            'discountAmount' => $discountAmount,
            'cartTotalAfterDiscount' => $cartTotal - $discountAmount,
        ];
    }



    // public function getStore()
    // {
    //     $store = auth()->user()->store;
    //     if (!$store) {
    //         throw new ItemNotFoundException('Store not found');
    //     }
    //     return $store;
    // }

}
