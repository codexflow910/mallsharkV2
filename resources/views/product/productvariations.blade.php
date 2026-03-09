
<div class="col-md-12 table-responsive" id="productVariationTable" style="padding: 0px;width:98%;margin:auto;">
</div>
 

<div class="col-md-12-12 btn_parentVariation " style="display: block;margin-left:12px;">
    <button class="btn btn-primary hide-on-table-show btn-simple height38 ml-0 mr-0 width220"  id="addVariationBtn" type="button" data-toggle="modal" data-target="#price-popup">Add Variations</button>
    <p class="ligt-grey  mb-0 fnt-12" style="margin-top:21px;margin-bottom:3px!important;">Note: For variable products you must define at least one variation of the main product.</p>
    <span class="pull-right">
        <button class="btn btn-primary btn-simple show-on-table-show" id="editVariationBtn" type="button" data-toggle="modal" data-target="#price-popup">Add/Edit Variation</button>
    </span>
    <div class="vrntbx" style="display: none;">    
        <span>
            <input type="checkbox" id="test5"  checked="">
            <label for="test5"></label>
        </span>

        <div class="btn-group" role="group" aria-label="Basic example">
            <button type="button" class="btn btn-secondary">Update Price</button> 
            <button type="button" class="btn btn-secondary">Updat Quantity</button>
        </div>
    </div>
</div>

