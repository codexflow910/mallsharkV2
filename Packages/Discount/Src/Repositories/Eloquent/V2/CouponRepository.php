<?php

namespace Packages\Discount\Repositories\Eloquent\V2;


use Exception;
use InvalidArgumentException;
use App\Models\AttachmentSizes;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Packages\Discount\Models\V2\Discount;
use Packages\Discount\Models\V2\DiscountUsage;
use Illuminate\Support\ItemNotFoundException;
use Packages\Discount\Contracts\V2\BaseApiResponse;
use Packages\Discount\Contracts\V2\DiscountRepositoryInterface;

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
