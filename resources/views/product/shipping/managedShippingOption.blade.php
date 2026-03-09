<?php
$shippingOptionId = isset($prodcutDetails['shipping_option_id']) ? $prodcutDetails['shipping_option_id'] : '';
if (isset($prodcutDetails['shipping_option_id']) && $prodcutDetails['shipping_option_id'] != '') {
    $shippingOptionId = $prodcutDetails['shipping_option_id'];
} else if (!empty($shippingId)) {
    $shippingOptionId = $shippingId;
}
if (isset($prodcutDetails['user_id'])) {
    $userId = $prodcutDetails['user_id'];
} else {
    $userId = getCurrentUserId();
}
$userShipping = getShippingOptionByUserId($userId);
if (is_array($userShipping) && count($userShipping) > 0) {
    foreach ($userShipping as $key => $value) {
        if($value->delete_status == 1){

        

        $optionID = rand(122, 99999);
        $checkedShippingOption = '';
        if ($key == 0) {
            if ($shippingOptionId == '') {
                $checkedShippingOption = 'checked=""';
            }
        }
        if ($value->id == $shippingOptionId) {
            $checkedShippingOption = 'checked=""';
        }
        //        if($value->shipping_country_id==0){
        //            $shipType="Calculated costs";
        //        }else{
        //            $shipType="Fixed costs";
        //        }
        $shipping = '';
        $flat_rate = $value->flat_shipping_rate;
        $free = $value->is_domesticfree;
       
        $service = '';
        if ($free == 1) {
          
            $service = "free";
        } elseif ($free == 0 && $flat_rate < 1) {
            $service = 'united-state';
            
        } elseif ($free == 0 && $flat_rate > 0) {
            $service = "other";
           
        }
         $currentURL = request()->url();
         $manged_shipping = URL::to('/managedshipping');

        ?>

        <div class="panel-repeater-radio shipping-closets removingShipping">
            <div class="panel-body-row-radio p-xs-2 row shipping-row">
                <div class="col-sm-8 p-xs-0">
                    <div class="radio-list">
                        <input type="hidden" id="shipping-profile-selection" class="shipping-profile-selection" name="shipping-profile-selection" value="{{$service}}">
                       
                        <label for="previousShippingOptions-<?php echo $optionID; ?>">
                            <span class="mr-xs-1"><?php echo $value->profile_name; ?>
                            </span>
                            <?php
                            if (!empty($value->flat_shipping_rate) && $value->flat_shipping_rate > 0) { ?>
                            <span class="badge badge-gray-darker mr-xs-1 icon-b-1">
                                 Fixed cost
                            </span>
                            <?php } ?>
                            <?php
                            if ($value->is_domesticfree) { ?>
                            <span class="badge badge-gray-darker mr-xs-1 icon-b-1">
                                 Free cost
                            </span>
                            <?php } ?>
                            <ul class="text-smaller text-gray-lighter mt-xs-1 p-xs-0">
                                <li class="comma-separated-list-item">
                                    <?php
                                    $processTime = getTimeProcess($value->processing_time_id);
                                    // print_rr($processTime);
                                    echo!empty($processTime->process_start_range) ? $processTime->process_start_range . "-" : "";
                                    echo!empty($processTime->process_end_range) ? $processTime->process_end_range . " Business " : "";
                                    echo!empty($processTime->process_unit) ? ucwords($processTime->process_unit) : "";
                                    ?>

                                    &nbsp; processing time</li>
                                <!--                                                                                            <li class="comma-separated-list-item">free shipping (international)
                                                                                                                            </li>
                                                                                                                            <li class="comma-separated-list-item">$10.00 handling fee
                                                                                                                            </li>-->
                                <li class="comma-separated-list-item">from <?php
                                    if ($value->shipping_cost == 'fixedCost') {
                                        $countryName = getCountryNameById($value->shipping_origin);
                                        if (is_array($countryName) && count($countryName) > 0) {
                                            if ($countryName[0]) {
                                                echo ', &nbsp;' . $countryName[0]->country_name;
                                            }
                                        }
                                    } else {
                                        echo '&nbsp;' . $value->origin_zipcode;
                                    }
                                    ?>
                                </li>
                            </ul>                                                
                        </label>
                    </div>
                </div>
                <div class="col-sm-4 text-right pr-xs-1 right-option-mbt">
                    <?php
                    if($value->is_domesticfree == 0 || $value->is_domesticfree == '0'){
                    ?>
                        <a href="javascript:;" data-ui="edit" class="text-smaller loadShippingOption" shippingOptionId="<?php echo base64_encode($value->id); ?>" >Edit
                        </a>
                        <a href="javascript:;" data-ui="duplicate" shippingOptionId="<?php echo base64_encode($value->id); ?>" class="loadShippingOption ml-xs-1 text-smaller">Duplicate
                        </a>
                        <a href="javascript:;" id="delete_shipping_profile" data-shipping-id="<?php echo $value->id; ?>" class=" ml-xs-1 text-smaller">Delete
                        </a>
                    <?php 
                    }
                    ?>
                </div>
            </div>
            <!-- panel-body-row-radio -->
        </div>

        <?php
        }
    }
}
?>
<div class="panel-repeater-radio custom-shipping-container br-top-none" style="display:none;">
    <div class="panel-body-row-radio p-xs-2 row ">
        <div class="col-sm-12 p-xs-0">
            <div class="radio-list" data-toggle="modal" data-target="#save_shipping_custom">
                <input type="radio" id="custom-shipping" name="previousShippingOptions" class="parent-radio-buttons" value="custom-shipping">
                <label for="custom-shipping">
                    <span class="mr-xs-1">Enter custom shipping options
                    </span>                                                
                </label>
            </div>
        </div>
    </div>
    <!-- panel-body-row-radio -->
</div>