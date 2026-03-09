<?php
$profileId = isset($result['shippingOptions']->id) ? $result['shippingOptions']->id : '';
$profileName = isset($result['shippingOptions']->profile_name) ? $result['shippingOptions']->profile_name : '';
$shippingCost = isset($result['shippingOptions']->shipping_cost) ? $result['shippingOptions']->shipping_cost : '';
$processingTimeOption = isset($result['shippingOptions']->processing_time_option) ? $result['shippingOptions']->processing_time_option : '';

$processingTimeStartRange = isset($result['shippingOptions']->processing_time_start_range) ? $result['shippingOptions']->processing_time_start_range : '';
$processingTimeEndRange = isset($result['shippingOptions']->processing_time_end_range) ? $result['shippingOptions']->processing_time_end_range : '';
$processTimeId = isset($result['shippingOptions']->processing_time_id) ? $result['shippingOptions']->processing_time_id : '';
if ($processingTimeOption == 'custom-range') {
    $processingTimeOption = 'custom-range-select-popup';
} else {
    $processingTimeStartRange = '';
    $processingTimeEndRange = '';
    //  $processTime = 'days';
}

if ($shippingCost == 'fixedCost') {
    $shippingOrigin = isset($result['shippingOptions']->shipping_origin) ? $result['shippingOptions']->shipping_origin : '';
    $shippingUpgrades = isset($result['shippingUpgrades']) ? $result['shippingUpgrades'] : null;
    $shippingFixCost = isset($result['shippingFixCost']) ? $result['shippingFixCost'] : null;
} else {
    $shippingServices = isset($result['shippingServices']) ? $result['shippingServices'] : null;
    $services = [];
    if (count($shippingServices) > 0 && is_array($shippingServices)) {
        foreach ($shippingServices as $key => $service) {
            $services[] = $service->service_value;
        }
    }
    $shippingCountries = isset($result['shippingCountries']) ? $result['shippingCountries'] : null;
    $originZipcode = isset($result['shippingOptions']->origin_zipcode) ? $result['shippingOptions']->origin_zipcode : '';
    $domesticFreeShippings = isset($result['shippingOptions']->is_domesticfree) ? $result['shippingOptions']->is_domesticfree : '';
    $internationalFreeShipping = isset($result['shippingOptions']->is_internationalfree) ? $result['shippingOptions']->is_internationalfree : '';
    $domesticHandlingFee = isset($result['shippingOptions']->domestic_handling_fee) ? $result['shippingOptions']->domestic_handling_fee : 0;
    $internationalHandlingFee = isset($result['shippingOptions']->international_handling_fee) ? $result['shippingOptions']->international_handling_fee : '';
    $shipService = isset($result['shippingOptions']->shipping_service) ? $result['shippingOptions']->shipping_service : '';
    $flatShippingRate = isset($result['shippingOptions']->flat_shipping_rate) ? $result['shippingOptions']->flat_shipping_rate : '';
    $shipcountryId = isset($result['shippingOptions']->shipping_country_id) ? $result['shippingOptions']->shipping_country_id : '';
    $shippingStateId = isset($result['shippingOptions']->shipping_state_id) ? $result['shippingOptions']->shipping_state_id : '';
    $shipping_city = isset($result['shippingOptions']->shipping_city) ? $result['shippingOptions']->shipping_city : '';
    $shipping_address1 = isset($result['shippingOptions']->shipping_address1) ? $result['shippingOptions']->shipping_address1 : '';
    $shipping_address2 = isset($result['shippingOptions']->shipping_address2) ? $result['shippingOptions']->shipping_address2 : '';
}
?>
<script>
    var action = "edit";
</script>
<div class="alert-box-edit">
    <p>
        Please note , any changes that you make to this profile will effect all products to which this profile is
        attached to.
    </p>
