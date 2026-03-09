<?php
// print_r($postedData);
// exit; 
?>

<?php if (!empty($data)) { ?>
    <table class="table tblvrnt border" >
        <thead class="thead-light">
            <tr id="tableHeadvaridation">
                <?php if (isset($postedData['variationAttributeone'])) { ?>
                    <th scope="col"><?php echo ucfirst($postedData['variationAttributeone']); ?><?php echo isset($postedData['variationAttributeUnitone'])?"(".$postedData['variationAttributeUnitone'].")":"";?></th>
                <?php } ?>
                <?php if (isset($postedData['variationAttributetwo'])) { ?>
                    <th scope="col"><?php echo ucfirst($postedData['variationAttributetwo']); ?><?php echo isset($postedData['variationAttributeUnittwo'])?" (".$postedData['variationAttributeUnittwo'].")":"";?></th>
                <?php } ?>
                <?php if (isset($postedData['variationAttributethree'])) { ?>
                    <th scope="col"><?php echo ucfirst($postedData['variationAttributethree']); ?><?php echo isset($postedData['variationAttributeUnitthree'])?" (".$postedData['variationAttributeUnitthree'].")":"";?></th>
                <?php } ?>
                <th scope="col">Price</th>
                <th scope="col">SKU</th>
                <th scope="col">Current Stock</th>
                <!-- <th scope="col" class="text-center">Available</th> -->
                <th scope="col" class="text-center">Associate Image</th>
                <th scope="col" class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody>

            <?php
            $counter=0;
            foreach ($data['variation_options'] as $key => $dv) {
                // dd($dv);
                ?>
                <tr class="variation_tr">
                    <?php
                    if (is_array($dv)) {
                        for ($i = 0; $i < count($dv); $i++) {
                            ?>
                            <td class="clm2">
                            
                                <?php echo ucfirst($dv[$i]); ?>
                                <input name="ProductvariationData[option<?php echo $i + 1; ?>][]" value="<?php echo htmlspecialchars($dv[$i], ENT_QUOTES, 'UTF-8'); ?>" type="hidden">
                            </td>
                            <?php
                        }
                    } else {
                       // echo '<span class="in-onee-case">in one case</span>';
                        ?>
                        <td class="clm2">
                            <?php echo ucfirst($data['variation_options'][$key]); ?>
                            <input name="ProductvariationData[option1][]" value="<?php echo htmlspecialchars($data['variation_options'][$key], ENT_QUOTES, 'UTF-8'); ?>" type="hidden">
                          

                        </td>
                        <?php
                    }
                    //echo $variationData[$counter]['option1'];
                    ?>
                    <td>
                        <input value="<?php echo isset($variationData[$counter]['price']) ? $variationData[$counter]['price'] : (isset($postedData['variation_price'][$counter]) ? $postedData['variation_price'][$counter]:''); ?>" name="ProductvariationData[price][]" onkeypress="return isNumber(event)"  type="text" class="priceField vrpris required-field form-control" id="#"></td>
                    <td>
                        <input value="<?php echo isset($variationData[$counter]['sku'])?$variationData[$counter]['sku']: (isset($postedData['variation_sku'][$counter]) ? $postedData['variation_sku'][$counter]:''); ?>" name="ProductvariationData[sku][]"  type="text" class="priceField vrsku form-control" id="#"></td>
                    <td>
                        <input value="<?php echo isset($postedData['variation_stock'][$counter]) ? $postedData['variation_stock'][$counter]:""; ?>" name="ProductvariationData[current_stock][]" onkeypress="return isNumber(event)" type="text" class="priceField required-field form-control vrstk" id="#" max="100000" max_length="6" ></td>
                        <input value="<?php echo isset($variationData[$counter]['sales']) ? $variationData[$counter]['sales']:''; ?>" name="ProductvariationData[sales][]"  type="hidden" class="priceField  form-control" id="#"></td>
                        <input value="<?php echo isset($variationData[$counter]['variation_id']) ? $variationData[$counter]['variation_id']:''; ?>" name="ProductvariationData[variationID][]" type="hidden" class="priceField form-control" id="#"></td>
                    <!-- <td class="text-center">
                        <label class="form-check-label container-check">                            
                            <input type="checkbox" id="visibility" class="variation-visible"  value="<?php //echo $key+1; ?>"  checked="" name="ProductvariationData[visibility][]">
                            <span class="checkmark"></span>
                         
                        </label>
                    </td> -->
                    <?php 
                    // dump('', $counter); 
                    ?>
                    <td class="text-center">
                        <label class="form-check-label">                            
                            <a class="{{ $edit == true ? 'showImagePopupdata'  :  'showImagePopup' }} variationImageIcon"  <?php if ($edit == true) echo 'data-product-id="' . $product_id . '"'; ?> id="showProductImage_<?php echo $key; ?>" data-variationkey="<?php echo $key; ?>"
                             data-value="<?php echo isset($postedData['variation_images'][$counter]) ? $postedData['variation_images'][$counter]: (isset($postedData['edit_variation_images'][$counter]) ? $postedData['edit_variation_images'][$counter]: '') ; ?>" 
                             data-id="showProductImage_<?php echo $key; ?>"
                              <?php if ($edit == true && isset($postedData['edit_variation_images'][$counter])) echo 'data-variationimage-id="' . $postedData['edit_variation_images'][$counter] . '"'; ?> style="pointer-events:none;opacity:0.5"> <!-- Add disabled attribute here -->

                              <i class="material-icons">image</i> <!-- Add disabled attribute here --></a>
                                <input name="ProductvariationData[product_variation_image][]" class="{{ $edit == true ? 'product_variation_image_specialcase'  :  'product_variation_image' }}"  value="<?php echo isset($postedData['variation_images'][$counter]) ? $postedData['variation_images'][$counter]: (isset($postedData['edit_variation_images'][$counter]) ? $postedData['edit_variation_images'][$counter]: ''); ?>" type="hidden">
                         
                        </label>
                    </td>
                    <td class="text-center">
                        <label class="form-check-label">                            
                            <a href="#" class="remove-variation">
                                <i class="material-icons">edit delete</i></a>
                            
                         
                        </label>
                    </td>

                </tr>
                <?php
                $counter++;
            }
            if (is_array($dv)) {
                ?>
            <input name="ProductvariationData[attribute1][]" value="<?php echo $posted = isset($postedData['variationAttributeone']) ? $postedData['variationAttributeone'] : '' ?>" type="hidden">
            <input name="ProductvariationData[attribute2][]" value="<?php echo $posted2 = isset($postedData['variationAttributetwo']) ? $postedData['variationAttributetwo'] : '' ?>" type="hidden">
            <input name="ProductvariationData[attribute3][]" value="<?php echo $posted3 = isset($postedData['variationAttributethree']) ? $postedData['variationAttributethree'] : '' ?>" type="hidden">
            <input name="ProductvariationData[unit1][]" value="<?php echo $unit1 = isset($postedData['variationAttributeUnitone']) ? $postedData['variationAttributeUnitone'] : ''; ?>" type="hidden">
            <input name="ProductvariationData[unit2][]" value="<?php echo $unit2 = isset($postedData['variationAttributeUnittwo']) ? $postedData['variationAttributeUnittwo'] : ''; ?>" type="hidden">
            <input name="ProductvariationData[unit3][]" value="<?php echo $unit3 = isset($postedData['variationAttributeUnitthree']) ? $postedData['variationAttributeUnitthree'] : ''; ?>" type="hidden">
            <input name="ProductvariationData[sales][]" value="<?php echo $sales = isset($postedData['variationAttributeUnitthree']) ? $postedData['variationAttributeUnitthree'] : ''; ?>" type="hidden">
            <!-- <input name="ProductvariationData[product_variation_image][]" value="<?php echo $product_variation_image = isset($postedData['product_variation_image']) ? $postedData['product_variation_image'] : ''; ?>" type="hidden"> -->
            <?php
        } else {
            $posted = isset($postedData['variationAttributeone']) ? $postedData['variationAttributeone'] : '';
            $posted2 = isset($postedData['variationAttributetwo']) ? $postedData['variationAttributetwo'] : '';
            $posted3 = isset($postedData['variationAttributethree']) ? $postedData['variationAttributethree'] : '';
            $attr = null;
            if ($posted) {
                $attr = $posted;
            } else if ($posted2) {
                $attr = $posted2;
            } else {
                $attr = $posted3;
            }
            ?>
            <input name="ProductvariationData[attribute1][]" value="<?php echo $attr; ?>" type="hidden">
            <input name="ProductvariationData[attribute2][]" value="" type="hidden">
            <input name="ProductvariationData[attribute3][]" value="" type="hidden">
            <?php
            $unit1 = isset($postedData['variationAttributeUnitone']) ? $postedData['variationAttributeUnitone'] : '';
            $unit2 = isset($postedData['variationAttributeUnittwo']) ? $postedData['variationAttributeUnittwo'] : '';
            $unit3 = isset($postedData['variationAttributeUnitthree']) ? $postedData['variationAttributeUnitthree'] : '';
            $unit = null;
            if ($unit1) {
                $unit = $unit1;
            } else if ($unit2) {
                $unit = $unit2;
            } else {
                $unit = $unit3;
            }
            ?>
            <input name="ProductvariationData[unit1][]" value="<?php echo $unit; ?>" type="hidden">
            <input name="ProductvariationData[unit2][]" value="" type="hidden">
            <input name="ProductvariationData[unit3][]" value="" type="hidden">
            <?php
        }
        ?></tbody>

    </table>
<?php } ?>

