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
$attribute1 = $attribute2 = $option1 = $option2 = $option3 = array();
$attributevalue1 = $attributevalue2 = $unit1 = $unit2 = $unit3 = '';
foreach ($variations as $key => $attribute) {
    if (($attribute->attribute1)) {
        $attributevalue1 = $attribute->attribute1;
        $unit1 = $attribute->unit1;
        if ($attribute->option1) {
            if (!(in_array($attribute->option1, $option1))) {
                $option1[] = $attribute->option1;
            }
        }
    }
    if (($attribute->attribute2)) {
        $attributevalue2 = $attribute->attribute2;
        $checkBoxsku2 = $attribute->check_box_sku2;
        $unit2 = $attribute->unit2;
        if (!(in_array($attribute->option2, $option2))) {
            $option2[] = $attribute->option2;
        }
    }
    if ($attribute->attribute3) {
        $attributevalue3 = $attribute->attribute3;
        $unit3 = $attribute->unit3;
        if (!(in_array($attribute->option3, $option3))) {
            $option3[] = $attribute->option3;
        }
    }
}
?>
<div class="row-full">
    <div class="custom-container clearfix">
        <div class="btn_parentVariation mobile-spacing-zero">
            <?php
            $showDisplayAdd = '';
            $showDisplayEdit = '';

            if (isset($variations) && count($variations) > 0) {
                $showDisplayAdd = 'display:none';
                $showDisplayEdit = 'opacity: 1;visibility: visible;';
            }
            ?>
            <?php
            $typeArray = array('simple','virtual');
            if (!in_array($prodcutDetails['product_type'], $typeArray)) {
                ?>
             
                <button style="<?php echo $showDisplayAdd; ?>" class="btn btn-primary hide-on-table-show btn-simple" id="addVariationBtn" type="button" data-toggle="modal" data-target="#price-popup">Add Variation</button>
                <span >
                    <button  style="<?php echo $showDisplayEdit; ?>" class="btn btn-primary btn-simple show-on-table-show" id="editVariationBtn" type="button" data-toggle="modal" data-target="#price-popup">Add / Edit Variations</button>
                    <p class="ligt-grey fnt-12"  style="margin-top:15px;" >Note: For variable products you must define at least one variation of the main product.</p>
                </span>
            <?php } ?>
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
    </div>

    <div class="custom-container clearfix">
        <div class="table-responsive" id="productVariationTable" style="padding:0px;">
            <?php
            if (isset($variations) && count($variations) > 0) {
                ?>
                <table class="table tblvrnt border" >
                    <thead class="thead-light">
                        <tr id="tableHeadvaridation">

                            <?php if (!empty($attributevalue1)) { ?>
                            <th scope="col"><?php echo ucfirst($attributevalue1); ?> <?php echo !empty($unit1)? " (".$unit1.")":""; ?></th>
                            <?php } ?>
                            <?php if (!empty($attributevalue2)) { ?>
                                <th scope="col"><?php echo ucfirst($attributevalue2); ?><?php echo !empty($unit2)? " (".$unit2.")":""; ?></th>
                            <?php } ?>
                            <?php if (!empty($attributevalue3)) { ?>
                                <th scope="col"><?php echo ucfirst($attributevalue3); ?><?php echo !empty($variations[0]->unit3)? " (".$variations[0]->unit3.")":""; ?></th>
                            <?php } ?>                      
                            <th scope="col">Price</th>
                            <th scope = "col">SKU</th>
                                <th scope = "col">Current Stock</th>
<!--                                <a href="#" data-toggle="tooltip" data-placement="top" title="Hooray!">Help</a>-->
                                <!-- <th scope = "col" class="text-center">Available </th> -->
                                <th scope="col" class="text-center">Associate Image</th>
                            <th scope = "col" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody id="tablevaridation">
                        <?php
                        // dd($variations);
                        foreach ($variations as $key => $VariationOptionvalue) {
                            $imageValue = $VariationOptionvalue->product_variation_image;
                           // echo 'Data: <pre>' .print_r($VariationOptionvalue,true). '</pre>'; die;
                            ?>
                            <tr class="variation_tr">
                                <?php if (isset($VariationOptionvalue->option1)) { ?>
                                    <td class="clm2 align-middle"><?php echo ucfirst($VariationOptionvalue->option1); ?></td>
                                <?php } ?>
                                <?php if (isset($VariationOptionvalue->option2)) { ?>
                                    <td class="clm2 align-middle"><?php echo ucfirst($VariationOptionvalue->option2); ?>
                                    <?php } ?>
                                    <?php if (isset($VariationOptionvalue->option3)) { ?>
                                    <td class="clm2 align-middle"><?php echo ucfirst($VariationOptionvalue->option3); ?>
                                    <?php } ?>
                                    <input name="ProductvariationData[variationID][]" value="<?php echo $vid1 = isset($VariationOptionvalue->variation_id) ? $VariationOptionvalue->variation_id : ''; ?>" type="hidden">
                                    <input name="ProductvariationData[attribute1][]" value="<?php echo $attrid1 = isset($attributevalue1) ? $attributevalue1 : ''; ?>" type="hidden">
                                    <input name="ProductvariationData[attribute2][]" value="<?php echo $attrid2 = isset($attributevalue2) ? $attributevalue2 : ''; ?>" type="hidden">
                                    <input name="ProductvariationData[attribute3][]" value="<?php echo $attrid3 = isset($attributevalue3) ? $attributevalue3 : ''; ?>" type="hidden">
                                    <input name="ProductvariationData[unit1][]" value="<?php echo $unit1 = isset($unit1) ? $unit1 : ''; ?>" type="hidden">
                                    <input name="ProductvariationData[unit2][]" value="<?php echo $unit2 = isset($unit2) ? $unit2 : ''; ?>" type="hidden">
                                    <input name="ProductvariationData[unit3][]" value="<?php echo $unit3 = isset($unit3) ? $unit3 : ''; ?>" type="hidden">
                                    <input name="ProductvariationData[option1][]" value="<?php echo $op1 = isset($VariationOptionvalue->option1) ? $VariationOptionvalue->option1 : ''; ?>" type="hidden">
                                    <input name="ProductvariationData[option2][]" value="<?php echo $op2 = isset($VariationOptionvalue->option2) ? $VariationOptionvalue->option2 : ''; ?>" type="hidden">
                                    <input name="ProductvariationData[option3][]" value="<?php echo $op3 = isset($VariationOptionvalue->option3) ? $VariationOptionvalue->option3 : ''; ?>" type="hidden">
                                    <input name="ProductvariationData[sales][]" value="<?php echo $sales = isset($VariationOptionvalue->sales) ? $VariationOptionvalue->sales : ''; ?>" type="hidden">
                                 

                                </td>
                                <?php
                                $price = '';
                                $validateClass = '';
                                if ($VariationOptionvalue->visibility == 1) {
                                    $validateClass = 'required-field';
                                }
                                if ($VariationOptionvalue->price == 0) {
                                    $price = '';
                                } else {
                                    $price = $VariationOptionvalue->price;
                                }
                                ?>
                                <td class="align-middle"><input name="ProductvariationData[price][]" onkeypress="return isNumber(event)" value="<?php echo $price; ?>" type="text" class="form-control vrpris <?php echo $validateClass; ?>" id="#"></td>
                                <td class="align-middle"> <input name="ProductvariationData[sku][]" value="<?php echo $VariationOptionvalue->sku; ?>"  type="text" class="form-control vrsku " id="#"></td>
                                <td class="align-middle"><input name="ProductvariationData[current_stock][]" onkeypress="return isNumber(event)" value="<?php echo $VariationOptionvalue->current_stock; ?>"  type="text" class="form-control vrstk" id="vrstk-{{$key+1}}" max="100000" maxlength="6"></td>
                                <!-- <td class="text-center"> -->
                                    <!-- <div class="checkboxParentList">    
                                         <input type="checkbox" value="<?php // echo $key + 1; ?>" class="variation-visible" id="visibility_<?php echo $key; ?>"  -->
                                    <?php
                                    // if ($VariationOptionvalue->visibility) {
                                    //  //   dd($VariationOptionvalue);
                                    //     echo 'checked="" ';
                                    // };
                                    ?> 
                                    <!-- name="ProductvariationData[visibility][]" -->
                                    <!-- > -->
                                    <!-- <label for="visibility_<?php // echo $key; ?>"></label></td> -->
                                    <!-- <td class="text-center">
                                        <label class="form-check-label">                            
                                            <a class=" showImagePopup" id="showProductImage_<?php // echo $key; ?>" data-id="showProductImage_<?php //echo $key; ?>" data-variation-id="<?php echo $VariationOptionvalue->product_variation_image; ?>">
                                                <i class="material-icons">image</i></a>
                                                <input name="ProductvariationData[product_variation_image][]" class="product_variation_image "  value="<?php echo $VariationOptionvalue->product_variation_image; ?>" type="hidden">
                                        </label>
                                     </div>
                                   </td> -->
                                   <td class="text-center ">
                                        <label class="form-check-label">                            
                                            <a class=" showVeriationImage" id="showProductImage_<?php echo $key; ?>" data-id="showProductImage_<?php echo $key; ?>" data-variationimage-id="<?php echo $imageValue; ?>" data-variationimage-key="<?php echo $key; ?>" data-product-id="<?php echo $VariationOptionvalue->product_id; ?>">
                                                <input name="ProductvariationData[product_variation_image][<?php echo $key; ?>]" class="product_variation_image_new product_variation_image_new_<?php echo $imageValue; ?>" data-veriKey="<?php echo $key; ?>"  value="<?php echo $imageValue; ?>" type="hidden" >
                                                <i class="material-icons">image</i></a>
                                        </label>
                                     </div>
                                   </td>
                                <td class="text-center align-middle"><a class="remove-variation align-middle" style="color: #183b56;" href="#"><i class="material-icons">edit/delete
                                                                        
                                                                    </i></a></td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
                <?php
            }
            ?>
        </div>
    </div>
</div>

<div id="price-popup" class="modal fade popup-style" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content clearfix">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Edit variations</h4>
            </div>
            <div class="modal-body">
                <div class="uploadariation"><span></span></div>
                <?php
                ?>
                <p>List all the options you offer. Buyers will see them in the order they are here.</p>
                <div class="col-group col-flush mb-xs-3  add-variation-popup">

                    <?php
                    $attributesOption = '';
                    $attSelected = '';
                    if (isset($variationAttributes) && !empty($variationAttributes)) {

                        foreach ($variationAttributes as $attribute) {
//                                if ($attribute->attribute_name == $attributevalue1) {
//                                    $attSelected = 'selected="selected"';
//                                } else {
//                                    $attSelected = '';
//                                }
                            $attributesOption .= '<option  value="' . $attribute->attribute_name . '">' . ucfirst($attribute->attribute_name) . '</option>';
                        }
                    }

                    if (!empty($attributevalue1)) {
                        $firstVariationDisplay = 'style="display:none;"';
                        $firstVariationDisplayBox = 'style="display:block;"';
                    } else {
                        $firstVariationDisplay = 'style="display:block;"';
                        $firstVariationDisplayBox = 'style="display:none;"';
                    }
                    if (!empty($attributevalue2)) {

                        $secondVariationMain = '';
                        $secondVariationDisplay = 'style="display:none;"';
                        $secondVariationDisplayBox = 'style="display:block;"';
                    } else {

                        $secondVariationMain = 'style="display:none;"';
                        $secondVariationDisplay = 'style="display:block;"';
                        $secondVariationDisplayBox = 'style="display:none;"';
                    }
                    if (isset($attributevalue3)) {
                        $thirdVariationMain = '';
                        $thirdVariationDisplay = 'style="display:none;"';
                        $thirdVariationDisplayBox = 'style="display:block;"';
                    } else {
                        $thirdVariationMain = 'style="display:none;"';
                        $thirdVariationDisplay = 'style="display:block;"';
                        $thirdVariationDisplayBox = 'style="display:none;"';
                    }
                    ?>
                    <div class="VariationMainDiv row" data-eq="0">
                        <div class="col-sm-6 col-md-5 " style="margin-top:-10px;">
                            <div class="variation-box" >
                                <div class="ui-toolkit hide-on-select form-group" <?php echo $firstVariationDisplay; ?>>
                                    <label class="select-wrap display-inline-block mb-xs-1">
                                        <label class="screen-reader-only" for="variation_property">Add a variation</label>
                                        <select class="select select-custom select-variation form-control" name="variation_property" id="select-variation" style="width: 267px!important;">
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


                            <div class="customAttribute input-group-body form-group mb-xs-2" style="margin-bottom: 0px!important;">
                            </div>                 

                            <div class="open-secondary" id="secondry-open" <?php echo $firstVariationDisplayBox; ?>>

                                <div class="mb-xs-1 results-box">
                                    <span class="strong results-text">
                                        <span class="text-small color"  style=" text-transform: capitalize "><?php echo ucfirst($attributevalue1); ?></span>

                                    </span>
                                    <button type="button" class="btn btn-link text-smaller ml-xs-2 delect-panel delete-variation" id="delete-variation">Delete</button>
                                </div>
                                <div class="variationUnit"></div>



                                <div class="check-list">
                                    <label class="container-check">Prices vary for each variant <?php // echo $attributevalue1; ?>
                                        <!-- <input id="priceCheckVar1" checked="checked" type="checkbox">
                                        <span class="checkmark"></span> -->
                                    </label>
                                </div>
                                <div class="check-list">
                                    <label class="container-check">Quantities vary for each variant <?php //echo $attributevalue1; ?>
                                        <!-- <input id="quantityCheckVar1" checked="checked"  type="checkbox">
                                        <span class="checkmark"></span> -->
                                    </label>
                                </div>
                                <div class="check-list">
                                    <label class="container-check">SKUs vary for each variant<?php //echo $attributevalue1; ?>
                                        <!-- <input id="skuCheckVar1" checked="checked" type="checkbox" >
                                        <span class="checkmark"></span> -->
                                    </label>
                                </div>

                            </div><!-- open-secondary -->

                        </div>


                        <div class="col-sm-1  col-lg-1"></div>
                        <div class="col-sm-5  col-lg-6">
                            <input type="hidden" name="variationAttributeone"  id="variationAttributeone" class="variation_attribute" value="<?php echo $attributevalue1; ?>"/>
                            <input type="hidden" name="variationAttributeUnitone"  id="variationAttributeUnitone" class="variation_attribute_unit" value="<?php echo $unit1; ?>"/>
                            <div id="variationOption1" class="selct-box-tags ui-toolkit" >
                                <div class="selectFeildVariation variationOptions">
                                    <?php
                                    $firstOptionvalueArray = '';

                                    $attributevalue1 = str_replace('<br>', '', $attributevalue1);
                                    foreach ($variationAttributes as $key => $value) {
                                        if ($value->attribute_name == $attributevalue1) {
                                            if ($value->attribute_options) {
                                                $firstOptionvalueArray = unserialize($value->attribute_options);
                                            }
                                        }
                                    }

                                    if ($firstOptionvalueArray) {
                                        if (is_array($firstOptionvalueArray)) {
                                            ?>

                                            <select class="select select-custom attributeOptions">
                                                <option value="">Add <?php echo ucfirst($attributevalue1); ?> options</option>
                                                <optgroup label="<?php echo ucfirst($attributevalue1); ?> options">
                                                    <?php
                                                    foreach ($firstOptionvalueArray as $op1_value) {
                                                        if (in_array(ucfirst($op1_value), $option1)) {
                                                            echo '<option style="display:none" value="' . $op1_value . '">' . ucfirst($op1_value) . '</option>';
                                                        } else {
                                                            echo '<option value="' . $op1_value . '">' . ucfirst($op1_value) . '</option>';
                                                        }
                                                    }
                                                    ?>


                                                </optgroup>
                                                <optgroup id="__custom" label="None of these work?">
                                                    <option value="__custom"> Create a new option </option>
                                                </optgroup>
                                            </select>
                                            <?php
                                        }
                                    } else {
                                        ?>

                                        <div class="input-group-body form-group">
                                            <div class="input-group-body">
                                                <input type="text" class="form-control add_new_option_input" name="add_new_option_input" placeholder="Type Variations here">
                                            </div>
                                            <div class="input-group-btnn">
                                                <button name="add-custom" type="button" class="btn btn-primary btn-append variation_optin_event" type="button" id="variation_optin_event">Add</button>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    ?>






                                </div>  
                                <div class="customFeildVariation">
                                </div>  
                                <div class="select-value varidationOptionValues" id="varidationOptionValues"><span></span>
                                    <?php
                                    if (is_array($option1) && count($option1) > 0) {
                                        foreach ($option1 as $key => $value_op_selected) {
                                            ?>
                                            <div class="variationOptionID">
                                                <input type="text" readonly class="form-control" value="<?php echo htmlspecialchars($value_op_selected, ENT_QUOTES, 'UTF-8'); ?>">
                                                <a optionval="<?php echo strtolower($value_op_selected); ?>" class="cross-icon"><i class="fa fa-times" aria-hidden="true"></i></a>
                                            </div>
                                            <?php
                                        }
                                    }
                                    ?>

                                </div>

                            </div><!-- selct-box-tags ui-toolkit -->


                        </div>

                    </div>

                    <hr class="mt-xs-4 mb-xs-4 show-on-select-box">

                    <div class="VariationMainDiv row" data-eq="1">
                        <div class="col-sm-6 col-md-5 " style="margin-top:-10px;">
                            <div class="variation-box">
                               
                                <div class="ui-toolkit hide-on-select form-group" <?php echo $secondVariationDisplay; ?>>
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


                            <div class="customAttribute input-group-body form-group mb-xs-2" style="margin-bottom: 0px!important;">
                            </div>                 

                            <div class="open-secondary" id="secondry-open" <?php echo $secondVariationDisplayBox; ?>>

                                <div class="mb-xs-1 results-box">
                                    <span class="strong results-text">
                                        <span class="text-small color"  style=" text-transform: capitalize "><?php echo ucfirst($attributevalue2); ?></span>

                                    </span>
                                    <button type="button" class="btn btn-link text-smaller ml-xs-2 delect-panel delete-variation" id="delete-variation">Delete</button>
                                </div>
                                <div class="variationUnit"></div>


                                <div class="check-list">
                                    <label class="container-check">Prices vary for each variant<?php //echo $attributevalue2; ?>
                                        <!-- <input id="priceCheckVar2-<?php echo rand(0, 9); ?>" checked="" type="checkbox" >
                                        <span class="checkmark"></span> -->
                                    </label>
                                </div>
                                <div class="check-list">
                                    <label class="container-check">Quantities vary for each variant<?php //echo $attributevalue2; ?>
                                        <!-- <input id="quantityCheckVar2-<?php echo rand(0, 9); ?>" checked="" type="checkbox" >
                                        <span class="checkmark"></span> -->
                                    </label>
                                </div>
                                <div class="check-list">
                                    <label class="container-check">SKUs vary for each variant<?php //echo $attributevalue2; ?>
                                        <!-- <input checked="" id="skuCheckVar2-<?php echo rand(0, 9); ?>" type="checkbox">
                                        <span class="checkmark"></span> -->
                                    </label>
                                </div>

                            </div><!-- open-secondary -->

                        </div>


                        <div class="col-sm-1  col-lg-1"></div>
                        <div class="col-sm-5  col-lg-6">
                            <input type="hidden" name="variationAttributetwo"  id="variationAttributetwo" class="variation_attribute" value="<?php echo $attributevalue2; ?>"/>
                            <input type="hidden" name="variationAttributeUnittwo"  id="variationAttributeUnittwo" class="variation_attribute_unit"  value="<?php echo $unit2; ?>"/>
                            <div id="variationOption1" class="selct-box-tags ui-toolkit">
                                <div class="selectFeildVariation variationOptions">


                                    <?php
                                    $secondOptionvalueArray = '';
                                    $attributevalue2 = str_replace('<br>', '', $attributevalue2);
                                    foreach ($variationAttributes as $key => $value) {
                                        if ($value->attribute_name == $attributevalue2) {
                                            if ($value->attribute_options) {
                                                $secondOptionvalueArray = unserialize($value->attribute_options);
                                            }
                                        }
                                    }

                                    if ($secondOptionvalueArray) {
                                        if (is_array($secondOptionvalueArray)) {
                                            ?>

                                            <select class="select select-custom form-control attributeOptions">
                                                <option value="">Add <?php echo ucfirst($attributevalue2); ?> options</option>
                                                <optgroup label="<?php echo ucfirst($attributevalue2); ?> options">
                                                    <?php
                                                    foreach ($secondOptionvalueArray as $op2_value) {
                                                        if (in_array(ucfirst($op2_value), $option2)) {
                                                            echo '<option style="display:none" value="' . $op2_value . '">' . ucfirst($op2_value) . '</option>';
                                                        } else {
                                                            echo '<option value="' . $op2_value . '">' . ucfirst($op2_value) . '</option>';
                                                        }
                                                    }
                                                    ?>


                                                </optgroup>
                                                <optgroup id="__custom" label="None of these work?">
                                                    <option value="__custom"> Create a new option </option>
                                                </optgroup>
                                            </select>
                                            <?php
                                        }
                                    } else {
                                        if ($attributevalue2) {
                                            ?>

                                            <div class="input-group-body form-group">
                                                <div class="input-group-body">
                                                    <input type="text" class="form-control add_new_option_input" name="add_new_option_input" placeholder="Type Variations here">
                                                </div>
                                                <div class="input-group-btnn">
                                                    <button name="add-custom" class="btn btn-primary btn-append variation_optin_event" type="button" id="variation_optin_event">Add</button>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                    }
                                    ?>

                                </div>  
                                <div class="customFeildVariation">
                                </div>  
                                <div class="select-value varidationOptionValues" id="varidationOptionValues2"><span></span>


                                    <?php
                                    if (is_array($option2) && count($option2) > 0) {
                                        foreach ($option2 as $key => $value_op2_selected) {
                                            ?>
                                            <div class="variationOptionID">
                                                <input type="text" readonly class="form-control" value="<?php echo htmlspecialchars($value_op2_selected, ENT_QUOTES, 'UTF-8'); ?>">
                                                <a optionval="<?php echo strtolower($value_op2_selected); ?>" class="cross-icon"><i class="fa fa-times" aria-hidden="true"></i></a>
                                            </div>
                                            <?php
                                        }
                                    }
                                    ?>
                                </div>

                            </div><!-- selct-box-tags ui-toolkit -->


                        </div>
                    </div>

                    <?php
                    $attributevalue3 = isset($attributevalue3) ? $attributevalue3 : '';
                    $mainDivThree = 'style="display:none"';
                    if ($attributevalue2) {
                        $mainDivThree = 'style="display:flex"';
                    }
                    ?>
                    <hr class="mt-xs-4 mb-xs-4 show-on-select-box" <?php echo $mainDivThree; ?>>
                    <div class="VariationMainDiv row" data-eq="2" <?php echo $mainDivThree; ?>>
                        <div class="col-sm-6 col-md-5 " style="margin-top:-10px;">
                            <div class="variation-box">
                              
                                <div class="ui-toolkit hide-on-select form-group" <?php echo $thirdVariationDisplay; ?>>
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


                            <div class="customAttribute input-group-body form-group mb-xs-2" style="margin-bottom: 0px!important;">
                            </div>                 

                            <div class="open-secondary" id="secondry-open" <?php echo $thirdVariationDisplayBox; ?>>

                                <div class="mb-xs-1 results-box">
                                    <span class="strong results-text">
                                        <span class="text-small color"  style=" text-transform: capitalize "><?php echo $thirdattr = isset($attributevalue3) ? ucfirst($attributevalue3) : ""; //ucfirst($attributevalue3);                                    ?></span>

                                    </span>
                                    <button type="button" class="btn btn-link text-smaller ml-xs-2 delect-panel delete-variation" id="delete-variation">Delete</button>
                                </div>
                                <div class="variationUnit"></div>


                                <div class="check-list">
                                    <label class="container-check">Prices vary for each variant<?php //echo $thirdattr = isset($attributevalue3) ? ($attributevalue3) : ""; ?>
                                        <!-- <input id="priceCheckVar2" checked="" type="checkbox">
                                        <span class="checkmark"></span> -->
                                    </label>
                                </div>
                                <div class="check-list">
                                    <label class="container-check">Quantities vary for each variant<?php //echo $attributevalue2; ?>
                                        <!-- <input id="quantityCheckVar3" checked="" type="checkbox"  >
                                        <span class="checkmark"></span> -->
                                    </label>
                                </div>
                                <div class="check-list">
                                    <label class="container-check">SKUs vary for each variant<?php
                                                //echo $thirdattr = isset($attributevalue3) ? ($attributevalue3) : "";
                                                ;
                                                ?>
                                        <!-- <input id="skuCheckVar3"  checked=""type="checkbox">
                                        <span class="checkmark"></span> -->
                                    </label>
                                </div>

                            </div><!-- open-secondary -->

                        </div>


                        <div class="col-sm-1  col-lg-1"></div>
                        <div class="col-sm-5  col-lg-6">
                            <input type="hidden" name="variationAttributethree"  id="variationAttributethree" class="variation_attribute" value="<?php echo $aa = isset($attributevalue3) ? $attributevalue3 : ""; ?>"/>
                            <input type="hidden" name="variationAttributeUnitthree"  id="variationAttributeUnitthree" class="variation_attribute_unit"  value="<?php echo $unit3; ?><?php echo $unit3; ?>"/>

                            <div id="variationOption3" class="selct-box-tags ui-toolkit">
                                <div class="selectFeildVariation variationOptions">


                                    <?php
                                    $thirdOptionvalueArray = '';
                                    if (isset($attributevalue3)) {
                                        $attributevalue3 = str_replace('<br>', '', $attributevalue3);
                                        foreach ($variationAttributes as $key => $value) {
                                            if ($value->attribute_name == $attributevalue3) {
                                                if ($value->attribute_options) {
                                                    $secondOptionvalueArray = unserialize($value->attribute_options);
                                                }
                                            }
                                        }
                                    }

                                    if ($thirdOptionvalueArray) {
                                        if (is_array($thirdOptionvalueArray)) {
                                            ?>

                                            <select class="select select-custom attributeOptions">
                                                <option value="">Add <?php echo ucfirst($attributevalue3); ?> options</option>
                                                <optgroup label="<?php echo ucfirst($attributevalue3); ?> options">
                                                    <?php
                                                    foreach ($thirdOptionvalueArray as $op3_value) {
                                                        if (in_array(ucfirst($op3_value), $option3)) {
                                                            echo '<option style="display:none" value="' . $op3_value . '">' . ucfirst($op3_value) . '</option>';
                                                        } else {
                                                            echo '<option value="' . $op3_value . '">' . ucfirst($op3_value) . '</option>';
                                                        }
                                                    }
                                                    ?>


                                                </optgroup>
                                                <optgroup id="__custom" label="None of these work?">
                                                    <option value="__custom"> Create a new option </option>
                                                </optgroup>
                                            </select>
                                            <?php
                                        }
                                    } else {

                                        if ($attributevalue3) {
                                            ?>

                                            <div class="input-group-body form-group">
                                                <div class="input-group-body">
                                                    <input type="text" class="form-control add_new_option_input" name="add_new_option_input" placeholder="Type Variations here">
                                                </div>
                                                <div class="input-group-btnn">
                                                    <button name="add-custom" class="btn btn-primary btn-append variation_optin_event" type="button" id="variation_optin_event">Add</button>
                                                </div>
                                            </div>       
                                            <?php
                                        }
                                    }
                                    ?>

                                </div>  
                                <div class="customFeildVariation">
                                </div>  
                                <div class="select-value varidationOptionValues" id="varidationOptionValuesthree"><span></span>


                                    <?php
                                    if (is_array($option3) && count($option3) > 0) {
                                        foreach ($option3 as $key => $value_op3_selected) {
                                            ?>
                                            <div class="variationOptionID">
                                                <input type="text" readonly class="form-control" value="<?php echo htmlspecialchars($value_op3_selected, ENT_QUOTES, 'UTF-8'); ?>">
                                                <a optionval="<?php echo strtolower($value_op3_selected); ?>" class="cross-icon"><i class="fa fa-times" aria-hidden="true"></i></a>
                                            </div>
                                            <?php
                                        }
                                    }
                                    ?>
                                </div>

                            </div><!-- selct-box-tags ui-toolkit -->

                        </div>
                    </div>
                    <div id="edit-variation-txt" ></div>
                </div>

                <div class="modal-footer">
                    <button type="button"
                    <?php
                    if ((is_array($option1) && count($option1) > 0) || is_array($option2) && count($option2) > 0 || is_array($option3) && count($option3) > 0) {
                       // echo 'disabled="disabled"';
                    }
                    ?>
                            class="btn btn-default btn-primary btn-simple "  data-dismiss="modal" id="btnvariationcheck">Update</button>

                </div>

            </div>
        </div>

    </div>

</div>
<script type="text/javascript">
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
                mainDivVariation.find('.customAttribute').append(inputField);
                break;
            default:

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
                mainDivVariation.find('.attributeCheckBoxText').text(variationValue);
                mainDivVariation.find('.show-on-select-box').show();
                mainDivVariation.find('.variation_attribute').val(variationValue);
                mainDivVariation.find('.results-text .color').text(variationValue);
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
                // console.log(aa);
                $('.VariationMainDiv').eq(aa).show();
                // aa.closest('.VariationMainDiv').show();
                // $('.VariationMainDiv').find("[value='" + variationValue + "']").hide();
                break;
        }
    });
    
 
    $(document).on('click', 'body *', function () {
        $(".numaric-only").on("keypress keyup blur", function (event) {
            //this.value = this.value.replace(/[^0-9\.]/g,'');
            $(this).val($(this).val().replace(/[^0-9\.\b]/g, '')); 
            if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
                event.preventDefault();
            }

        });
    });   
    