</div>
<form id="shipping-form" method="post" action="">




    <input type="hidden" id="shippingOptionMethodPopup" value="<?php echo $result['shippingOptions']->shipping_cost; ?>">
    <div class="col-group col-flush row align-items-center" style="align-items: baseline!important;">
        <div class="col-md-2 col-lg-1 profileNameCustomWidth ">
            <h6 class="profile-name ">Profile name</h6>
        </div>
        <div class="col-md-6  small-font profile-name">
            <div class="form-group ">
                <input type="text" class="form-control" id="edit_profile_name" name="profile_name"
                    value="<?php
                    if ($action == 'duplicate') {
                        $rand = rand(100, 1000);
                        echo $profileName . '-' . $rand;
                    } else {
                        echo $profileName;
                    }
                    ?>" placeholder="">
            </div>
            <?php if ($action == 'edit') { ?>
            <input type="hidden" id="shipping_option_id" name="shipping_option_id" value="<?php echo $result['shippingOptions']->id; ?>">
            <?php } else { ?>
            <input type="hidden" id="shipping_option_id" name="shipping_option_id" value="">
            <?php } ?>
        </div>
    </div>


    <!-- <div class="orgin-shipping">
    </div> -->


    <hr class="mt-xs-4 mb-xs-4">


    <!-- @if ($shippingCost == 'fixedCost')
-->

    <!-- <div class="col-md-12  mrgn-top0">
                <h6>Shipping origin *
                </h6>
                <p >The country you're shipping from
                </p>
            </div>
            <div class="col-md-12  mrgn-top0">
                <div class="form-group equal-width-mbt mb-0">
                    <select class="form-control" name="shippingOriginCountryPopup" id="shippingOriginCountryPopup">

                        <?php
                        // $selectedCountry = $shippingOrigin;
                        // $selectedAttribute = '';
                        // $allCountries = getAllCountries();
                        // foreach ($allCountries as $key => $country) {
                        //     if ($country['id'] == $selectedCountry) {
                        //         $selectedAttribute = 'selected="seleced"';
                        //     } else {
                        //         $selectedAttribute = '';
                        //     }
                        //     echo '<option value="' . $country['id'] . '" ' . $selectedAttribute . '>' . $country['name'] . '</option>';
                        // }
                        ?>

                    </select>
                </div>
            </div>
         -->
    <!--
@else
-->



    <!--
@endif -->

    <!-- <div class="col-md-12 lftexp mrgn-top0">
                <h6 class="mb-0">Processing time *
                </h6>
                <p>Once purchased, how long does it take you to ship
                </p>
            </div> -->
    <!-- <div class="col-md-12 rghtexp mrgn-top0">
                <div class="form-group equal-width-mbt mb-0">
                    <select class="form-control" name="process_time" id="processing-time-perioud-popup">
                        <?php
                        // foreach ($processTime as $value) {
                        //  print_rr($value);
                        //    if ($value->processing_time_id != 11) {
                        //      if ($processTimeId == $value->processing_time_id) {
                        //           $selectedItem = 'selected="selected"';
                        //         } else {
                        //            $selectedItem = '';
                        //       }
                        ?>
                                <option value="<?php // echo $value->processing_time_id;
                                ?>" <?php // echo $selectedItem;
                                ?>><?php // echo $value->process_start_range;
                                ?> - <?php // echo $value->process_end_range;
                                ?> Business <?php // echo ucwords($value->process_unit);
                                ?></option>

                                <?php
                                //   }
                                //   }
                                ?>
                        <option value="custom-range" id="custom-range-select"
                        <?php
                        //  if ($processTimeId == 'custom-range') {
                        //       echo 'selected="selected"';
                        //     }
                        ?>
                                >Custom Range </option>
                        <option value="11"

                                <?php
                                // if ($processTimeId == '11') {
                                //     echo 'selected="selected"';
                                // }
                                ?>
                                >Unknown</option>
                    </select>
                </div> -->


    <!-- <p class="mrgn-top35 hide-when-custom-range-select">Buyers are more likely to purchase items that ship quickly </p>-->

    <!-- <div class="custom-range custom-range-select-popup show-on-custom-range-select -->
    <?php
    // if ($processingTimeOption == 'custom-range') {
    //     echo 'show-div';
    // }
    ?>
    <!-- "> -->
    <!-- <div class="option-processing-cnt mb-0">
                        <div class="select-range">
                            <div class="display-inline-block custom-width-select">
                                <div class="form-group">
                                    <select class="form-control" name="start_range" id="start_range">
                                        <option value="" >From...</option>
                                        <?php
                                        // $selectedItem = '';
                                        // for ($i = 1; $i <= 9; $i++) {
                                        //     if ($processingTimeStartRange == $i) {
                                        //         $selectedItem = 'selected="selected"';
                                        //     } else {
                                        //         $selectedItem = '';
                                        //     }
                                        //     echo '<option value="' . $i . '" ' . $selectedItem . '>' . $i . '</option>';
                                        // }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="display-inline-block">
                                <span class="seprator-slash"> - </span>
                            </div>
                            <div class="display-inline-block custom-width-select">
                                <div class="form-group">
                                    <select class="form-control" name="end_range" id="end_range">
                                        <option value="">To...</option>
                                        <?php
                                        // $selectedItem = '';
                                        // for ($i = 1; $i <= 9; $i++) {
                                        //     if ($processingTimeEndRange == $i) {
                                        //         $selectedItem = 'selected="selected"';
                                        //     } else {
                                        //         $selectedItem = '';
                                        //     }
                                        //     echo '<option value="' . $i . '" ' . $selectedItem . '>' . $i . '</option>';
                                        // }
                                        ?>
                                    </select>
                                </div>
                            </div> -->
    <!-- </div>   select-range -->
    <!--  </div> option-processing-cnt -->
    <!-- <div class="display-inline-block">
                        <span class="checkbox-list-small">
                            <input type="radio" id="business-days-popup" name="process_unit"  value="days"

                                   <?php
                                   //    if ($processTime == 'days') {
                                   //        echo 'checked=""';
                                   //    }
                                   ?>
                                   >
                            <label for="business-days-popup">Business Days</label>
                        </span>
                        <span class="checkbox-list-small">
                            <input type="radio" id="business-week-popup" name="process_unit"  value="weeks"

                                   <?php
                                   //    if ($processTime == 'weeks') {
                                   //        echo 'checked=""';
                                   //    }
                                   ?>>
                            <label for="business-week-popup">Weeks</label>
                        </span>
                    </div>     -->
    <!-- </div> show-on-custom-range-select -->







    <!-- </div> -->
    <!-- col-group col-flush row -->



    <!-- @if ($shippingCost == 'fixedCost')
