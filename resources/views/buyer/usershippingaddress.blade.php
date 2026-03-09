@extends('layouts.new-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Buyer info')
@include('buyer.sidebar')
<main>
    <?php
    //print_rr($buyerShippingData);
//    exit;
    ?>
    <script src="{{ URL::asset('assets/js/jquery.validate.js',env('HTTPS_STATUS')) }}"></script>
    <div class="dshbrdin">
        <div class="container">
            <div class="row">
                <div class="col-md-12"> 
                    <div class="dshodrs">
                        <!-- Product Sorting Starts -->
                        <div class="row srtbx"><div class="col-md-6"><h3 class=" ">Shipping Address</h3></div></div>
                        <div class="row">
                            <form class="col-md-6 usrbsc mb-15" action="" method="post" id="shipping_form">
                                @if(Session::has('shippAddressmessage'))
                                <div class="alert alert-success alert-success-mbt" id="success_message" role="alert">
                                    {{ Session::get('shippAddressmessage') }}
                                    <script>
                                        setTimeout(function () {
                                            jQuery("#success_message").fadeOut('slow');
                                        }, 3000);
                                    </script>
                                </div>
                                @endif
                                {{ csrf_field() }}
                                <div class="row">
                                    <div class="col-sm-12">       				
                                        <div class="form-group">
                                            <label>Full Name</label>
                                            <input maxlength="40" type="text" name="full_name" value="<?php echo ($buyerShippingData['full_name']) ? $buyerShippingData['full_name'] : ""; ?>" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Country</label>
                                            <select class="form-control" name="user_country" id="user_country">
                                                <option value="">Please select</option>
                                                <?php
                                                $Canada_S = '';
                                                $usa_S = '';
                                                if (isset($buyerShippingData['country']) && $buyerShippingData['country'] == '1') {
                                                    $usa_S = 'selected';
                                                }
                                                if (isset($buyerShippingData['country']) && $buyerShippingData['country'] == '2') {
                                                    $Canada_S = 'selected';
                                                }

                                                $countries=getAllCountries();
                                                if(!empty($countries)){
                                                foreach($countries as $cn){
                                                    if($cn['code'] == 'US' ){
                                                $selec='';
                                                if(isset($buyerShippingData['country']) && $buyerShippingData['country']!='' && $buyerShippingData['country']==$cn['id'] ){
                                                    $selec='selected';
                                                }
                                                ?>
                                                <!-- <option value="<?php echo $cn['id']; ?>" <?php echo $selec; ?>><?php echo $cn['name']; ?></option> -->
                                                <?php }
                                                
                                            }

                                                }
                                                echo '<option  value="1" ' . $usa_S . '>USA</option>';
                                                echo '<option  value="2" ' . $Canada_S . '>CANADA</option>';
                                                ?>

                                            </select>
                                            @if ($errors->has('user_country'))
                                            <div class="error">{{ $errors->first('user_country') }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <?php
                                            ?>
                                            <label>State</label>
                                                <select class="form-control" name="user_state" id="user_state">
                                                    <?php

                                                    if(isset($buyerShippingData['shipping_state']) && $buyerShippingData['shipping_state']!=''){
                                                    $states=getStatesOfCountry($buyerShippingData['shipping_state'][0]['country_id']);
                                                    foreach($states as $st){
                                                    $selected='';
                                                    if(isset($buyerShippingData['state']) && $buyerShippingData['state']!='' && $buyerShippingData['state']==$st['state_id'] ){
                                                    $selected='selected';
                                                    }
                                                    ?>
                                                    <option value="<?php echo $st['state_id'] ?>" <?php echo $selected; ?>><?php echo $st['state_name']; ?></option>
                                                    <?php  }
                                                    }?>
                                                    <option value="">Please select</option>
                                                </select>
                                        </div>
                                        @if ($errors->has('user_state'))
                                        <div class="error">{{ $errors->first('user_state') }}</div>
                                        @endif
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Street</label>
                                            <input maxlength="90" type="text" name="street" value="<?php echo ($buyerShippingData['street'] ? $buyerShippingData['street'] : '') ?>" class="form-control">
                                        </div>
                                        @if ($errors->has('street'))
                                        <div class="error">{{ $errors->first('street') }}</div>
                                        @endif
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>City</label>
                                            <input type="text" maxlength="40" name="city" value="<?php echo ($buyerShippingData['city'] ? $buyerShippingData['city'] : '') ?>" class="form-control">
                                            
                                        </div>
                                    </div> 
                                    <div class="col-sm-6">
                                        <div class="form-group" id="staticParent">
                                            <label>Post Code</label>
                                            <input maxlength="6" type="text" value="<?php echo ($buyerShippingData['post_code'] ? $buyerShippingData['post_code'] : '') ?>" id="post_code" name="post_code" class="form-control">
                                            @if ($errors->has('post_code'))
                                            <div class="error">{{ $errors->first('post_code') }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Flat / Other </label>
                                            <input type="text" maxlength="40" name="flat_other" value="<?php echo ($buyerShippingData['flat_other'] ? $buyerShippingData['flat_other'] : '') ?>" class="form-control">
                                            
                                        </div>
                                    </div> 
                                </div>   
                                <input type="hidden" name="shipping_key" id="shipping_key" value="<?php echo ($buyerShippingData['sh_id']) ? Crypt::encryptString($buyerShippingData['sh_id']) : ""; ?>"/>
                                <input type="submit" name="submit" class="btn btn btn-primary btn btn-primary-background text-white width100 mt-10" value="Save">
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script>
    $(document).ready(function () {
        $("#user_country").on("change", function () {
            data = {};
            var countryCode = $(this).val();
            data['country_id'] = countryCode;
            sendreq(data, 'buyer', 'state', 'getStates');
        });
    });
    function getStates(data) {
        $("#user_state").html(data);
    }
    function getCities(data) {
        $("#user_city").html(data);
    }

</script>
<script>
    $(document).ready(function () {
        jQuery.validator.addMethod("lettersonly", function (e, r) {
            return this.optional(r) || /^[a-z ]+$/i.test(e)
        }, "Only letters are allowed."),
                jQuery.validator.addMethod("numbersonly", function (e, r) {
                    return this.optional(r) || /^[0-9 +]+$/i.test(e)
                }, "Only numbers are allowed."),
                $("#shipping_form").validate({
            errorElement: "span",
            rules: {
                full_name: {required: true, minlength: 4, lettersonly: !0,
                    maxlength: 25},
                user_country: {required: true
                },
                user_state: {required: true},
                post_code: {required: true, minlength: 3, numbersonly: !0,
                    maxlength: 11},
                user_phone: {required: true},
                street: {required: true},
                city: {required: true,minlength: 4, maxlength: 25},
                // flat_other: {required: true},
            },
            messages: {
                full_name: {required: "Name is required."},
                user_country: {required: "Country is required."},
                user_state: {required: "State is required."},
                user_phone: {required: "Phone no is required."},
                post_code: {required: "Zip code is required."},
                street: {required: "Street address is required."},
                city: {required: "City is required."},
                // flat_other: {required: "Flat rate is required."},
            },
            errorPlacement: function (error, element) {
                error.appendTo(element.closest('.form-group'));
            },
            submitHandler: function (form) {
                form.submit();
            }
        });
    });
</script>
@endsection