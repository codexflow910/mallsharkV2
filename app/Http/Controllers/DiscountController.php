<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Product;
use App\Models\Discount;
use Illuminate\Http\Request;
use App\Models\DiscountUsage;
use App\Services\DiscountsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Repositories\Discounts\Discountsaccess;

class DiscountController extends Controller
{

    private $_reportobj;
    private $_request;
    private $discountsService;

    public function __construct(Request $request, Discountsaccess $discountsaccess, DiscountsService $discountsService)
    {
        $this->_request = $request;
        $this->_reportobj = $discountsaccess;
        $this->discountsService = $discountsService;
    }

    private function getAuthenticatedUserId($request)
    {
        if ($request->is('api/*')) {
            return auth()->user()->user_id;
        } else {
            return Session::get('userData')['user_id'];
        }
    }


    public function Index()
    {
        try {
            $user = $this->getAuthenticatedUserId($this->_request);
            $store = getStorDataUserId($user);
            $storeId = $store->store_id;
            $discounts = Discount::where('store_id', $storeId)->get();
            if($this->_request->is('api/*')){
                if($discounts->isnotempty()){

                    return response()->json([
                        'status' => true,
                        'data' => $discounts
                    ], 200);
                }else{
                    return response()->json([
                        'status' => false,
                        'message' => 'No sales found'
                    ], 200);
                }
                
            }else{
                return view('Store.discounts.index', compact('discounts'));

            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'messages' => $e->getMessage()
            ], 500);
        }
    }
    public function discountList()
    {
        try {
            $user = $this->getAuthenticatedUserId($this->_request);
            $store = getStorDataUserId($user);
            $storeId = $store->store_id;
            $discounts = Discount::where('store_id', $storeId)->where('archive',false)->orderBy('created_at', 'desc')->get();
          
            if($this->_request->is('api/*')){
                if($discounts->isnotempty()){
                    return response()->json([
                        'status' => true,
                        'data' => $discounts
                    ], 200);
                }else{
                    return response()->json([
                        'status' => false,
                        'message' => 'No sales found'
                    ], 200);
                }
                
            }else{
                return view('Store.discounts.discount-list', compact('discounts'))->render();

            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'messages' => $e->getMessage()
            ], 500);
        }
    }
    public function getArchiveDiscounts()
    {
        try {
            $user = $this->getAuthenticatedUserId($this->_request);
            $store = getStorDataUserId($user);
            $storeId = $store->store_id;
            $discounts = Discount::where('store_id', $storeId)->where('archive',true)->get();
            if($this->_request->is('api/*')){
                if($discounts->isnotempty()){
                    return response()->json([
                        'status' => true,
                        'data' => $discounts
                    ], 200);
                }else{
                    return response()->json([
                        'status' => false,
                        'message' => 'No sales found'
                    ], 200);
                }
                
            }else{
                return view('Store.discounts.index', compact('discounts'));

            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'messages' => $e->getMessage()
            ], 500);
        }
    }
    public function discountDetails()
    {
        return view('Store.discounts.discountDetail');
    }

    public function edit($id)
    {
        try {
            $discount = Discount::find($id);
            // dd( $discount);
            if ($discount) {
                return view('Store.discounts.editDetail', compact('discount'));
            }
            return redirect()->route('discounts.index')->with('error', 'Discount not found');
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'messages' => $e->getMessage()
            ], 500);
        }

    }

    public function updateStatus($id)
    {
        try {
            DB::beginTransaction();

            $discount = Discount::find($id);
            $status = $this->_request->input('status');
            $store_id = $discount->store_id;
            $now = Carbon::now();
            $startDate = $discount->start_time && $discount->start_time != "0000-00-00 00:00:00" ? Carbon::parse($discount->start_time)->format('Y-m-d') : null;
            $startTime = $discount->start_time && $discount->start_time != "0000-00-00 00:00:00" ? (Carbon::parse($discount->start_time)->format('H:i:s') == "00:00:00" ? null : Carbon::parse($discount->start_time)->format('H:i:s')) : null;
            $endDate = $discount->end_time && $discount->end_time != "0000-00-00 00:00:00" ? Carbon::parse($discount->end_time)->format('Y-m-d') : null;
            $endTime = $discount->end_time && $discount->end_time != "0000-00-00 00:00:00" ? Carbon::parse($discount->end_time)->format('H:i:s') : null;
            $discountStart = $startDate ? Carbon::parse($startDate . ' ' . $startTime) : null;
            $discountEnd = $endDate ? Carbon::parse($endDate . ' ' . $endTime) : null;
            
            if ($status == 'active') {
              
                if ($endDate) {
                    if ($now->gt($discountEnd)) {
                        return response()->json([
                            'message' => 'Cannot activate because The discount is expired. Please update the dates using the edit functionality to activate this discount.',
                            'status' => false,
                        ], 400);
                    }
                }
            
                $activeDiscounts = Discount::where('store_id', $store_id)
                ->where('status', 'active')
                ->where('id', '!=', $id)
                ->get();
                if ($activeDiscounts->count() > 0) {
                    return response()->json([
                        'message' => 'There are other active discounts for this store. Do you want to deactivate them and activate this one?',
                        'requires_confirmation' => true,
                    ]);
                } else {
                
                    if ($discountStart && $discountStart->lte($now)) {
                        $discount->status = 'active';
                        $discount->state = 'live';
                    } else if ($discountStart && $discountStart->gt($now)) {
                        $discount->status = 'active';
                        $discount->state = 'scheduled';
                    }
                    if ($startDate) {
                        
                        if ($now->gt($discountStart)) {
                            $discount->status_manual_override = true;
                            $this->discountsService->updateProductPrices($store_id, $discount);
                        }
                    }else{
                       

                        $discount->status_manual_override = false; 
                        $this->discountsService->resetProductPrices($store_id, $discount->id);
                    } 
                    $discount->save();
                    DB::commit();
                    return response()->json(['message' => 'Discount activated successfully']);
                }
            } else {
                if($startDate . ' ' . ($startTime) <= $now){
                    $discount->status = 'inActive';
                    $discount->state = 'default';
                   }else if($startDate . ' ' . ($startTime) > $now){
                    $discount->status = 'inActive';
                    $discount->state = 'default';
                   }
                $discount->status_manual_override = false;
                $discount->save();
                $this->discountsService->resetProductPrices($store_id, $discount->id);

                DB::commit();
                return response()->json(['message' => 'Discount deactivated and product prices reset successfully']);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function confirmUpdateStatus($id)
    {
        try {
            DB::beginTransaction();

            $discount = Discount::find($id);
            $store_id = $discount->store_id;
            $now = Carbon::now();
            
            $startDate = $discount->start_time && $discount->start_time != "0000-00-00 00:00:00" ? Carbon::parse($discount->start_time)->format('Y-m-d') : null;
            $startTime = $discount->start_time && $discount->start_time != "0000-00-00 00:00:00" ? (Carbon::parse($discount->start_time)->format('H:i:s') == "00:00:00" ? null : Carbon::parse($discount->start_time)->format('H:i:s')) : null;
            $endDate = $discount->end_time && $discount->end_time != "0000-00-00 00:00:00" ? Carbon::parse($discount->end_time)->format('Y-m-d') : null;
            $endTime = $discount->end_time && $discount->end_time != "0000-00-00 00:00:00" ? Carbon::parse($discount->end_time)->format('H:i:s') : null;
            $discountStart = $startDate ? Carbon::parse($startDate . ' ' . $startTime) : null;
            $discountEnd = $endDate ? Carbon::parse($endDate . ' ' . $endTime) : null;
            $updatedDiscount=Discount::where('store_id', $store_id)
                ->where('status', 'active')
                ->where('id', '!=', $id)->first();
                if(!empty($updatedDiscount)){
                    $updatedDiscount->status = 'inActive';
                    $updatedDiscount->state = 'default';
                    $updatedDiscount->save();
                }
               
                if($discountStart && $discountStart->lte($now)){
                    $discount->status = 'active';
                    $discount->state = 'live';
                   }else if($discountStart && $discountStart->gt($now)){
                    $discount->status = 'active';
                    $discount->state = 'scheduled';
                   }
            if ($startDate) {
                if ($now->gt(Carbon::parse($startDate . ' ' . ($startTime ?? '00:00:00')))) {
                   
                    $discount->status_manual_override = true;
                    $this->discountsService->updateProductPrices($store_id, $discount);
                }else{
                    
                    $discount->status_manual_override = false;
                    $this->discountsService->resetProductPrices($store_id, $updatedDiscount->id);
                }
            }
            $discount->save();

            DB::commit();
            return response()->json(['message' => 'Discount activated']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function archive($id)
    {
        try {
                $discount = Discount::find($id);
                if ($discount) {
                    $this->discountsService->resetProductPrices($discount->store_id, $id);
                    $discount->archive = true;
                    $discount->save();
                    return response()->json(['status' => true,'message' => 'Discount Archived successfully']);
                }
                return response()->json(['status' => false ,'message' => 'Discount not found'], 404);
            } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'messages' => $e->getMessage()
            ], 500);
        }
    }
    public function unArchive($id)
    {
        try {
                $discount = Discount::find($id);
                if ($discount) {
                    $discount->archive = false;
                    $discount->status = 'inActive';
                    $discount->state = 'default';
                    $discount->save();
                    return response()->json(['status' => true,'message' => 'Discount unArchived successfully']);
                }
                return response()->json(['status' => false ,'message' => 'Discount not found'], 404);
            } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'messages' => $e->getMessage()
            ], 500);
        }
    }
    public function save()
    {
        try {
            $user = $this->getAuthenticatedUserId($this->_request);
            $store = getStorDataUserId($user);
            $storeId = $store->store_id;
            $validator = Validator::make($this->_request->all(), [
                'discountActive' => 'required|in:Sales,Coupons',
                'autoDiscountTitle' => 'required_if:discountActive,Sales',
                'discountCode' => 'required_if:discountActive,Coupons',
                'discount_type' => 'required|in:percentage,fixed',
                'discount_applicable' => 'nullable|in:wholeInventory,product,category',
                'start_date' => 'required|date',
                'start_time' => 'nullable|date_format:H:i',
                'end_date' => 'nullable|date',
                'end_time' => 'nullable|date_format:H:i',
                'percentage_value' => 'nullable|required_if:discount_type,percentage|numeric|min:0|max:100',
                'fixed_value' => 'nullable|required_if:discount_type,fixed|numeric|min:0',
                'totalUses' => 'nullable|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $discount = $this->discountsService->saveDiscount($this->_request->all(), $storeId);
            return response()->json([
                'status' => true,
                'messages' => 'discount saved successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'messages' => $e->getMessage()
            ], 500);
        }

    }
    public function saveAndActive()
    {
        try {
            $user = $this->getAuthenticatedUserId($this->_request);
            $store = getStorDataUserId($user);
            $storeId = $store->store_id;
            $validator = Validator::make($this->_request->all(), [
                'discountActive' => 'required|in:Sales,Coupons',
                'autoDiscountTitle' => 'required_if:discountActive,Sales',
                'discountCode' => 'required_if:discountActive,Coupons',
                'discount_type' => 'required|in:percentage,fixed',
                'discount_applicable' => 'nullable|in:wholeInventory,product,category',
                'start_date' => 'required|date',
                'start_time' => 'nullable|date_format:H:i',
                'end_date' => 'nullable|date',
                'end_time' => 'nullable|date_format:H:i',
                'percentage_value' => 'nullable|required_if:discount_type,percentage|numeric|min:0|max:100',
                'fixed_value' => 'nullable|required_if:discount_type,fixed|numeric|min:0',
                'totalUses' => 'nullable|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $activeDiscounts = Discount::where('store_id', $storeId)
                ->where('status', 'active')
                ->get();
                if ($activeDiscounts->count() > 0) {
                    return response()->json([
                        'message' => 'There are other active discounts for this store. Do you want to deactivate them and activate this one?',
                        'requires_confirmation' => true,
                    ]);
                }
            $discount = $this->discountsService->saveDiscount($this->_request->all(), $storeId);
                $discount = Discount::find($discount->id);
                $store_id = $discount->store_id;
                $now = Carbon::now();
                $startDate = $discount->start_time && $discount->start_time != "0000-00-00 00:00:00" ? Carbon::parse($discount->start_time)->format('Y-m-d') : null;
                $startTime = $discount->start_time && $discount->start_time != "0000-00-00 00:00:00" ? (Carbon::parse($discount->start_time)->format('H:i:s') == "00:00:00" ? null : Carbon::parse($discount->start_time)->format('H:i:s')) : null;
                $endDate = $discount->end_time && $discount->end_time != "0000-00-00 00:00:00" ? Carbon::parse($discount->end_time)->format('Y-m-d') : null;
                $endTime = $discount->end_time && $discount->end_time != "0000-00-00 00:00:00" ? Carbon::parse($discount->end_time)->format('H:i:s') : null;
                if ($endDate) {
                    if ($now->gt(Carbon::parse($endDate . ' ' . ($endTime ?? '23:59:59')))) {
                        return response()->json([
                            'message' => 'Cannot activate because The discount is expired. Please update the dates using the edit functionality to activate this discount.',
                            'status' => false,
                        ], 400);
                    }
                }
               if($startDate . ' ' . ($startTime) <= $now){
                $discount->status = 'active';
                $discount->state = 'live';
               }else if($startDate . ' ' . ($startTime) > $now){
                $discount->status = 'active';
                $discount->state = 'scheduled';
               }
                    
                    if ($startDate) {
                        if ($now->gt(Carbon::parse($startDate . ' ' . ($startTime ?? '00:00:00')))) {
                            $discount->status_manual_override = true;
                            $this->discountsService->updateProductPrices($store_id, $discount);
                        }
                    }else{
                        $discount->status_manual_override = false; 
                        $this->discountsService->resetProductPrices($store_id, $discount->id);
                    } 
                    $discount->save();
                    DB::commit();
                return response()->json(['status' => true,'message' => 'Discount activated successfully']);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'messages' => $e->getMessage()
            ], 500);
        }

    }
    public function saveAndActiveConfirm()
    {
        try {
            $user = $this->getAuthenticatedUserId($this->_request);
            $store = getStorDataUserId($user);
            $storeId = $store->store_id;
            $validator = Validator::make($this->_request->all(), [
                'discountActive' => 'required|in:Sales,Coupons',
                'autoDiscountTitle' => 'required_if:discountActive,Sales',
                'discountCode' => 'required_if:discountActive,Coupons',
                'discount_type' => 'required|in:percentage,fixed',
                'discount_applicable' => 'nullable|in:wholeInventory,product,category',
                'start_date' => 'required|date',
                'start_time' => 'nullable|date_format:H:i',
                'end_date' => 'nullable|date',
                'end_time' => 'nullable|date_format:H:i',
                'percentage_value' => 'nullable|required_if:discount_type,percentage|numeric|min:0|max:100',
                'fixed_value' => 'nullable|required_if:discount_type,fixed|numeric|min:0',
                'totalUses' => 'nullable|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $discount = $this->discountsService->saveDiscount($this->_request->all(), $storeId);
            $data = $this->_request->all();
           
                $discount = Discount::find($discount->id);
                $store_id = $discount->store_id;
                $now = Carbon::now();
                $startDate = $discount->start_time && $discount->start_time != "0000-00-00 00:00:00" ? Carbon::parse($discount->start_time)->format('Y-m-d') : null;
                $startTime = $discount->start_time && $discount->start_time != "0000-00-00 00:00:00" ? (Carbon::parse($discount->start_time)->format('H:i:s') == "00:00:00" ? null : Carbon::parse($discount->start_time)->format('H:i:s')) : null;
                $endDate = $discount->end_time && $discount->end_time != "0000-00-00 00:00:00" ? Carbon::parse($discount->end_time)->format('Y-m-d') : null;
                $endTime = $discount->end_time && $discount->end_time != "0000-00-00 00:00:00" ? Carbon::parse($discount->end_time)->format('H:i:s') : null;
            
                if($startDate . ' ' . ($startTime) <= $now){
                    $discount->status = 'active';
                    $discount->state = 'live';
                   }else if($startDate . ' ' . ($startTime) > $now){
                    $discount->status = 'active';
                    $discount->state = 'scheduled';
                   }
                if ($startDate) {
                    if ($now->gt(Carbon::parse($startDate . ' ' . ($startTime ?? '00:00:00')))) {
                        $discount->status_manual_override = true;
                        $this->discountsService->updateProductPrices($store_id, $discount);
                    }
                }else{
                    $discount->status_manual_override = false; 
                    $this->discountsService->resetProductPrices($store_id, $discount->id);
                } 
                $discount->save();
                $updatedDiscount=Discount::where('store_id', $storeId)
                ->where('status', 'active')
                ->where('id', '!=', $discount->id)->first();
                if(!empty($updatedDiscount)){
                    $updatedDiscount->status = 'inActive';
                    $updatedDiscount->state = 'default';
                    $updatedDiscount->save();
                }
                DB::commit();
                return response()->json(['status' => true,'message' => 'Discount activated successfully']);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'messages' => $e->getMessage()
            ], 500);
        }

    }
    public function update($id)
    {
        try {
            // return response()->json($this->_request->all());
            $validator = Validator::make($this->_request->all(), [
                'discountActive' => 'required|in:Sales,Coupons',
                'autoDiscountTitle' => 'required_if:discountActive,Sales',
                'discountCode' => 'required_if:discountActive,Coupons',
                'discount_type' => 'required|in:percentage,fixed',
                'discount_applicable' => 'nullable|in:wholeInventory,product,category',
                'start_date' => 'required|date',
                'start_time' => 'nullable|date_format:H:i',
                'end_date' => 'nullable|date',
                'end_time' => 'nullable|date_format:H:i',
                'percentage_value' => 'nullable|required_if:discount_type,percentage|numeric|min:0|max:100',
                'fixed_value' => 'nullable|required_if:discount_type,fixed|numeric|min:0',
                'totalUses' => 'nullable|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            DB::beginTransaction();
            $updatedDiscount = $this->discountsService->updateDiscount($this->_request->all(), $id);
            // return response()->json($updatedDiscount);
            if ($updatedDiscount) {
                $this->discountsService->updateProductPricesAfterDiscountUpdate($updatedDiscount);
            }
            DB::commit();
            
            return response()->json([
                'status' => true,
                'messages' => 'Discount saved and product prices updated successfully'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'messages' => $e->getMessage()
            ], 500);
        }
    }

    public function updateAndActive($id)
    {
        try {
            // return response()->json($this->_request->all());
            $validator = Validator::make($this->_request->all(), [
                'discountActive' => 'required|in:Sales,Coupons',
                'autoDiscountTitle' => 'required_if:discountActive,Sales',
                'discountCode' => 'required_if:discountActive,Coupons',
                'discount_type' => 'required|in:percentage,fixed',
                'discount_applicable' => 'nullable|in:wholeInventory,product,category',
                'start_date' => 'required|date',
                'start_time' => 'nullable|date_format:H:i',
                'end_date' => 'nullable|date',
                'end_time' => 'nullable|date_format:H:i',
                'percentage_value' => 'nullable|required_if:discount_type,percentage|numeric|min:0|max:100',
                'fixed_value' => 'nullable|required_if:discount_type,fixed|numeric|min:0',
                'totalUses' => 'nullable|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            DB::beginTransaction();
            $user = $this->getAuthenticatedUserId($this->_request);
            $store = getStorDataUserId($user);
            $storeId = $store->store_id;
            $activeDiscounts = Discount::where('store_id', $storeId)
                ->where('status', 'active')
                ->where('id', '!=', $id)
                ->get();
                if ($activeDiscounts->count() > 0) {
                    return response()->json([
                        'message' => 'There are other active discounts for this store. Do you want to deactivate them and activate this one?',
                        'requires_confirmation' => true,
                    ]);
                }
            $updatedDiscount = $this->discountsService->updateDiscount($this->_request->all(), $id);
            // return response()->json($updatedDiscount);
            if ($updatedDiscount) {
                $this->discountsService->updateProductPricesAfterDiscountUpdate($updatedDiscount);
            }
            $discount = Discount::find($id);
                $store_id = $discount->store_id;
                $now = Carbon::now();
                $startDate = $discount->start_time && $discount->start_time != "0000-00-00 00:00:00" ? Carbon::parse($discount->start_time)->format('Y-m-d') : null;
                $startTime = $discount->start_time && $discount->start_time != "0000-00-00 00:00:00" ? (Carbon::parse($discount->start_time)->format('H:i:s') == "00:00:00" ? null : Carbon::parse($discount->start_time)->format('H:i:s')) : null;
                $endDate = $discount->end_time && $discount->end_time != "0000-00-00 00:00:00" ? Carbon::parse($discount->end_time)->format('Y-m-d') : null;
                $endTime = $discount->end_time && $discount->end_time != "0000-00-00 00:00:00" ? Carbon::parse($discount->end_time)->format('H:i:s') : null;
                if ($endDate) {
                    if ($now->gt(Carbon::parse($endDate . ' ' . ($endTime ?? '23:59:59')))) {
                        return response()->json([
                            'message' => 'Cannot activate because The discount is expired. Please update the dates using the edit functionality to activate this discount.',
                            'status' => false,
                        ], 400);
                    }
                }
               if($startDate . ' ' . ($startTime) <= $now){
                $discount->status = 'active';
                $discount->state = 'live';
               }else if($startDate . ' ' . ($startTime) > $now){
                $discount->status = 'active';
                $discount->state = 'scheduled';
               }
                    
                    if ($startDate) {
                        if ($now->gt(Carbon::parse($startDate . ' ' . ($startTime ?? '00:00:00')))) {
                            $discount->status_manual_override = true;
                            $this->discountsService->updateProductPrices($store_id, $discount);
                        }
                    }else{
                        $discount->status_manual_override = false; 
                        $this->discountsService->resetProductPrices($store_id, $discount->id);
                    } 
                    $discount->save();
            DB::commit();
            
            return response()->json([
                'status' => true,
                'messages' => 'Discount saved and product prices updated successfully'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'messages' => $e->getMessage()
            ], 500);
        }
    }
    public function updateAndActiveConfirm($id)
    {
        try {
            // return response()->json($this->_request->all());
            $validator = Validator::make($this->_request->all(), [
                'discountActive' => 'required|in:Sales,Coupons',
                'autoDiscountTitle' => 'required_if:discountActive,Sales',
                'discountCode' => 'required_if:discountActive,Coupons',
                'discount_type' => 'required|in:percentage,fixed',
                'discount_applicable' => 'nullable|in:wholeInventory,product,category',
                'start_date' => 'required|date',
                'start_time' => 'nullable|date_format:H:i',
                'end_date' => 'nullable|date',
                'end_time' => 'nullable|date_format:H:i',
                'percentage_value' => 'nullable|required_if:discount_type,percentage|numeric|min:0|max:100',
                'fixed_value' => 'nullable|required_if:discount_type,fixed|numeric|min:0',
                'totalUses' => 'nullable|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            DB::beginTransaction();
            $user = $this->getAuthenticatedUserId($this->_request);
            $store = getStorDataUserId($user);
            $storeId = $store->store_id;
            $updatedDiscount = $this->discountsService->updateDiscount($this->_request->all(), $id);
            // return response()->json($updatedDiscount);
            if ($updatedDiscount) {
                $this->discountsService->updateProductPricesAfterDiscountUpdate($updatedDiscount);
            }
            $discount = Discount::find($id);
                $store_id = $discount->store_id;
                $now = Carbon::now();
                $startDate = $discount->start_time && $discount->start_time != "0000-00-00 00:00:00" ? Carbon::parse($discount->start_time)->format('Y-m-d') : null;
                $startTime = $discount->start_time && $discount->start_time != "0000-00-00 00:00:00" ? (Carbon::parse($discount->start_time)->format('H:i:s') == "00:00:00" ? null : Carbon::parse($discount->start_time)->format('H:i:s')) : null;
                $endDate = $discount->end_time && $discount->end_time != "0000-00-00 00:00:00" ? Carbon::parse($discount->end_time)->format('Y-m-d') : null;
                $endTime = $discount->end_time && $discount->end_time != "0000-00-00 00:00:00" ? Carbon::parse($discount->end_time)->format('H:i:s') : null;
                if ($endDate) {
                    if ($now->gt(Carbon::parse($endDate . ' ' . ($endTime ?? '23:59:59')))) {
                        return response()->json([
                            'message' => 'Cannot activate because The discount is expired. Please update the dates using the edit functionality to activate this discount.',
                            'status' => false,
                        ], 400);
                    }
                }
               if($startDate . ' ' . ($startTime) <= $now){
                $discount->status = 'active';
                $discount->state = 'live';
               }else if($startDate . ' ' . ($startTime) > $now){
                $discount->status = 'active';
                $discount->state = 'scheduled';
               }
                    
                    if ($startDate) {
                        if ($now->gt(Carbon::parse($startDate . ' ' . ($startTime ?? '00:00:00')))) {
                            $discount->status_manual_override = true;
                            $this->discountsService->updateProductPrices($store_id, $discount);
                        }
                    }else{
                        $discount->status_manual_override = false; 
                        $this->discountsService->resetProductPrices($store_id, $discount->id);
                    } 
                    $discount->save();
                   
                    $updatedDiscount=Discount::where('store_id', $storeId)
                ->where('status', 'active')
                ->where('id', '!=', $discount->id)->first();
                // dd($updatedDiscount);
                if(!empty($updatedDiscount)){
                    $updatedDiscount->status = 'inActive';
                    $updatedDiscount->state = 'default';
                    $updatedDiscount->save();
                }
               
            DB::commit();
            
            return response()->json([
                'status' => true,
                'messages' => 'Discount saved and product prices updated successfully'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'messages' => $e->getMessage()
            ], 500);
        }
    }
    public function fetchProductsWithVariation()
    {
        try {
            $userId = $this->getAuthenticatedUserId($this->_request);
            $Products = $this->discountsService->fetchProductsWithVariation($userId);
            return response()->json([
                'status' => true,
                'data' => [
                    'products' => $Products->get()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'messages' => $e->getMessage()
            ], 500);
        }
    }

    public function getCategoryListForDiscounts()
    {
        try {
            $sub = $this->_request->get('sub');
            $userId = $this->getAuthenticatedUserId($this->_request);
            $categories = $this->discountsService->getCategories($userId, $sub);
            return response()->json([
                'status' => true,
                'data' => [
                    'categorySales' => $categories,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'messages' => $e->getMessage()
            ], 500);
        }
    }

    public function applyCoupon(Request $request)
    {
        // Validate request data
        $request->validate([
            'coupon_code' => 'required|string',
            'store_id' => 'required|integer',
            'cart_total' => 'nullable|numeric|min:0',
            'cart_quantity' => 'nullable|integer|min:0',
        ]);

        $couponCode = $request->coupon_code;
        $storeId = $request->store_id;
        $cartTotal = $request->cart_total;
        $cartQuantity = $request->cart_quantity;

        // Call the DiscountService to apply coupon logic
        $result = $this->discountsService->applyCoupon($couponCode, $storeId, $cartTotal, $cartQuantity);

        if (!$result['success']) {
            return response()->json(['success' => false, 'message' => $result['message']]);
        }

        // If the coupon is applied successfully, return the response
        return response()->json([
            'success' => true,
            'message' => 'Coupon applied successfully!',
            'discountAmount' => $result['discountAmount'],
            'cartTotal' => $result['cartTotal'],
            'storeId' => $storeId,
            'couponCode' => $couponCode
        ]);
    }

}
