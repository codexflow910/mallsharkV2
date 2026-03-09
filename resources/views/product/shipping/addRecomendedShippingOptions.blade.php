<div class="col-md-4 lftexp clculateRecommeded clculateRecommeded-div no-space-top">
    <h6>Where I'll ship *</h6>
    <p class="small">What countries will you ship to?</p>    
</div>


<?php
$shippingOptionId = isset($prodcutDetails['shipping_optionID']) ? $prodcutDetails['shipping_optionID'] : '';
$result = getShippingOptionMethodById($shippingOptionId);
$publish = isset($result['shippingOptions']->publish) ? $result['shippingOptions']->publish : '';
if ($publish) {

    $shippingServices = NULL;
    $services = array();

    $shippingCountries = NULL;
    $originZipcode = '';
    $domesticFreeShippings = '';
    $internationalFreeShipping = '';
    $domesticHandlingFee = '';
    $internationalHandlingFee = '';
} else {

    $shippingServices = isset($result['shippingServices']) ? $result['shippingServices'] : NULL;
    $services = array();
    if (count($shippingServices) > 0 && is_array($shippingServices)) {
        foreach ($shippingServices as $key => $service) {
            $services[] = $service->service_value;
        }
    }
    $shippingCountries = isset($result['shippingCountries']) ? $result['shippingCountries'] : NULL;
    $domesticFreeShippings = isset($result['shippingOptions']->domestic_free_shippings) ? $result['shippingOptions']->domestic_free_shippings : '';
    $internationalFreeShipping = isset($result['shippingOptions']->international_free_shipping) ? $result['shippingOptions']->international_free_shipping : '';
    $domesticHandlingFee = isset($result['shippingOptions']->domestic_handling_fee) ? $result['shippingOptions']->domestic_handling_fee : '';
    $internationalHandlingFee = isset($result['shippingOptions']->international_handling_fee) ? $result['shippingOptions']->international_handling_fee : '';
}
?>


<div class="col-md-8 rghtexp clculateRecommeded clculateRecommeded-div countries-section-box mt-0 " > 
    <div class="shipping-address countriesListCheckBox ">
        <div id="accordion2" role="tablist" aria-multiselectable="true" class="cstm-hidden-chzn">
            <div class="card all-options bt-0">
                <div class="card-header" role="tab" id="">
                    <h5 class="mb-0">
                        <label class="container-check">
                            <input type="checkbox" class="checkbox" id="all-countries-checkbox" checked>
                            <span class="checkmark"></span>
                        </label>        
                        <a data-toggle="collapse" data-parent="#accordion2" href="#all-country" aria-expanded="true" aria-controls="all-country">
                           United States <span class="text-clr">(United States only)</span>
                        </a>

                    </h5>
                </div>
