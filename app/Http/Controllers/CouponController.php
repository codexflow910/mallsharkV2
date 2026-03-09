<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $coupons = Coupon::paginate(10);
        return view('admin.coupon.all_coupons',compact('coupons'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.coupon.create_coupon');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       // dd($request);
        $request->validate([
            'code_name' => 'required',
            'value_discount' => 'required',
            'end_date' => 'required',
        ]);

        Coupon::create($request->all());
        return redirect()->route('coupon.index')
            ->with('success', 'Coupon Created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $coupon = Coupon::findOrFail($id);
        return view('admin.coupon.edit_coupons', compact('coupon'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $coupon = Coupon::find($id);
        $coupon->question = $request->input('code_name');
        $coupon->answer = $request->input('description');
        $coupon->type = $request->input('type');
        $coupon->type = $request->input('end_date');
        $coupon->type = $request->input('value_discount');
        $coupon->type = $request->input('free_shipping');
        $coupon->type = $request->input('limit_number');
        $coupon->type = $request->input('limit_user');
        $coupon->update();
        if($coupon){
            return redirect()->route('coupon.index')->with('updatesuccess', 'Coupon Updated Successfully');
        }else{
            return redirect()->route('coupon.editCoupon')->with('error', 'Something Went Wrong PLease try again');

        }
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
       
        DB::table('coupons')->where('id',$request->id)->delete();
        return response()->json(
            [
                'success' => true,
                'message' => 'Proof! Your Coupon has been deleted!'
            ]
        );
    }
}