-->

    <!-- <div class="fixed-shipping-cost custom-shipping-container mt-10  mb-10">
        <hr class="mt-xs-4 mb-xs-4 showon-customshipping-select" >
        <div class="col-group col-flush row">
            <div class="col-md-12  mt-0 mb-15">
                <h6>Fixed shipping costs
                </h6>
            </div>
            <div class="col-md-12  mt-0 mb-15">
                <p>The price each buyer will pay for shipping
                </p>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-sm-4">
                        <p class="strong text-truncate text-smaller text-gray">Destination
                        </p>
                    </div>
                    <div class="col-sm-4">
                        <p class="strong text-truncate text-smaller text-gray">One item
                        </p>
                    </div>
                    <div class="col-sm-4">
                        <p class="strong text-truncate text-smaller text-gray">Each additional item
                            <span class="normal text-gray-lighter ml-xs-1">
                                Optional
                            </span>
                        </p>
                    </div>
                </div>
            </div> -->
    <!-- <div class="card-body fixedShippingCostMainDivPopup"> -->
    <?php
    // if (is_array($shippingFixCost) && count($shippingFixCost) > 0) {
    //     //// print_rr($shippingFixCost);
    //     foreach ($shippingFixCost as $key => $value) {
    ?>
    <!-- <div class="row entry-body"> -->
    <!-- <div class="col-sm-4">
                                <input type="hidden" class="popupCountriesMange" value="<?php // echo $value->destination_id;
                                ?>"  />
                                <p class="align-with-btn already-showed"><?php
                                // $countryName = getCountryNameById($value->destination_id);
                                // if (is_array($countryName) && count($countryName) > 0) {
                                //     if ($countryName[0]) {
                                //         echo $countryName[0]->name;
                                //     }
                                // }
                                ?>
                                </p>
                            </div>
                            <div class="col-sm-4">
                                <div class="primary-cost common-mbt-stle">
                                    <label class="input-prepend has-validation">
                                        <div class="input-prepend-item"> &#36;
                                        </div>
                                        <input type="text" id="shippingPrice<?php // echo $value->fix_cost_id;
                                        ?>" onkeypress="Numvalidate(event, 'price', 'shippingPrice<?php echo $value->fix_cost_id; ?>')" class="input form-control " value="<?php echo $value->one_item; ?>" placeholder="0.00">
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="secondary-cost common-mbt-stle">
                                    <label class="input-prepend has-validation">
                                        <div class="input-prepend-item"> &#36;
                                        </div>
                                        <input type="text" class="input form-control" id="shippingPriceEachItem<?php // echo $value->fix_cost_id;
                                        ?>" onkeypress="Numvalidate(event, 'price', 'shippingPriceEachItem<?php echo $value->fix_cost_id; ?>')" value="<?php echo $value->each_item; ?>" placeholder="">
                                    </label>
                                </div>
                            </div> -->
    <?php
    //  if ($key != 0) {
    ?>
    <!-- <div class="col-sm-1 text-center">
                                    <a href="javascript:;" class="align-with-btn text-gray-lighter cross-icon-cont remove-container">
                                        <span class="ss-etsy ss-delete icon-smaller icon-t-2">
                                            <i class="fa fa-times" aria-hidden="true"></i></span>
                                    </a>
                                </div> -->
    <?php
    // }
    ?>


    <!-- </div> -->
    <!-- row entry-body -->
    <?php
    //     }
    // }
    ?>

    <!-- <div class="parentFixedShipping">

                </div> -->


    <!-- <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <a href="javascript:;" class="icon-t-1" id="add-another-location-popup">
                                <span class="ss-etsy ss-plus icon-smaller mr-xs-1"><i class="fa fa-plus" aria-hidden="true"></i></span>
                                Add another location
                            </a>
                        </div>
                        <div class="col-md-6 pl-xs-0 pr-xs-0">
                            <div class="text-right">
                                <a href="javascript:;" target="_blank" class="link-gray-darker">
                                    Estimate shipping costs
                                </a>
                            </div>
                        </div>
                    </div>
                </div> -->

    <!-- </div>  -->
    <!-- </div> -->
    <!-- card -->
    <!-- </div> -->




    <!-- <hr class="mt-xs-4 mb-xs-4" > -->



    <!-- <div class="fixed-shipping-cost custom-shipping-container shipping-upgrade-optional shippingUpgradeOptionalPopup"> -->
    <!-- <div class="col-group col-flush row">

            <div class="col-md-12  mt-0 mb-15">
                <h6>
                    Shipping upgrades
                    <span class="text-gray-lighter text-smaller">
                        Optional
                    </span>

                </h6>
            </div>
            <div class="col-md-12  mt-0 mb-15">
                <p>Add faster options for your buyers (added to shipping cost)
                </p>
            </div>
        </div> -->
    <!-- <div class="card"> -->
    <!-- <div class="card-header hide-onload">
                <div class="row">
                    <div class="col-sm-3">
                        <p class="strong text-truncate text-smaller text-gray">Upgrade name
                        </p>
                    </div>
                    <div class="col-sm-3">
                        <p class="strong text-truncate text-smaller text-gray">Destination
                        </p>
                    </div>
                    <div class="col-sm-3">
                        <p class="strong text-truncate text-smaller text-gray">One item
                        </p>
                    </div>
                    <div class="col-sm-3">
                        <p class="strong text-truncate text-smaller text-gray">Each additional item
                        </p>
                    </div>

                </div>
            </div> -->
    <!-- <div class="card-body"> -->

    <?php
    // if (is_array($shippingUpgrades) && count($shippingUpgrades) > 0) {
    //     /// print_rr($shippingUpgrades);
    //     foreach ($shippingUpgrades as $key => $value) {
    ?>


    <!-- <div class="row entry-body upgrade-shipping hide-onload">
                            <div class="col-sm-3">
                                <label class="input-prepend has-validation">
                                    <input type="text" class="input form-control" value="<?php // echo $value->upgrade_name
                                    ?>" placeholder="Upgrade name">
                                </label>

                            </div>
                            <div class="col-sm-3">
                                <div class="upgrade-type common-mbt-stle">
                                    <div class="form-group">
                                        <select class="form-control" name="">
                                            <option value="domestic"
                                            <?php
                                            // if ($value->level == 'domestic') {
                                            //     echo 'selected="selected"';
                                            // }
                                            ?>>Domestic
                                            </option>
                                            <option value="international"

                                                    <?php
                                                    // if ($value->level == 'international') {
                                                    //     echo 'selected="selected"';
                                                    // }
                                                    ?>
                                                    >International
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="secondary-cost common-mbt-stle">
                                    <label class="input-prepend has-validation">
                                        <div class="input-prepend-item"> &#36;
                                        </div>
                                        <input type="text" class="input form-control" id="upgradeShipping_<?php //echo $value->shipping_upgrade_id;
                                        ?>" onkeypress="Numvalidate(event, 'price', 'upgradeShipping_<?php echo $value->shipping_upgrade_id; ?>')" value="<?php echo $value->one_item; ?>" placeholder="0.00">
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="secondary-cost common-mbt-stle">
                                    <label class="input-prepend has-validation">
                                        <div class="input-prepend-item"> &#36;
                                        </div>
                                        <input type="text" class="input form-control" id="upgradeShippingEachItem_<?php //echo $value->shipping_upgrade_id;
                                        ?>" onkeypress="Numvalidate(event, 'price', 'upgradeShippingEachItem_<?php echo $value->shipping_upgrade_id; ?>')" value="<?php echo $value->one_item; ?>" value="<?php echo $value->each_item; ?>" placeholder="">
                                    </label>
                                </div>
                            </div>
                        </div>

                        <?php
                        //     }
                        // } else {
                        ?>
                    <div class="row entry-body upgrade-shipping hide-onload">
                        <div class="col-sm-3">
                            <label class="input-prepend has-validation">
                                <input type="text" class="input form-control" value="" placeholder="Upgrade name">
                            </label>

                        </div>
                        <div class="col-sm-3">
                            <div class="upgrade-type common-mbt-stle">
                                <div class="form-group">
                                    <select class="form-control" name="">
                                        <option value="domestic">Domestic </option>
                                        <option value="international">International</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="secondary-cost common-mbt-stle">
                                <label class="input-prepend has-validation">
                                    <div class="input-prepend-item"> &#36;
                                    </div>
                                    <input type="text" class="input form-control" value="" id="shippingPriceItemPopup" onkeypress="Numvalidate(event, 'price', 'shippingPriceItemPopup')"  placeholder="0.00">
                                </label>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="secondary-cost common-mbt-stle">
                                <label class="input-prepend has-validation">
                                    <div class="input-prepend-item"> &#36;
                                    </div>
                                    <input type="text" class="input form-control" id="shippingPriceEachItemPopup" onkeypress="Numvalidate(event, 'price', 'shippingPriceEachItemPopup')" value="" placeholder="">
                                </label>
                            </div>
                        </div>
                    </div>

                    <?php
                    // }
                    ?>

                <div class="add-shipping-upgrade-container"> -->

    <!-- </div> -->
    <!-- add-shipping-upgrade-container -->
    <!--
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <a href="javascript:;" class="icon-t-1" id="add-upgrade-shipping-pop">
                                <span class="ss-etsy ss-plus icon-smaller mr-xs-1"><i class="fa fa-plus" aria-hidden="true"></i></span>
                                Add a shipping upgrade
                            </a>
                        </div>

                    </div>
                </div> -->
    <!--
            </div>
        </div> -->
    <!-- card -->
    <!-- </div> -->

    <!--