<!--                <div class="body-text hidden">
                    <p>We make shipping internationally a breeze. Costs are automatically calculated based on the buyer&#39;s country&#44; and we&#39;ve got you covered on the customs front too.</p>
                    <div class="mt-xs-1">
                        <a href="javascript:;" class="link-gray-darker" target="_blank">
                            Read more about international shipping
                        </a>
                    </div>            
                </div>-->
            </div><!-- card all-options -->
            <?php
            $continentArray = array(
                'Asia Pacific',
                'Sub-Saharan Africa',
                'North Africa and the Middle East',
                'Europe',
                'Latin America and the Caribbean',
                'North America',
                'Australia, New Zealand and Oceania',
            );
            foreach ($continentArray as $key => $continentvalue) {
                $continent = strtolower(trim($continentvalue));
                $continent = preg_replace('/[^a-z0-9-]/', '-', $continent);
                $continent = preg_replace('/-+/', "-", $continent);
                $continentslug = rtrim($continent, '-');
                ?>
<!--                <div class="card hidden">
                    <div class="card-header" role="tab" id="">
                        <h5 class="mb-0">

                            <label class="container-check">
                                <input type="checkbox" class="checkbox countryCheckBox" id="<?php echo $continentslug; ?>-check">
                                <span class="checkmark"></span>
                            </label>        
                            <a data-toggle="collapse" data-parent="#accordion2" href="#<?php echo $continentslug; ?>" aria-expanded="true" aria-controls="<?php echo $continentslug; ?>">
                                <?php echo $continentvalue; ?>
                            </a>
                        </h5>
                    </div>
                    <div id="<?php echo $continentslug; ?>" class="collapse" role="tabpanel" aria-labelledby="">
                        <div class="card-block row">

                            <?php
                            $key++;
                            foreach (getAllCountries($key) as $Countries) {
                                ?>
                                <div class="col-xs-12 col-md-6">

                                    <div class="checkbox-list <?php
                                    if ($Countries['id'] == 228) {
                                        echo 'shippingCountryUS';
                                    }
                                    ?>">
                                        <label class="container-check"><?php echo $Countries['name']; ?>

                                            <input type="checkbox" name="countries[]" value="<?php echo $Countries['id']; ?>"
                                            <?php
                                            if ($Countries['id'] == 228) {
                                                echo 'checked="checked"';
                                            }
                                            ?>
                                                   >
                                            <span class="checkmark"></span>
                                        </label>
                                    </div> checkbox-list 
                                </div>

                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>-->
                <?php
            }
            ?>
        </div><!-- accordion2 -->
    </div><!-- shipping-address -->
</div>
<!-- rghtexp -->

<div class="col-md-4 lftexp clculateRecommeded clculateRecommeded-div">
    <h6>Shipping Services *</h6>
    <p class="small">Buyers can choose these at checkout</p>    
    <div class="mt-xs-1 mt-md-2 text-smaller hide-restore-setting">
        <a id="restore-defaults" href="javascript:;">Restore defaults</a>
    </div>
</div>
<div class="col-md-8 rghtexp clculateRecommeded clculateRecommeded-div"> 
    <div class="ups-priority">
        <div class="shipping-address-gray top-selected">
            <div class="panel-insight">
                <div class="card-header0mbt">
                    <span class="mr-xs-1">6 USPS mail classes</span>
                    <span class="badge badge-gray-darker icon-b-1">Default</span>
                    <a id="data-ui-edit" href="javascript:;">
                        <span class="ss-etsy ss-write icon-smaller icon-t-1 mr-xs-1"></span> <i class="fa fa-pencil" aria-hidden="true"></i> Edit
                    </a>
                </div>
            </div>
        </div><!-- shipping-address-gray -->
        <div class="shipping-address-gray section-insight">
            <div class="col-group col-flush mt-xs-1 panel-body-row">



                <?php
                $shippingServiceArray = getShippingServicesList();
                foreach ($shippingServiceArray as $key => $value) {
                    ?>
                    <div class="listing-checkbox">
                        <div class="col-md-8 pr-xs-0">
                            <label class="container-check" for="<?php echo $value['keyId']; ?>_edit">
                                <?php echo $value['title']; ?>
                                <input type="checkbox" class="checkbox"  value="<?php echo $value['keyId']; ?>" id="<?php echo $value['keyId']; ?>_edit" name="shippingServices[]" 

                                       <?php
                                       if ($shippingOptionId) {
                                           if (is_array($services) && in_array($value['keyId'], $services)) {
                                               echo 'checked="checked"';
                                           }
                                       } else {
                                           echo 'checked="checked"';
                                       }
                                       ?>
                                       >
                                <span class="checkmark"></span>
                            </label>
                            <div class="text-detail-check">
                                <p class="text-gray-lighter text-smaller"><?php echo $value['description']; ?></p>
                            </div>                                                                                        
                        </div>
                        <div class="col-md-4 hide-xs hide-sm text-smaller text-gray text-right">
                            <?php echo $value['days']; ?>                                                                           
                        </div>
                    </div><!-- listing-checkbox -->
                    <?php
                }
                ?>
<!--                <div class="advanced-shipping-service-section">
                    <div class="heading-section-ad">
                        <a href="javascript:;" id="advanced-option-sec">
                            Advanced shipping services
                        </a>
                    </div>

                    <div class="advanced-option-body" id="">
                        <div class="listing-checkbox">
                            <div class="col-md-8 pr-xs-0">
                                <label class="container-check" for="SUSPS-Media-Mail">
                                    SUSPS Media Mail
                                    <input type="checkbox" class="checkbox" value="susps-media-mail" id="SUSPS-Media-Mail" name="shippingServices[]"

                                           <?php
                                           if (is_array($services) && in_array('susps-media-mail', $services)) {
                                               echo 'checked="checked"';
                                           }
                                           ?>>
                                    <span class="checkmark"></span>
                                </label>
                                <div class="text-detail-check">
                                    <p class="text-gray-lighter text-smaller">A restricted class of mail used only to send books,</p>
                                    <p class="text-gray-lighter text-smaller">film, printed music, scripts, video, CDs and DVDs.</p>
                                </div>                                                                                        
                            </div>
                            <div class="col-md-4 hide-xs hide-sm text-smaller text-gray text-right">
                                <p>2-8 days</p>    
                                <p>Domestic</p>                                                                                
                            </div>
                        </div> listing-checkbox 
                        <div class="listing-checkbox">
                            <div class="col-md-8 pr-xs-0">
                                <label class="container-check" for="USPS-Parcel-Select-Ground">
                                    USPS Parcel Select Ground
                                    <input type="checkbox" class="checkbox" value="usps-parcel-select" id="USPS-Parcel-Select-Ground" name="shippingServices[]"
                                    <?php
                                    if (is_array($services) && in_array('usps-parcel-select', $services)) {
                                        echo 'checked="checked"';
                                    }
                                    ?>>
                                    >
                                    <span class="checkmark"></span>
                                </label>
                                <div class="text-detail-check">
                                    <p class="text-gray-lighter text-smaller">An economical ground service for regular and large parcels.</p>
                                </div>                                                                                        
                            </div>
                            <div class="col-md-4 hide-xs hide-sm text-smaller text-gray text-right">
                                <p>2-9 days</p>    
                                <p>Domestic</p>                                                                                
                            </div>
                        </div> listing-checkbox 
                    </div> advanced-option-body 
                </div> advanced-shipping-service-section -->

            </div><!-- panel-body-row -->
        </div><!-- section-insigh -->
    </div><!-- ups-priority -->
</div>

<div class="col-md-4 lftexp clculateRecommeded clculateRecommeded-div">
    <h6>Free shipping  <span class="light-color-text">(Optional)</span></h6>
    <p class="small">Want to offer buyers a free shipping option?</p>    
</div>

<div class="col-md-8 rghtexp small-font  clculateRecommeded clculateRecommeded-div"> 
    <div class="form-group ">
        <input type="checkbox"

               <?php
               if ($domesticFreeShippings) {
                   echo 'checked="checked"';
               }
               ?>
               id="domesticFreeShippings" name="domesticFreeShippings"  value="1"><label  for="domesticFreeShippings">Free domestic shipping</label>
        &nbsp; &nbsp; &nbsp;
        <input type="checkbox"

               <?php
               if ($internationalFreeShipping) {
                   echo 'checked="checked"';
               }
               ?>
               id="internationalFreeShipping" name="internationalFreeShipping" value="1" ><label  for="internationalFreeShipping">Free international shipping</label>
    </div> 
</div>


<?php
$displayHandlingFee = '';
$displayNoneHandlingFee = '';
if ($domesticHandlingFee != $internationalHandlingFee) {
    $displayHandlingFee = 'style="display: inline-block;"';
    $displayNoneHandlingFee = 'style="display: none;"';
}
?>
<!-- <div class="col-md-4 lftexp clculateRecommeded clculateRecommeded-div">
    <h6>Handling Fee <span class="light-color-text">(Optional)</span></h6>
    <p class="small">Weight and size are required for calculated profiles. Learn more about calculated shipping.</p>     
</div>  -->
<div class="col-md-4 small-lable rghtexp dollar-signadded clculateRecommeded clculateRecommeded-div"> 

    <div class="form-group hidden-after-link-select">

        <label class="visible-hidden-hide" <?php echo $displayHandlingFee; ?>>Domistic</label>
        <input  onkeypress='Numvalidate(event, "price", "domesticHandlingFee")' type="text" class="form-control"  id="domesticHandlingFee" name="domesticHandlingFee"  value="<?php echo $domesticHandlingFee; ?>" placeholder="">
        <span class="price-dollar">$</span>

    </div>


</div>

<div class="col-md-4 rghtexp small-lable ghtexp dollar-signadded clculateRecommeded clculateRecommeded-div"> 
    <a  href="javascript:;" class="blue-text hidden-after-link-select this-hide-all anchor-link display-inline-block mt-30" <?php echo $displayNoneHandlingFee; ?>>
        <span >Set separate fees for domestic &amp; international</span>
    </a>

    <div class="form-group hidden-first" <?php echo $displayHandlingFee; ?>>
        <label>International</label>
        <input onkeypress='Numvalidate(event, "price", "internationalHandlingFee")' type="text" class="form-control"  id="internationalHandlingFee" name="internationalHandlingFee"  value="<?php echo $internationalHandlingFee; ?>" placeholder="International">
        <span class="price-dollar">$</span>
    </div>  
    <a href="javascript:;" class="remove-added-list" <?php echo $displayHandlingFee; ?>>
        Remove
    </a> 
</div>
<!-- clculateRecommeded end -->