<div id="price-popup" class="modal fade popup-style" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content clearfix">
            <div class="modal-header" style="padding:1rem!important;">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Edit variations</h4>
            </div>
            <div class="modal-body">
                <div class="uploadariation"><span></span></div>
                <p>List all the options you offer. Buyers will see them in the order they are here.</p>
                <div class="col-group col-flush mb-xs-3  add-variation-popup">

                    <?php
                    
                    $attributesOption = '';
                    if (isset($variationAttributes) && !empty($variationAttributes)) {

                        foreach ($variationAttributes as $attribute) {

                            $attributesOption .= '<option value="' . $attribute->attribute_name . '">' . ucfirst($attribute->attribute_name) . '</option>';
                        }
                    }
                    ?>
                    <div class="variation-last-box col-sm-6 col-md-5" style="display: none;">
                        <!-- <p class="strong mb-xs-1 hide-on-select">Add a variation</p> -->
                        <div class="ui-toolkit hide-on-select form-group">
                            <label class="select-wrap display-inline-block mb-xs-1">
                                <label class="screen-reader-only" for="variation_property">Add a variation</label>
                                <select class="form-control select select-custom select-variation" name="variation_property" id="select-variation">
                                    <option value="0">Choose variation type</option>
                                    <optgroup label="Variation types">
                                        <?php echo $attributesOption; ?>
                                    </optgroup>                        
                                </select>                            
                            </label>
                        </div>
                    </div>
                    <div class="VariationMainDiv row" data-eq="0">
                        <div class="col-sm-6 col-md-5 ">
                            <div class="variation-box">
                                <!-- <p class="strong mb-xs-1 hide-on-select">Add a variation</p> -->
                                <div class="ui-toolkit hide-on-select form-group">
                                    <label class="select-wrap display-inline-block mb-xs-1">
                                        <label class="screen-reader-only" for="variation_property">Add a variation</label>

                                        <select class="form-control select select-custom select-variation" name="variation_property" id="select-variation" style="width: 267px!important;">
                                            <option value="0">Choose variation type</option>
                                            <optgroup label="Variation types">
                                                <?php echo $attributesOption; ?>
                                            </optgroup>
                                            <optgroup label="None of these work?">
                                                <option value="__custom" id="create-a-new">Create a new variation</option>
                                            </optgroup>
                                        </select>                            
                                    </label>
                                </div>
                            </div>


                            <div class="customAttribute input-group-body form-group mb-xs-2" style="margin-bottom: 0px!important;margin-top:-10px;">
                            </div>                 

                            <div class="open-secondary first-open-secondary" id="secondry-open">

                                    <div class="mb-xs-1 results-box">
                                        <span class="strong results-text">
                                            <span class="text-small color" >Flavor</span>
                                        </span>
                                        <button class="btn btn-link text-smaller ml-xs-2 delect-panel delete-variation"  id="delete-variation" type="button">Delete</button>
                                    </div>
                                <div class="variationUnit"></div>
                                <div class="check-list">
                                    <label class="container-check">Prices vary for each <span class="attributeCheckBoxText">variant</span>
                                        <!-- <input id="priceCheckVar1" type="checkbox" checked="checked">
                                        <span class="checkmark"></span> -->
                                    </label>
                                </div>
                                <div class="check-list">
                                    <label class="container-check">Quantities vary for each <span class="attributeCheckBoxText">variant</span>
                                        <!-- <input id="quantityCheckVar1" type="checkbox" checked="checked">
                                        <span class="checkmark"></span> -->
                                    </label>
                                </div>
                                <div class="check-list">
                                    <label class="container-check">SKUs vary for each <span class="attributeCheckBoxText">variant</span>
                                        <!-- <input id="skuCheckVar1" type="checkbox" checked="checked">
                                        <span class="checkmark"></span> -->
                                    </label>
                                </div>

                            </div><!-- open-secondary -->

                        </div>


                        <div class="col-sm-1  col-lg-1"></div>
                        <div class="col-sm-5  col-lg-6">
                            <input type="hidden" name="variationAttributeone"  id="variationAttributeone" class="variation_attribute" value=""/>
                            <input type="hidden" name="variationAttributeUnitone"  id="variationAttributeUnitone" class="variation_attribute_unit" value=""/>
                            <div id="variationOption1" class="selct-box-tags ui-toolkit" style="display:none">
                                <div class="selectFeildVariation variationOptions">
                                </div>  
                                <div class="customFeildVariation">
                                </div>  
                                <div class="select-value varidationOptionValues" id="varidationOptionValues"><span></span></div>

                            </div><!-- selct-box-tags ui-toolkit -->


                        </div>

                    </div>

                    <hr class="mt-xs-4 mb-xs-4 show-on-select-box">
                    <div class="VariationMainDiv row"  data-eq="1" style="display: none;">
                        <div class="col-sm-6 col-md-5 ">
                            <div class="variation-box">
                                <!-- <p class="strong mb-xs-1 hide-on-select">Add a variation</p> -->
                                <div class="ui-toolkit hide-on-select form-group">
                                    <label class="select-wrap display-inline-block mb-xs-1">
                                        <label class="screen-reader-only" for="variation_property">Add a variation</label>
                                        <select class="form-control select select-custom select-variation" name="variation_property" id="select-variation" style="width: 267px!important;">
                                            <option value="0">Choose variation type</option>
                                            <optgroup label="Variation types">
                                                <?php echo $attributesOption; ?>
                                            </optgroup>
                                            <optgroup label="None of these work?">
                                                <option value="__custom" id="create-a-new">Create a new variation</option>
                                            </optgroup>
                                        </select>                            
                                    </label>
                                </div>
                            </div>


                            <div class="customAttribute input-group-body form-group mb-xs-2" style="margin-bottom: 0px!important;margin-top:-10px;">
                            </div>                 

                            <div class="open-secondary second" id="secondry-open" >

                                <div class="mb-xs-1 results-box">
                                    <span class="strong results-text">
                                        <span class="text-small color" >Flavor</span>

                                    </span>
                                    <button class="btn btn-link text-smaller ml-xs-2 delect-panel delete-variation" id="delete-variation" type="button">Delete</button>
                                </div>
                                <div class="variationUnit"></div>
                                <div class="check-list">
                                    <label class="container-check">Pricess vary for each <span class="attributeCheckBoxText">variant</span>
                                        <!-- <input id="priceCheckVar2" type="checkbox" checked="checked">
                                        <span class="checkmark"></span> -->
                                    </label>
                                </div>
                                <div class="check-list">
                                    <label class="container-check">Quantities vary for each <span class="attributeCheckBoxText">variant</span>
                                        <!-- <input id="quantityCheckVar2" type="checkbox" checked="checked">
                                        <span class="checkmark"></span> -->
                                    </label>
                                </div>
                                <div class="check-list">
                                    <label class="container-check">SKUs vary for each <span class="attributeCheckBoxText">variant</span>
                                        <!-- <input id="skuCheckVar2" type="checkbox" checked="checked">
                                        <span class="checkmark"></span> -->
                                    </label>
                                </div>

                            </div>

                        </div>


                        <div class="col-sm-1  col-lg-1"></div>
                        <div class="col-sm-5  col-lg-6">
                            <input type="hidden" name="variationAttributetwo"  id="variationAttributetwo" class="variation_attribute" value=""/>
                            <input type="hidden" name="variationAttributeUnittwo"  id="variationAttributeUnittwo" class="variation_attribute_unit"  value=""/>
                            <div id="variationOption1" class="selct-box-tags ui-toolkit" style="display:none">
                                <div class="selectFeildVariation variationOptions">
                                </div>  
                                <div class="customFeildVariation">
                                </div>  
                                <div class="select-value varidationOptionValues" id="varidationOptionValues2"><span></span></div>

                            </div>


                        </div>
                    </div>
                    <hr class="mt-xs-4 mb-xs-4 show-on-select-box">

                    <div class="row VariationMainDiv" id="thirdvariation" data-eq="2" style="display: none;">
                        <hr class="mt-xs-4 mb-xs-4 show-on-select-box-variation ml-15 mr-15">                        
                        <div class="col-sm-6 col-md-5 ">
                            <div class="variation-box">
                                <!-- <p class="strong mb-xs-1 hide-on-select">Add variation</p> -->
                                <div class="ui-toolkit hide-on-select form-group">
                                    <label class="select-wrap display-inline-block mb-xs-1">
                                        <label class="screen-reader-only" for="variation_property">Add a variation</label>
                                        <select class="form-control select select-custom select-variation" name="variation_property" id="select-variation" style="width: 267px!important;">
                                            <option value="0">Choose variation type</option>
                                            <optgroup label="Variation types">
                                                <?php echo $attributesOption; ?>
                                            </optgroup>
                                            <optgroup label="None of these work?">
                                                <option value="__custom" id="create-a-new">Create a new variation</option>
                                            </optgroup>
                                        </select>                            
                                    </label>
                                </div>
                            </div>


                            <div class="customAttribute input-group-body form-group mb-xs-2" style="margin-bottom: 0px!important;margin-top:-10px;">
                            </div>                 

                            <div class="open-secondary third" id="secondry-open">

                                <div class="mb-xs-1 results-box">
                                    <span class="strong results-text">
                                        <span class="text-small color" >Flavor</span>

                                    </span>
                                    <button class="btn btn-link text-smaller ml-xs-2 delect-panel delete-variation" id="delete-variation" type="button">Delete</button>
                                </div>
                                <div class="variationUnit"></div>
                                <div class="check-list">
                                    <label class="container-check">Prices vary for each <span class="attributeCheckBoxText">variant</span>
                                        <!-- <input id="priceCheckVar2" type="checkbox" checked="checked">
                                        <span class="checkmark"></span> -->
                                    </label>
                                </div>
                                <div class="check-list">
                                    <label class="container-check">Quantities vary for each <span class="attributeCheckBoxText">variant</span>
                                        <!-- <input id="quantityCheckVar2" type="checkbox" checked="checked">
                                        <span class="checkmark"></span> -->
                                    </label>
                                </div>
                                <div class="check-list">
                                    <label class="container-check">SKUs vary for each <span class="attributeCheckBoxText">variant</span>
                                        <!-- <input id="skuCheckVar2" type="checkbox" checked="checked">
                                        <span class="checkmark"></span> -->
                                    </label>
                                </div>

                            </div>

                        </div>
                        <div class="col-sm-1  col-lg-1"></div>
                        <div class="col-sm-5  col-lg-6">
                            <input type="hidden" name="variationAttributethree"  id="variationAttributethree" class="variation_attribute" value=""/>
                            <input type="hidden" name="variationAttributeUnitthree"  id="variationAttributeUnitthree" class="variation_attribute_unit"  value=""/>
                            <div id="variationOption1" class="selct-box-tags ui-toolkit" style="display:block">
                                <div class="selectFeildVariation variationOptions">
                                </div>  
                                <div class="customFeildVariation">
                                </div>  
                                <div class="select-value varidationOptionValues" id="varidationOptionValuesthree"><span></span></div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer" style="padding: 1rem 0rem 0rem 1rem!important;">
                    <button type="button" class="btn btn-default popupCancel" data-dismiss="modal">Cancel</button>
                    <button type="button" disabled="disabled" class="btn btn-default btn-primary-back btn-simple "  data-dismiss="modal" id="btnvariationcheck">Update</button>
                </div>

            </div>
        </div>

    </div>


