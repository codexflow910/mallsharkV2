<style>
    #save_shipping_custom .modal-dialog {
        max-width: 1050px;
    }
    .shipping-row{
        align-items: center;
    }
    .right-option-mbt{
        padding-right: 25px;
    }
</style>
<?php

use Illuminate\Support\Facades\URL;
$prodcutWeight = isset($prodcutDetails['weight']) ? $prodcutDetails['weight'] : '';
$productSizeLength = isset($prodcutDetails['productSizeLength']) ? $prodcutDetails['productSizeLength'] : '';
$productSizeWidth = isset($prodcutDetails['productSizeWidth']) ? $prodcutDetails['productSizeWidth'] : '';
$productSizeHeight = isset($prodcutDetails['productSizeHeight']) ? $prodcutDetails['productSizeHeight'] : '';
$storeId = isset($store_data->store_id) ? $store_data->store_id : "";
?>
<script>
    var action = "add";
</script>
<div id="save_shipping_custom" class="modal fade popup-style show shipping-popup-modal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content clearfix">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&#215;</button>
                <h4 class="modal-title"><b>Shipping Profile</b> </h4>
            </div>

            <form action="" method="" id="save_custom_shipping">
                <div class="modal-body">
                    <div class="uploadariation"><span></span></div>
                    <div class="shippingProfileResposne"></div>

                    <input type="hidden" id="shippingOptionMethodPopup" value="">
                    <div class="col-group col-flush row align-items-center" >
                        <div class="col-md-2 col-lg-1 profileNameCustomWidth">
                            <h6 class="profile-name ">Profile name </h6>
                        </div>
                        <div class="col-md-6  small-font profile-name">
                            <div class="form-group " style="margin-bottom: 0px;">
                                <input type="text" class="form-control" id="add_profile_name" name="profile_name" value="" placeholder="Enter Profile Name">
                            </div>
                        </div>
                    </div>

                    <hr class="mt-xs-4 mb-xs-4">


                    <div class="orgin-shipping">
                        <h6 class="profile-name mb-20 notthisBold">Shipping Origin <span class="whereyouareitem">(Where are your items being shipped from)</span></h6>

                        <div class="col-group col-flush row ">
                            <div class="col-md-6 bottomSpacing">
                                <h6>Street</h6>
                                <input type="text" class="form-control" id="shipping_address1" name="shipping_address1" onkeypress="" value="" placeholder="Ex. 124 Birch St.">

                            </div>
                            <div class="col-md-6 bottomSpacing">
                                <h6>City</h6>
                                <input type="text" class="form-control" id="shipping_city" name="shipping_city" onkeypress="" value="" placeholder="Ex. Chicago">
                            </div>


                            <div class="col-md-6 countryPanel">
                                <h6>Country</h6>
                                <div class="zerobottom">
                                    <div class="form-group" style="margin-bottom:0px;">
                                        <!-- <label for="Country">Country *</label> -->
                                        <select class="form-control" name="shipping_country_id" id="shipping_country_id">

                                            <!-- <option value="">What countries will you ship to?</option> -->
                                            @php
                                                $countries = getAllCountries();
                                            @endphp

                                            @foreach ($countries as $cn)
                                                <option value="{{ $cn['id'] }}">
                                                    {{ ucfirst(strtolower($cn['name'])) }}  
                                                </option>
                                            @endforeach

                                        </select>
                                        <span class="error">{{ $errors->first('store_country') }}</span>
                                    </div>
                                </div>
                            </div>


                            <div class="col-md-6">
                                <h6>State/Province</h6>
                                {{-- @dd(getStatesOfCountry($storeData['store_country'])) --}}
                                <div class="form-group" style="margin-bottom: 0px;">
                                    <!-- <label for="Province">Province *</label> -->
                                    <select class="form-control" name="shipping_state_id" id="shipping_state_id">
                                        <?php

                                        if (isset($store_data->store_country) && $store_data->store_country != '') {
                                            $states = getStatesOfCountry($store_data->store_country);
                                            foreach ($states as $st) {
                                                $selected = '';
                                                if (isset($store_data->store_state) && $store_data->store_state != '' && $store_data->store_state == $st['state_id']) {
                                                    $selected = 'selected';
                                                }
                                        ?>
                                                <option value="<?php echo $st['state_id'] ?>" <?php echo $selected; ?>><?php echo $st['state_name']; ?></option>
                                        <?php  }
                                        } ?>
                                        <option value="">Select your Province</option>
                                    </select>
                                    <span class="error">{{ $errors->first('store_state') }}</span>
                                </div>
                            </div>

                            <div class="col-md-6 ">
                                <h6>Zip/Postal Code</h6>
                                <div class="form-group zp zip-code-padding" style="margin-bottom:0px;">
                                    <label class="has-validation" style="margin-bottom: 0px;">
                                        <div class="input-prepend input-prepend-icon">
                                            <!-- <span class="input-prepend-item">
                                                <i class="fa fa-map-marker" aria-hidden="true"></i>
                                            </span> -->
                                            <input type="text" class="form-control required-field" id="add_origin_zip_code" name="origin_zipcode" maxlength="5" placeholder="Ex. 00012">
                                            <input type="hidden" id="store_id" name="store_id" value="{{$storeId}}">
                                        </div>
                                    </label>
                                </div>
                            </div>







                        </div>







                    </div>

                    <!-- <div class="col-group col-flush row ">
                        <div class="col-md-12 lftexp align-self-center">
                            <h6>Shipping Address 2*</h6>
                        </div>
                        <div class="col-md-12 rghtexp">
                            <input type="text" class="form-control" id="shipping_address2" name="shipping_address2" onkeypress="" value="" placeholder="">
                        </div>                    
                    </div> -->


                    <hr class="mt-xs-4 mb-xs-4">
                    <!-- static contetn made by asif  6-14-2023 -->
                    <div class="whereyouwillship">
                      
                    </div>

                    <div class="col-group col-flush row bottomSpacing">
                        <div class="col-md-12 ">
                            <h6>Shipping Method *</h6>
                            <!-- <p class="small mb-0">What countries will you ship to?</p>     -->
                        </div>
                        <div class="col-md-6 ">
                            <div class="shipping-address popup-shipping">
                                <div id="accordion-inner" role="tablist" aria-multiselectable="true" class="countries-checkbox-list">
                                    <div class="all-options bt-0 equal-width-mbt">
                                        <div class="card-header" role="tab" style="border-bottom:none!important;">
                                            <div class="form-group" style="margin-bottom: 25px;">
                                                <select class="form-control slctshipoption" name="select-shipoption" id="select-shipoption">
                                                    
                                                    <option value="other" id="other-shipping-po">Fixed Shipping</option>
                                                    <!-- <option value="free" id="editfree-shipping">Free Shipping</option> -->
                                                </select>
                                            </div>

                                        </div>
                                    </div>

                                </div>
                                <!-- accordion-inner -->
                            </div>
                        </div>
                    </div>

                    <div class="col-group col-flush row bottomSpacing">
                        <div class="col-md-12  align-self-center ">
                            <h6>Fixed Shipping *</h6>
                        </div>
                        <div class="col-md-12 ">
                            <input type="text" class="form-control" id="" name="flat_shipping_rate" onkeypress="" value="" placeholder="">
                        </div>
                    </div>
                    


                    

                    <!-- <div class="col-group col-flush row">
                        <div class="col-md-12 lftexp">
                            <h6>Free shipping  <span class="light-color-text">(Optional)</span></h6>
                            <p class="small mb-0">Want to offer buyers a free shipping option?</p>
                        </div>
                        <div class="col-md-12 rghtexp small-font">
                            <div class="form-group mb-0 listing-checkbox no-brder">


                                <label for="add_domestic_free_shipping" class="container-check">
                                    Free domestic shipping
                                    <input type="checkbox" id="add_domestic_free_shipping" name="domestic_free_shipping" value="domestic">
                                    <span class="checkmark"></span>
                                </label>
                           -->
                    <!--                                &nbsp; &nbsp; &nbsp;
                                <label for="international_free_shipping" class="container-check">
                                    Free international shipping
                                    <input type="checkbox" id="international_free_shipping" name="international_free_shipping" value="international"
                                           >
                                    <span class="checkmark"></span>
                                </label>-->
                    <!-- </div>            

                        </div>
                    </div>
                    -->
                    <div class="col-group col-flush row">
                       

                        <!--                        <div class="col-md-4 rghtexp small-lable ghtexp dollar-signadded"> 
                                                    <a href="javascript:;" class="blue-text hidden-after-link-select this-hide-all anchor-link display-inline-block mt-30">
                                                        <span>Set separate fees for domestic &amp; international</span>
                                                    </a>
                        
                                                    <div class="form-group hidden-first">
                                                        <label>International</label>
                                                        <input  type="text" class="form-control" id="international_handling_fee" name="international_handling_fee" onkeypress="Numvalidate(event, 'price', 'international_handling_fee')" value="" placeholder="International">
                                                        <span class="price-dollar">$</span>
                                                    </div>  
                                                    <a href="javascript:;" class="remove-added-list">
                                                        Remove
                                                    </a> 
                                                </div>    -->


                    </div>


                </div><!-- modal-body -->
            </form>
            <div class="modal-footer flex-footerStart">
                <button type="button" class="btn btn-default btn-primary btn-simple " data-dismiss="modal">Cancel</button>

                <?php 
                     $currentURL = request()->url();
                    $manged_shipping = URL::to('/managedshipping');
                    if($currentURL != $manged_shipping){ ?>

                        <button type="button" class="btn btn-primary" id="save_custom_shipp">Save profile</button>
                
                <?php }else{?>
                    <button type="button" class="btn btn-primary" id="save_custom_shipp_manageship">Save profile</button>

                    <?php } ?>
                
                {{-- <button type="button" class="btn btn-default btn-primary btn-simple " data-dismiss="modal">Cancel</button> --}}
            </div>
        </div><!-- modal-content -->
    </div><!-- modal-dialog -->
</div><!-- shipping-popup -->
<style>
    .showSweetAlert {
        z-index: 123456789 !important;
    }
</style>
<script>
    $(document).ready(function() {
        $("#shipping_country_id").on("change", function() {
            countryId = $(this).val();
            data = {};
            data['country_id'] = countryId;
            sendreq(data, 'buyer', 'state', 'getStates');
        });
    });

    function getStates(data) {
        $("#shipping_state_id").html(data);
    }
    $("#shipping_state_id").on("change", function() {
        var stateId = $("#shipping_state_id").val();
        if (stateId.length <= 0) {
            $(".hide-city").hide();
        } else {
            $(".hide-city").show();
        }
    });

    function getStates(data) {
        $("#shipping_state_id").html(data);
    }
</script>