@else
-->

    <div class="orgin-shipping">

        <div class="col-group col-flush row bottomSpacing">

            <div class="col-md-6 bottomSpacing">
                <h6>Sreet *</h6>
                <input type="text" class="form-control" id="shipping_address1" name="shipping_address1"
                    onkeypress="" value="<?php echo $shipping_address1; ?>" placeholder="">
            </div>

            <div class="col-md-6 bottomSpacing">
                <h6>City *</h6>
                <input type="text" class="form-control" id="shipping_city" name="shipping_city" onkeypress=""
                    value="<?php echo $shipping_city; ?>" placeholder="Enter shipping City *">
            </div>



            <div class="col-md-6  bottomSpacing">
                <h6>Country *</h6>

                <div class="form-group">
                    <!-- <label for="Country">Country *</label> -->
                    <select class="form-control" name="shipping_country_id" id="shipping_country_id">
                        <?php
                        $countries = getAllCountries(); 

                        $selectedCountryId = isset($shipcountryId) ? $shipcountryId : ''; 

                        if (!empty($countries)) {
                            foreach ($countries as $cn) {
                                // Check if the country is selected
                                $isSelected = ($selectedCountryId == $cn['id']) ? 'selected' : '';
                                echo '<option value="' . $cn['id'] . '" ' . $isSelected . '>' . $cn['name'] . '</option>';
                            }
                        }
                        ?>
                    </select>

                    <span class="error">{{ $errors->first('store_country') }}</span>
                </div>
            </div>
            <div class="col-md-6 bottomSpacing ">
                <div class="form-group">

                    <h6>Province *</h6>
                    <select class="form-control" name="shipping_state_id" id="shipping_state_id">
                        <?php

                        if ($shipcountryId && $shipcountryId != '') {
                            $states = getStatesOfCountry($shipcountryId);
                            foreach ($states as $st) {
                                $selected = '';
                                if ($shippingStateId && $shippingStateId != '' && $shippingStateId == $st['state_id']) {
                                    $selected = 'selected';
                                }
                        ?>
                        <option value="<?php echo $st['state_id']; ?>" <?php echo $selected; ?>><?php echo $st['state_name']; ?></option>
                        <?php  }
                        } ?>
                        <option value="">Select your Province</option>
                    </select>
                    <span class="error">{{ $errors->first('store_state') }}</span>
                </div>
            </div>

            <div class="col-md-6  mrgn-top0">
                <h6>Postal Code *</h6>
                <p style="display:none;">Enter the zip code of the origin from where this product will be shipped.
                </p>
                <div class="form-group zp zip-code-padding">
                    <label class="has-validation">
                        <div class="input-prepend input-prepend-icon">
                            <!-- <span class="input-prepend-item">
                                <i class="fa fa-map-marker" aria-hidden="true"></i>
                            </span> -->
                            <input type="text" class="form-control required-field" id="edit_origin_zip_code"
                                name="origin_zipcode" id="origin_zipcode" maxlength="5" placeholder=""
                                value="<?php echo $originZipcode; ?>"
                                placeholder="Enter the zip code of the origin from where this product will be shipped.">
                        </div>
                    </label>
                </div>
            </div>
        </div>
    </div>
    <hr class="mt-xs-4 mb-xs-4" style="margin-top:29px!important;">

    <div class="orgin-shipping">
        <div class="col-group col-flush row bottomSpacing">
            <div class="col-md-12 ">
                <h6>Shipping Method *</h6>
                <!-- <p class="small mb-0">What countries will you ship to?</p>     -->
            </div>
            <div class="col-md-6 ">
                <div class="shipping-address popup-shipping equal-width-mbt">
                    <div id="accordion-inner" role="tablist" aria-multiselectable="true"
                        class="countries-checkbox-list">
                        <div class=" all-options bt-0">
                            <div class="card-header" role="tab" id="" style="border-bottom: none!important;">
                                <!--                            <h5 class="mb-0">
                                                            <label class="container-check">
                                                                <input type="checkbox" class="checkbox" id="all-countries-checkbox-popup">
                                                                <span class="checkmark"></span>
                                                            </label>
                                                            <a data-toggle="collapse" data-parent="#accordion-inner" href="#all-country" aria-expanded="true" aria-controls="all-country">
                                                                <span class="text-clr">(United States only)</span>
                                                            </a>
                                                        </h5>-->
                                <div class="form-group">

                                    <select class="form-control slctshipoption" name="select-shipoption"
                                        id="editselect-shipoption">
                                        <option <?php
                                        if (!empty($flatShippingRate)) {
                                            echo 'selected';
                                        }
                                        ?> value="other" id="editother-shipping-po">Fixed
                                            Shipping</option>


                                    </select>
                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--                    <div class="body-text">
                                                <p>We make shipping internationally a breeze. Costs are automatically calculated based on the buyer&#39;s country&#44; and we&#39;ve got you covered on the customs front too.</p>
                                                <div class="mt-xs-1">
                                                    <a href="javascript:;" class="link-gray-darker" target="_blank">
                                                        Read more about international shipping
                                                    </a>
                                                </div>
                                            </div>-->

    <!-- card all-options -->
    <?php
    //                $continentArray = array(
    //                    'Asia Pacific',
    //                    'Sub-Saharan Africa',
    //                    'North Africa and the Middle East',
    //                    'Europe',
    //                    'Latin America and the Caribbean',
    //                    'North America',
    //                    'Australia, New Zealand and Oceania',
    //                );
    //                foreach ($continentArray as $key => $continentvalue) {
    //                    $continent = strtolower(trim($continentvalue));
    //                    $continent = preg_replace('/[^a-z0-9-]/', '-', $continent);
    //                    $continent = preg_replace('/-+/', "-", $continent);
    //                    $continentslug = rtrim($continent, '-');
    ?>
    <!--                    <div class="card">
                                            <div class="card-header" role="tab" id="">
                                                <h5 class="mb-0">
                    
                                                    <label class="container-check">
                                                        <input type="checkbox" class="checkbox countryCheckBox" id="<?php //echo $continentslug;
                                                        ?>-check">
                                                        <span class="checkmark"></span>
                                                    </label>
                                                    <a data-toggle="collapse" data-parent="#accordion-inner" href="#<?php //echo $continentslug;
                                                    ?>_2" aria-expanded="true" aria-controls="<?php //echo $continentslug;
                                                    ?>">
                    <?php //echo $continentvalue;
                    ?>
                                                    </a>
                                                </h5>
                                            </div>
                                            <div id="<?php //echo $continentslug;
                                            ?>_2" class="collapse" role="tabpanel" aria-labelledby="">
                                                <div class="card-block row">
                    
                    <?php
                    //$key++;
                    //  foreach (getAllCountries($key) as $countries) {
                    ?>
                                                        <div class="col-xs-12 col-md-6">
                                                            <div class="checkbox-list">
                                                                <label class="container-check"><?php //echo $countries['name'];
                                                                ?>
                    
                                                                    <input type="checkbox" name="countries-popup[]" value="<?php //echo $countries['id'];
                                                                    ?>"
                    <?php
                    //                                                if (is_array($shippingCountries) && in_array($countries['id'], $shippingCountries)) {
                    //                                                    echo 'checked="checked"';
                    //                                                }
                    ?>
                                                                           >
                                                                    <span class="checkmark"></span>
                                                                </label>
                                                            </div> checkbox-list
                                                        </div>
                    
                    <?php
                    // }
                    ?>
                                                </div>
                                            </div>
                                        </div>-->
    <?php
    //   }
    ?>

    <!-- accordion-inner -->

    <?php
    $style = 'style=display:flex;';
    if (!empty($flatShippingRate || $domesticFreeShippings)) {
        $style = 'style=display:none;';
    }
    ?>

   
    <?php
    $display = 'style=display:none;';
    if (!empty($flatShippingRate)) {
        $display = 'style=display:flex;';
    }
    ?>
    <div class="col-group col-flush row bottomSpacing">
        <div class="col-md-9">
            <h6>Fixed Shipping </h6>
            <input type="text" class="form-control" id="edit_flat_ship" name="flat_shipping_rate"
                onkeypress="Numvalidate(event, 'edit_flat_ship', 'edit_flat_ship')" value="<?php echo $flatShippingRate; ?>"
                placeholder="">
        </div>
    </div>


    <!-- <div class="col-group col-flush row">
        <div class="col-md-12 lftexp">
            <h6>Free shipping  <span class="light-color-text">(Optional)</span></h6>
            <p class="small">Want to offer buyers a free shipping option?</p>
        </div>
        <div class="col-md-12 rghtexp small-font">
            <div class="form-group mb-0 listing-checkbox no-brder">
                <label for="domestic_free_shipping" class="container-check">Free domestic shipping
                <input type="checkbox" id="domestic_free_shipping" name="domestic_free_shipping" value="domestic"
                <?php
                // if ($domesticFreeShippings) {
                //     echo 'checked="checked"';
                // }
                ?>

                       >   -->
    <!-- <span class="checkmark"></span>
                </label> -->
    <!--                &nbsp; &nbsp; &nbsp;
                <label for="international_free_shipping" class="container-check">Free international shipping
                                <input type="checkbox" id="international_free_shipping" name="international_free_shipping" value="international"
                                <?php
                                //                if ($internationalFreeShipping) {
                                //                    echo 'checked="checked"';
                                //                }
                                ?>

                                    >
                                <span class="checkmark"></span>
                </label>-->
    <!-- </div>

        </div>
    </div> -->


    <!--
@endif -->


</form>
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