</div>
<?php
if (isset($variationAttributes) && !empty($variationAttributes)) {
    $attributeArray = array();
    foreach ($variationAttributes as $key => $attribute) {
        if ($attribute->attribute_options) {
            $attributeArray[$attribute->attribute_name] = unserialize($attribute->attribute_options);
        } else {
            if ($attribute->attribute_unit) {
                $unitArray = unserialize($attribute->attribute_unit);
                foreach ($unitArray as $unit) {
                    $attributeArray[$attribute->attribute_name][$unit] = '';
                }
            } else {
                $attributeArray[$attribute->attribute_name] = '';
            }
        }
    }
}
?>
<script type="text/javascript">
    $(document).ready(function(){
        $(".first-open-secondary").hide();
        $(".open-secondary").hide();
    });
    var obj;
    var variationObj = '<?php echo json_encode($attributeArray); ?>';
    obj = jQuery.parseJSON(variationObj);
    var unitHTML = '';
    var unitOptionHTML = '';
    jQuery('body').on('change', '.select-variation', function (e) {
        unitHTML = '';
        unitOptionHTML = '';
        let repeated = false;
        var mainDivVariation = $(this).parentsUntil('.VariationMainDiv').parent();
        mainDivVariation.find('.variationUnit').empty();
        mainDivVariation.find('.variationOptions').empty();
        mainDivVariation.find('.customFeildVariation').empty();
        var variationValue = $(this).find('option:selected').val();
        mainDivVariation.find('.varidationOptionValues').empty();
        let variationExistsError = mainDivVariation.find('.variation-exists');
        if(variationExistsError){
            variationExistsError.remove();
        }
        $('#edit-variation-txt').empty();
        switch (variationValue) {
            case '':

                break;
            case '__custom':

                var inputField = customTextField('Name the variation', 'customAttributeField');
                mainDivVariation.find('.customAttribute').show()
                let inputExists = mainDivVariation.find('.customAttribute').find('.add_new_option_input');
                
                // if(inputExists.length < 1){

                    mainDivVariation.find('.customAttribute').append(inputField);
                // }
                break;
            default:
                
            $('.VariationMainDiv').find('.results-text .color').each((index,item)=>{
                $(item).text();
            })
                jQuery.each(obj, function (key, data) {
                    if (key === variationValue) {
                        $('.VariationMainDiv').find('.results-text .color').each((index,item)=>{
                                
                                if($(item).text().toLowerCase() == variationValue.toLowerCase()){
                                    if(mainDivVariation.find('.results-text .color') != item){

                                        mainDivVariation.find('.select-wrap').append('<p class="variation-exists" style="color: red; font-weight: normal;">Variation already added</p>')
                                        repeated = true
                                        return;
                                    }
                                }
                                })
                        if (data.length > 0) {
                           
                            unitOptionHTML += '<select class="select select-custom attributeOptions" style="background-color:#fff;">';
                            unitOptionHTML += '<option value="">Add ' + capitalize(variationValue) + ' options</option>';
                            unitOptionHTML += '<optgroup label="' + capitalize(variationValue) + ' options">';
                            jQuery.each(data, function (key_option, option_val) {
                                unitOptionHTML += '<option value="' + option_val + '">' + capitalize(option_val) + '</option>';
                            });
                            unitOptionHTML += '</optgroup>';
                            unitOptionHTML += '</optgroup>';
                            unitOptionHTML += '<optgroup id="__custom" label="None of these work?">';
                            unitOptionHTML += '<option value="__custom"> Create a new option </option>';
                            unitOptionHTML += '</optgroup>';
                            unitOptionHTML += '</select>';
                        } else {
                            if (!isEmptyObject(data)) {

                                unitHTML += '<div class="form-group">';
                                unitHTML += '<select class="form-control attributeUnits">';
                                unitHTML += '<option value="">Choose a unit</option>';
                                jQuery.each(data, function (key_unit, unit_val) {
                                    unitHTML += '<option value="' + key_unit + '">' + capitalize(key_unit) + '</option>';
                                });

                                unitHTML += '</select>';
                                unitHTML += '</div>';
                            } else {

                                var optionHTML = customTextField('Type Variations here');
                                mainDivVariation.find('.variationOptions').append(optionHTML);
                            }
                        }
                    }
                });
                if(repeated){
                    break;
                }
                mainDivVariation.find('.selct-box-tags').show();
                mainDivVariation.find('.customvariation').hide();
                mainDivVariation.find(".hide-on-select").hide();
                mainDivVariation.find('.open-secondary').show();
            //    mainDivVariation.find('.attributeCheckBoxText').text(variationValue);
                mainDivVariation.find('.attributeCheckBoxText').text("variant");
                mainDivVariation.find('.show-on-select-box').show();
                mainDivVariation.find('.variation_attribute').val(capitalize(variationValue));
                mainDivVariation.find('.results-text .color').text(capitalize(variationValue));
                mainDivVariation.find('.customvariation').hide();
                if (unitHTML) {
                    mainDivVariation.find('.variationUnit').append(unitHTML);
                } else {
                    mainDivVariation.find('.variationOptions').append(unitOptionHTML);
                }
                //mainDivVariation.find('.uploadariation').hide();
                $('.show-on-select-box').show();
                // alert("called");
                aa = $(this).closest('.VariationMainDiv').attr('data-eq');
                aa = parseInt(aa) + 1;
                if (isNaN(aa)) {
                    aa = parseInt(1);
                }
                if(aa == 1){
                    $('.VariationMainDiv').eq(aa).show();
                    // $('.VariationMainDiv').eq(aa).find('.second').hide();
                }else if(aa == 2){
                    $('.VariationMainDiv').eq(aa).show();
                    // $('.VariationMainDiv').eq(aa).find('.third').hide();
                }
                
                // aa.closest('.VariationMainDiv').show();
             
                mainDivVariation.find('.customAttribute').hide()
                mainDivVariation.find('.customAttribute').find('.add_new_option_input').remove();
                mainDivVariation.find('.customAttribute').find('.customAttributeField').remove();
                // $('.VariationMainDiv').find("[value='" + variationValue + "']").hide();
                break;
        }
    });




  $(document).ready(function() {
    // Set targetDiv to display: none initially
    var targetDiv = $('.mt-xs-4.mb-xs-4.show-on-select-box').css('display', 'none');

    $('.form-control.select.select-custom.select-variation').change(function() {
      var selectedValue = $(this).val();

      // Set the display property based on the selected value
      targetDiv.css('display', selectedValue !== '' ? 'block' : 'none');
    });
  });

</script>