<script>
    $(document).ready(function() {
    // Your code to be executed when the DOM is ready goes here
    var totalImages = jQuery('#all-uplpad-image .image-panel').length;
                // console.log(totalImages);
                // debugger;
                if (totalImages <= 0) {
                    
                    // Disable the button
                    // jQuery('.btn-primary-back.selectFeatureImageAdd').attr('disabled', 'disabled');
                    jQuery('.variationImageIcon').css({
                        'pointer-events' : 'none',
                        'opacity' : '0.5'
                    });
                    
                } else {
                    // debugger;
                    // Enable the button
                    // jQuery('.btn-primary-back.selectFeatureImageAdd').removeAttr('disabled');
                    jQuery('.variationImageIcon').css({
                        'pointer-events' : 'auto',
                        'opacity' : ''
                    });
                    
                }
});
   
</script>
<script>
    $(document).ready(function() {
        // Disable image icon if no images are uploaded
        var totalImages = jQuery('#all-uplpad-image .image-panel').length;
        if (totalImages <= 0) {
            jQuery('.variationImageIcon').css({
                'pointer-events': 'none',
                'opacity': '0.5'
            });
        } else {
            jQuery('.variationImageIcon').css({
                'pointer-events': 'auto',
                'opacity': ''
            });
        }
    
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
    

