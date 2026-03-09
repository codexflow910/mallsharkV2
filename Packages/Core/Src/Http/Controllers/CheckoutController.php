<?php

namespace Packages\Core\Http\Controllers;

use Exception;
use Packages\Order\Models\StoreOrder;
use App\Http\Controllers\Controller;
use Packages\Core\Services\CoreService;
use Packages\Core\Contracts\BaseApiResponse;
use Illuminate\Support\ItemNotFoundException;
use Packages\Core\Http\Requests\CheckoutRequest;
use Illuminate\Http\Request;
class CheckoutController extends Controller
{
    use BaseApiResponse;
    protected $coreService;

    public function __construct(CoreService $coreService)
    {
        $this->coreService = $coreService;
    }

    public function getCheckout(CheckoutRequest $request)
    {
        try {
            $data = $request->validated();

            $response = $this->coreService->getCheckout($data);

            return $response;
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function paymentReceived(Request $request)
    {
        try {
            $response = $this->coreService->paymentReceived($request);

            return $response;
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
    public function buyNow(CheckoutRequest $request){
        try{
            $data = $request->all();
            $response = $this->coreService->makeCheckoutData($data);
            return $response;
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
    public function OrderConfirmation()
    {
        $id = request()->query('id');
        $sessionExists = StoreOrder::where('session_id', $id)->exists();
        if (!$sessionExists) {
            return response()->json([
                'status' => false,
                'success_url' =>  'myapp://payment-cancel',
            ]);
        }
        
        $storeOrders = StoreOrder::where('session_id',$id)->where('charge_status','unpaid')->get();
       
        if($storeOrders->isEmpty()) {
           
            return response()->json([
                'status' => true,
                'success_url' =>  'myapp://payment-success',
            ]);
        }else{
            return response()->json([
                'status' => false,
                'success_url' =>  'myapp://payment-cancel',
            ]);
        }
        
        
    }
}
