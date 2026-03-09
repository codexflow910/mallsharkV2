<?php

namespace Packages\Discount\Repositories\Eloquent;


use Exception;
use InvalidArgumentException;
use App\Models\AttachmentSizes;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Packages\Discount\Models\Discount;
use Packages\Discount\Models\DiscountUsage;
use Illuminate\Support\ItemNotFoundException;
use Packages\Discount\Contracts\BaseApiResponse;
use Packages\Discount\Contracts\DiscountRepositoryInterface;

class CouponRepository extends DiscountRepository implements DiscountRepositoryInterface
{
    use BaseApiResponse;
    protected $model;

    public function __construct(Discount $model)
    {
        $this->model = $model;
    }

    public function getCouponByCode($couponCode)
    {
        return Discount::where('code', $couponCode)->first();
    }

    public function checkUserCouponUsage($userId, $couponId)
    {
        return DiscountUsage::where('user_id', $userId)
            ->where('discount_id', $couponId)
            ->exists();
    }

}