</script>
<style>
    .inside-variation{
    font-style: italic;
    font-weight: 600;
    }
</style>

<script>
    $(document).ready(function() {
        // For price field validation
        $('.vrpris').on('input', function() {
            var priceValue = $(this).val();
            // Check if the input exceeds 9999 or is negative
            if (priceValue.length > 4 || parseInt(priceValue) > 9999 || parseInt(priceValue) < 0) {
                $(this).addClass("is-invalid");

                // Revert to 9999 if the price exceeds 9999
                if (parseInt(priceValue) > 9999) {
                    $(this).val(9999).prop('readonly', true);
                } else if (parseInt(priceValue) < 0) {
                    // Revert to 0 if the value is negative
                    $(this).val(0).prop('readonly', true);
                }
            } else {
                // Remove error if the value is valid
                $(this).removeClass("is-invalid");
            }
        });

        // For stock field validation
        $('.vrstk').on('input', function() {
            var stockValue = $(this).val();
            // Check if the input exceeds 9999 or is negative
            if (stockValue.length > 4 || parseInt(stockValue) > 9999 || parseInt(stockValue) < 0) {
                $(this).addClass("is-invalid");

                // Revert to 9999 if the stock exceeds 9999
                if (parseInt(stockValue) > 9999) {
                    $(this).val(9999).prop('readonly', true);
                } else if (parseInt(stockValue) < 0) {
                    // Revert to 0 if the value is negative
                    $(this).val(0).prop('readonly', true);
                }
            } else {
                // Remove error if the value is valid
                $(this).removeClass("is-invalid");
            }
        });

        // Allow backspace to correct mistakes for both price and stock fields
        $('.vrpris, .vrstk').on('keydown', function(e) {
            if (e.keyCode === 8) {
                $(this).prop('readonly', false);
                $(this).removeClass("is-invalid");
            }
        });
    });
</script>
