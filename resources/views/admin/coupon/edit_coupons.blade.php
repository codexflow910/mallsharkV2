@extends('layouts.new-main')
@section('content')
@section('title', 'Coupon admin')
<section class="admin-dashboard dshbrdin buyer mt-0 user-management manage-categories-section">
    @include('admin.adminsidebar')

<div class="container">
      
    <div class="row">
     
    <div class="col-md-12 mt-5">
        @if (Session::has('error'))
        <div class="col-sm-12">
            <div class="alert alert-danger">
                <ul>
                    <li>{!! Session::get('error') !!}</li>
                </ul>
            </div>
        </div>
        @endif
    <form method="POST" action="{{URL::to('/admin/coupon-create')}}">
      @csrf
        <div class="mt-5 mb-5 col-md-6">
        <div class="form-group">
          <label for="exampleInputEmail1">Coupon Code</label>
          <div class="d-flex">
            <input type="text" class="form-control" id="codename"  name="code_name" value="{{$coupon->code_name}}"   placeholder="Type Code"> &nbsp;&nbsp;&nbsp; <span  onclick="randomString();" class="btn btn-primary">Generate Coupon</span>  
          </div>  
           
        </div>
        <div class="form-group">
          <label for="exampleInputEmail1">Short Description</label>
          <textarea class="form-control"  name="description" rows="3"  placeholder="Type Description">{{$coupon->description}} </textarea>
        </div>
           
        </div>   

       <div class="mt-5 mb-5 col-md-4">
        <h5>General Coupon Setting</h5>
        <div class="form-group">
        <label for="exampleInputEmail1">Discount Type</label>
         <select class="form-control" name="type">
            <option value="fixprice" <?php if($coupon->type == 'fixprice') { ?> selected="selected"<?php } ?>>Fixed Cart Discount</option>
            <option value="percent" <?php if($coupon->type == 'percent') { ?> selected="selected"<?php } ?>>Percentage Discount</option>
            
          </select>
        </div> 
        <div class="form-group">
          <label for="exampleInputEmail1">Coupon Amount </label>
          <input type="number" class="form-control" name="value_discount" value="{{$coupon->value_discount}}"  placeholder="$$$"> 
        </div>
        <div class="form-group">
          <label for="exampleInputEmail1">Coupon Expiry Date </label>
          <input type="date" class="form-control" name="end_date"  placeholder="DD-MM-YY" value="{{Carbon\Carbon::parse($coupon->end_date)->format('Y-m-d H:i:s')}}"> 
        </div>
        <div class="form-group">
          <label for="exampleInputEmail1">Allow Free Shipping</label><br>
          <input class="form-check-input" type="checkbox" name="free_shipping" style="left:0px;" value="{{$coupon->free_shipping}}">  
          <p style="font-size:12px; line-height: 16px; position: relative; top:3px">Chek this box if the coupon grants free shipping. A free shipping method must be enabled in US and be set to require " a valid free shipping coupon" (see the "free shipping requires" setting).</p>
        </div>    
        </div>
        
       <!-- <div class="mt-5 mb-5 col-md-4">
        <h5>Usage Restriction</h5>
        <div class="form-group">
        <label for="exampleInputEmail1">Minimum Spend</label>
        <input type="text" class="form-control"   placeholder="$$$">     
        </div> 
        <div class="form-group">
        <label for="exampleInputEmail1">Maximum Spend</label>
        <input type="text" class="form-control"   placeholder="$$$">     
        </div>
        <div class="form-group">
          <label for="exampleInputEmail1">Individual Use Only</label><br>
          <input class="form-check-input" type="checkbox" value="" id="defaultCheck1">  
          <p style="font-size:12px; line-height: 16px; position: relative; top:3px">Chek this box if the coupon cannot be used in conjunction with other coupons</p>
        </div>  
        </div>
        !-->
        <div class="mt-5 mb-5 col-md-4">
        <h5>Usage Limits</h5>
        <div class="form-group">
        <label for="exampleInputEmail1">Usage Limit Per Coupon</label>
        <input type="number" class="form-control" name="limit_number"  value="{{$coupon->limit_number}}"  placeholder="Unlimited Usage">     
        </div> 
        <div class="form-group">
        <label for="exampleInputEmail1">Usage Limit Per User</label>
        <input type="number" class="form-control" name="limit_user"  value="{{$coupon->limit_user}}" placeholder="Unlimied Usage">     
        </div> 
        </div>
        
        <div class="mt-5 mb-5 col-md-4"> 
        <button type="submit" class="btn btn-primary">Save Settings</button>    
        </div>
        
    </form> 
        

     
   </div>
   </div>
</div>

<!-- dshbrdinadmin -->

</section>
<script>

function randomString() {
    var chars = "0123456789";
    var string_length = 4;
    var randomstring = '';
    for (var i = 0; i < string_length; i++) {
        var rnum = Math.floor(Math.random() * chars.length);
        randomstring += chars.substring(rnum, rnum + 1);
    }
    document.getElementById('codename').value = 'SHOP' + randomstring+'ZIT';
}
</script>
@endsection