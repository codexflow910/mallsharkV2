<?php
$countriesList = array();
$allCountries = getAllCountries();
foreach ($allCountries as $key => $country) {
    $countriesList[$country['id']] = $country['name'];
    ///echo '<option value="' . $country['country_id'] . '">' . $country['country_name'] . '</option>';
}
?>
<script type="text/javascript">

    var countriesObj = '<?php echo json_encode($countriesList); ?>';
    countriesObj = jQuery.parseJSON(countriesObj);
    jQuery('body').on('click', '#add-another-location,#add-another-location-popup', function (event) {
        jQuery('body').find('.popupCountriesMange').each(function () {
            //// alert($(this).val());
            delete countriesObj[$(this).val()];
            /// console.log(countriesObj)

        });

        $countriesOption = '';
        jQuery.each(countriesObj, function (key, cuntriesData) {
            ///console.log(cuntriesData)
            $countriesOption += '<option value="' + key + '">' + cuntriesData + '</option>';
        });
        $(event.target).closest('.fixed-shipping-cost').find('.parentFixedShipping').append('<div class="row entry-body"><div class="col-sm-4"><div class="form-group"><div class="form-group equal-width-mbt"><select class="form-control countryFixCost popupCountriesMange" name="shippingFixCost[destinationId][]">' + $countriesOption + ' </select></div></div></div><div class="col-sm-4"><div class="primary-cost common-mbt-stle"><label class="input-prepend has-validation"><div class="input-prepend-item"> &#36;</div><input type="text" name="shippingFixCost[oneItem][]" class="input form-control countryFixCostPriceOneItem required-field" value="" placeholder="0.00"></label></div></div><div class="col-sm-3"><div class="secondary-cost common-mbt-stle"><label class="input-prepend has-validation"><div class="input-prepend-item"> &#36;</div><input type="text" class="input form-control countryFixCostPriceEachItem"  name="shippingFixCost[eachItem][]" value="" placeholder=""></label></div></div><div class="col-sm-1 text-center"><a href="javascript:;" class="align-with-btn text-gray-lighter cross-icon-cont remove-container"><span class="ss-etsy ss-delete icon-smaller icon-t-2"><i class="fa fa-times" aria-hidden="true"></i></span></a></div></div>');
    });

</script>


<div id="shipping-popup" class="modal fade popup-style show shipping-popup-modal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content clearfix">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&#215;</button>
                <h4 class="modal-title">Shipping profile</h4>
            </div>
            <div class="modal-body">
                <div class="uploadariation"><span></span></div>
                <div class="shippingProfileResposne"></div>

            </div><!-- modal-body -->

            <div class="modal-footer">
            <button type="button" class="btn btn-default large-btn-mbt  btn-simple " data-dismiss="modal">Cancel</button>
            <?php 
                $currentURL = request()->url();
                $manged_shipping = URL::to('/managedshipping');
                if($currentURL != $manged_shipping){ ?>

                <button type="button" class="btn btn-primary" id="updateDuplicateProfile">Save profile</button>
                <?php }else{ ?>
                <button type="button" class="btn btn-primary" id="updateDuplicateMangeShippingProfile">Save profile</button>

            <?php } ?>
               
            </div>
        </div><!-- modal-content -->
    </div><!-- modal-dialog -->
</div><!-- shipping-popup -->