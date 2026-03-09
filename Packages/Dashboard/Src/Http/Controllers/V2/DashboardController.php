<?php

namespace Packages\Dashboard\Http\Controllers\V2;


use Exception;
use Packages\Auth\Models\V2\User;
use Packages\Auth\Models\V2\DeviceToken;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Dotenv\Exception\ValidationException;
use Illuminate\Support\ItemNotFoundException;
use Packages\Dashboard\Contracts\V2\BaseApiResponse;
use Packages\Dashboard\Services\V2\DashboardService;
use Packages\SocialLogin\Http\Requests\V2\GoogleLoginRequest;
use Packages\SocialLogin\Http\Requests\V2\FacebookLoginRequest;

class DashboardController extends Controller
{
    use BaseApiResponse;


    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;

    }

    public function storeInfo(Request $request)
    {
        try {
            $data = $this->dashboardService->storeInfo();
            return $this->successResponse($data, 'Store information fetched successfully');
        } catch (Exception $e) {
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
            
        }
    }

    public function ordersStats(Request $request)
    {
        try {
            $timeFilter = $request->query('filter', 'all_time');
            if($timeFilter != 'all_time' && $timeFilter != 'this_week' && $timeFilter != 'last_week' && $timeFilter != 'this_month' && $timeFilter != 'today'){
                throw new ValidationException('Invalid time filter');
            }
            $orderCounts  = $this->dashboardService->ordersStats($timeFilter);

            return $this->successResponse($orderCounts, 'Orders count fetched successfully');

        } catch (Exception $e) {

            if ($e instanceof ValidationException) {
                return $this->validationErrorResponse($e->getMessage());
            }
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function productStats()
    {
        try {
            $productCounts = $this->dashboardService->productStats();
            return $this->successResponse($productCounts, 'Product counts fetched successfully');
        } catch (Exception $e) {

            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function orderPercentages(Request $request)
    {
        try {
            $request->validate([
                'filter' => 'nullable|in:today,this_week,last_week,this_month,all_time',
            ]);

            $filter = $request->get('filter', 'all_time');
            $orderPercentages = $this->dashboardService->getOrderPercentages($filter);

            return $this->successResponse($orderPercentages, 'Order percentages fetched successfully');
        } catch (Exception $e) {

            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function discountLive()
    {
        try{
            $discount = $this->dashboardService->discountLive();

            if ($discount == null) {
                throw new ItemNotFoundException('There is no live discount');
            }
            return $this->successResponse($discount, 'Discount live fetched successfully');

        } catch (Exception $e) {

            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }

    }

}
