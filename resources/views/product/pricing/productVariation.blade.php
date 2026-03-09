<?php
$attribute1 = isset($attribute1) ? $attribute1 : '';
$attribute2 = isset($attribute2) ? $attribute2 : '';

$option1 = isset($option1) ? $option1 : '';
$variationAttributeone = isset($variationAttributeone) ? $variationAttributeone : '';
$variationAttributetwo = isset($variationAttributetwo) ? $variationAttributetwo : '';
$variationAttributeUnitone = isset($variationAttributeUnitone) ? $variationAttributeUnitone : '';
$variationAttributeUnittwo = isset($variationAttributeUnittwo) ? $variationAttributeUnittwo : '';

$pricevalidator1 = isset($pricevalidator1) ? $pricevalidator1 : false;
$pricevalidator2 = isset($pricevalidator2) ? $pricevalidator2 : false;
$quantityvalidator1 = isset($quantityvalidator1) ? $quantityvalidator1 : false;
$quantityvalidator2 = isset($quantityvalidator2) ? $quantityvalidator2 : false;
$skuvalidator1 = isset($skuvalidator1) ? $skuvalidator1 : false;
$skuvalidator2 = isset($skuvalidator2) ? $skuvalidator2 : false;
?>


<table class="table tblvrnt border" >
    <thead class="thead-light">
        <tr id="tableHeadvaridation">
            <th scope="col"></th>
            <th scope="col clm2">Options</th>
            <?php if ($pricevalidator1 != 'false') { ?>
                <th scope="col">Actual Price</th>
                <th scope="col">Suggested Retail</th>
                <th scope="col">Receive Back</th>
            <?php } if ($quantityvalidator1 != 'false') { ?>

                <th scope="col">Quantity</th>
            <?php } if ($skuvalidator1 != 'false') { ?>

                <th scope="col">SKU</th>
            <?php } ?>
            <th scope="col">Visible</th>
        </tr>
    </thead>
    <tbody id="tablevaridation">

        <?php
        $INCREAMENT_COUNTER = 0;

        foreach ($option1 as $key => $value) {
            $counterHTML = rand(2332, 999999);
            ?>

            <tr class="variation_tr">

                <td style="visibility : visible"> 
                    <input type="checkbox" id="del_<?php echo $counterHTML; ?>" >
                    <label for="del_<?php echo $counterHTML; ?>"></label>
                    <input name="ProductvariationData[attribute1][]" value="<?php echo $variationAttributeone; ?>" type="hidden">
                    <input name="ProductvariationData[attribute2][]" value="<?php echo $variationAttributetwo; ?>" type="hidden">
                    <input name="ProductvariationData[unit1][]" value="<?php echo $variationAttributeUnitone; ?>" type="hidden">
                    <input name="ProductvariationData[unit2][]" value="<?php echo $variationAttributeUnittwo; ?>" type="hidden">
                    <input name="ProductvariationData[option1][]" value="<?php echo $value; ?>" type="hidden">
                    <input name="ProductvariationData[option2][]" value="" type="hidden">
                    <input name="ProductvariationData[product_variation_image][]" value="" type="hidden">
                </td>

                <td class="clm2"><?php echo $value; ?></td>
                <?php if ($pricevalidator1 != 'false') { ?>
                    <td><input name="ProductvariationData[price][]"  type="text"  onkeypress='Numvalidate(event, "price", "actualPrice_<?php echo $counterHTML; ?>")'  onkeyup='priceValidate("actualPrice_<?php echo $counterHTML; ?>")'  class="required-field form-control keypressNumValidate actualPrice_<?php echo $counterHTML; ?>" id="actualPrice_<?php echo $counterHTML; ?>"></td>
                    <td><input name="ProductvariationData[salePrice][]" type="text" onkeypress='Numvalidate(event, "price", "salePrice_<?php echo $counterHTML; ?>")'  onkeyup='priceValidate("salePrice_<?php echo $counterHTML; ?>")'  class="form-control required-field salePrice_<?php echo $counterHTML; ?>" id="salePrice_<?php echo $counterHTML; ?>"></td>
                    <td><input  name="ProductvariationData[receiveBackPrice][]" type="text" class="form-control required-field priceValue_<?php echo $counterHTML; ?>" onkeypress='Numvalidate(event, "price", "retrunPrice_<?php echo $counterHTML; ?>")'  onkeyup='priceValidate("priceValue_<?php echo $counterHTML; ?>")'  id="retrunPrice_<?php echo $counterHTML; ?>"></td>
                <?php } else { ?>
            <input name="ProductvariationData[price][]" type="hidden" class=" form-control" id="actualPrice_<?php echo $counterHTML; ?>">
            <input name="ProductvariationData[salePrice][]" type="hidden" class="form-control " id="salePrice_<?php echo $counterHTML; ?>">
            <input  readonly="" name="ProductvariationData[receiveBackPrice][]" type="hidden" class="form-control" id="retrunPrice_<?php echo $counterHTML; ?>">
            <?php
        }
        if ($quantityvalidator1 != 'false') {
            ?>
            <td><input name="ProductvariationData[quantity][]" type="text" class="form-control required-field" id="quantity_<?php echo $counterHTML; ?>"></td>
        <?php } else {
            ?>
            <input name="ProductvariationData[quantity][]" type="hidden" class="form-control" id="quantity<?php echo $counterHTML; ?>">
            <?php
        }
        if ($skuvalidator1 != 'false') {
            ?>
            <td><input name="ProductvariationData[sku][]" type="text" class="form-control" id="sku<?php echo $counterHTML; ?>"></td>
        <?php } else { ?>
            <input name="ProductvariationData[sku][]" type="hidden" class="form-control" id="sku<?php echo $counterHTML; ?>">
        <?php } ?>
        <input name="ProductvariationData[checkboxPrice1][]" value="<?php echo $pricevalidator1; ?>" type="hidden">
        <input name="ProductvariationData[checkboxPrice2][]" value="<?php echo $pricevalidator2; ?>" type="hidden">
        <input name="ProductvariationData[checkboxQuantity1][]" value="<?php echo $quantityvalidator1; ?>" type="hidden">
        <input name="ProductvariationData[checkboxQuantity2][]" value="<?php echo $quantityvalidator2; ?>" type="hidden">
        <input name="ProductvariationData[checkboxSKU1][]" value="<?php echo $skuvalidator1; ?>" type="hidden">
        <input name="ProductvariationData[checkboxSKU2][]" value="<?php echo $skuvalidator2; ?>" type="hidden">
        <td><input type="checkbox" id="visibility_<?php echo $counterHTML; ?>" value="1" checked="" name="ProductvariationData[visibility][<?php echo $INCREAMENT_COUNTER; ?>']">
            <label for="visibility_<?php echo $counterHTML; ?>">
            </label>
        </td>
    </tr>
    <?php
}
?>

<?php $INCREAMENT_COUNTER++; ?>

</tbody>
</table>