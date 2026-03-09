<div class="row">
    <div class="col-md-4 lftexp">
        <h6>Price *</h6>
        <p>Factor in the costs of materials and labor, plus any related business expenses.<a href="javascript:;">Click To Know</a> </p>
    </div> 
    <?php
    $priceDisable = '';
    $quantityDisable = '';
    $skuDisable = '';
    $checkedPrice1 = isset($checkedPrice1) ? $checkedPrice1 : '';
    $checkedPrice2 = isset($checkedPrice2) ? $checkedPrice2 : '';
    $checkedQuantity1 = isset($checkedQuantity1) ? $checkedQuantity1 : '';
    $checkedQuantity2 = isset($checkedQuantity2) ? $checkedQuantity2 : '';
    $checkedSKU1 = isset($checkedSKU1) ? $checkedSKU1 : '';
    $checkedSKU2 = isset($checkedSKU2) ? $checkedSKU2 : '';
    $prodcutPrice = isset($prodcutDetails['price']) ? $prodcutDetails['price'] : '';
    $prodcutSalePrice = isset($prodcutDetails['salePrice']) ? $prodcutDetails['salePrice'] : '';
    $prodcutReceiveBackPrice = isset($prodcutDetails['receiveBackPrice']) ? $prodcutDetails['receiveBackPrice'] : '';

    $prodcutQuantity = isset($prodcutDetails['quantity']) ? $prodcutDetails['quantity'] : '';
    $prodcutSKU = isset($prodcutDetails['sku']) ? $prodcutDetails['sku'] : '';


    if (isset($variations) && count($variations) > 0) {

        if ($checkedPrice1 || $checkedPrice2) {
            $priceDisable = 'disabled="disabled"';
        }
        if ($checkedQuantity1 || $checkedQuantity2) {
            $quantityDisable = 'disabled="disabled"';
        }
        if ($checkedSKU1 || $checkedSKU2) {
            $skuDisable = 'disabled="disabled"';
        }
    }
    ?>
    <div class="col-md-8 rghtexp"> 
        <div class="row">

            <div class="col-sm-4 dollar-signadded">

                <input <?php echo $priceDisable; ?>  type="text" id="actualprice" name="actualprice" class="form-control getPriceFormate validatePrice" value="<?php echo $prodcutPrice; ?>" placeholder="Actual Price" onkeypress='Numvalidate(event, "zip", "actualprice")'  onkeyup='priceValidate("validatePrice")' maxlength="5"/> 

                <span class="price-dollar">$</span>
            </div> 
            <div class="col-sm-4 dollar-signadded">
                <input <?php echo $priceDisable; ?>  type="text" id="suggestedprice" name="suggestedprice" class="form-control getPriceFormate validatePrice" value="<?php echo $prodcutSalePrice; ?>"  placeholder="Suggested Retail" onkeypress='Numvalidate(event, "zip", "suggestedprice")'  maxlength="5" /> 
                <span class="price-dollar">$</span>
            </div>
            <div class="col-sm-4 dollar-signadded">
                <input type="text" id="receivelprice" name="receivelprice" class="form-control getPriceFormate validatePrice" value="<?php echo $prodcutReceiveBackPrice; ?>"  placeholder="Receive Back" onkeypress='Numvalidate(event, "zip", "receivelprice")' onkeyup='priceValidate("validatePrice")' maxlength="5" /> 
                <span class="price-dollar">$</span>
            </div>

        </div>
    </div>

    <div class="col-md-4 lftexp">
        <h6>Quantity  *</h6>
        <p>For quantities greater than one, this listing will renew automatically until it sells out. You’ll be charged a $0.20 USDC  listing fee each time.</p>
    </div> 
    <div class="col-md-8 rghtexp">
        <div class="row">
            <div class="col-sm-4">
                <input type="text" id="productQuantity" name="productQuantity"  <?php echo $quantityDisable; ?> onkeypress='Numvalidate(event, "zip", "productQuantity")'  maxlength="5" value="<?php echo $prodcutQuantity; ?>"  class="form-control"/>
            </div>
        </div>

    </div>

    <div class="col-md-4 lftexp">
        <h6>SKU  (Optional)</h6>
        <p>SKUs are for your use only—buyers won’t see them.<a href="javascript:;">Click To Know</a> </p>
    </div> 

    <div class="col-md-8 rghtexp">
        <div class="row">
            <div class="col-sm-4">

                <input type="text" id="productSKU"  <?php echo $skuDisable; ?>  name="productSKU" value="<?php echo $prodcutSKU; ?>" class="form-control" maxlength="20"/>
            </div>
        </div>

    </div>     
</div>   
<script>
jQuery('#actualprice').keyup(function(){
	val=jQuery(this).val();
	if(val==''){
		val=0;
	}
	if(val!=''){
		val=parseInt(val);
		var returnPrice = val/2;
				returnPrice=returnPrice.toFixed(2);
                jQuery('#receivelprice').val(returnPrice);
	}
});

jQuery('#receivelprice').keyup(function(){
	val=jQuery(this).val();
	if(val==''){
		val=0;
	}
	if(val!=''){
		val=parseInt(val);
		var returnPrice = val*2;
				returnPrice=returnPrice.toFixed(2);
                jQuery('#actualprice').val(returnPrice);
	}
});

    function priceValidate(_class) {
		return false;
        var mainPrice = '';
        var salePrice = '';
        var receivelprice = '';
        mainPrice = jQuery('body').find('.' + _class).eq(0).val();
        salePrice = jQuery('body').find('.' + _class).eq(1).val();
        receivelprice = jQuery('body').find('.' + _class).eq(2).val();
        if (!mainPrice) {
            mainPrice = 0;
        }
        if (!salePrice) {
            salePrice = 0;
        }
        if (!receivelprice) {
            receivelprice = 0;
        }

        console.log(mainPrice);
        console.log(salePrice);
        console.log(receivelprice);
        mainPrice = parseInt(mainPrice);
        salePrice = parseInt(salePrice);
        receivelprice = parseInt(receivelprice);
        if (mainPrice < salePrice) {
            if (mainPrice == salePrice) {
                jQuery('body').find('.' + _class).css('border-color', 'rgba(0, 128, 0, 0.39)');
                jQuery('body').find('.' + _class).removeClass('error-class')
            } else {
                jQuery('body').find('.' + _class).css('border-color', 'red');
                jQuery('body').find('.' + _class).addClass('error-class');
            }
            /// jQuery('body').find('.' + _class).eq(0).val(mainPrice.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
            //// jQuery('body').find('.' + _class).eq(1).val(salePrice.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
        } else {

            ///  jQuery('body').find('.' + _class).eq(0).val(mainPrice.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
            ////  jQuery('body').find('.' + _class).eq(1).val(salePrice.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
            jQuery('body').find('.' + _class).css('border-color', 'rgba(0, 128, 0, 0.39)');
            jQuery('body').find('.' + _class).removeClass('error-class')
//
        }

        //if (mainPrice) {
            if (receivelprice) {
                var returnPrice = receivelprice * 2;
				returnPrice=returnPrice.toFixed(2);
                jQuery('body').find('.' + _class).eq(0).val(returnPrice);
				return false;
			}
            if (mainPrice) {
                var returnPrice = mainPrice / 2;
				returnPrice=returnPrice.toFixed(2);
                jQuery('body').find('.' + _class).eq(2).val(returnPrice);
				return false;
            }
       // }

    }
    jQuery('body').on('blur', '.validatePrice', function (e) {

//        var mainPrice = jQuery('body').find('.validatePrice').eq(0).val();
//        var salePrice = jQuery('body').find('.validatePrice').eq(1).val();
//        console.log(mainPrice);
//        console.log(salePrice);
//        if (!mainPrice) {
//            mainPrice = 0;
//        }
//        if (!salePrice) {
//            salePrice = 0;
//        }
//
//        if ((mainPrice < salePrice) && (mainPrice != salePrice)) {
//            jQuery('body').find('.validatePrice').eq(0).val(mainPrice.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
//            jQuery('body').find('.validatePrice').eq(1).val(salePrice.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
//            jQuery('.validatePrice').css('border-color', 'red');
//            jQuery('body').find('.validatePrice').eq(1).addClass('error-class')
//        } else {
//            jQuery('body').find('.validatePrice').eq(0).val(mainPrice.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
//            jQuery('body').find('.validatePrice').eq(1).val(salePrice.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
//            jQuery('.validatePrice').css('border-color', 'rgba(0, 128, 0, 0.39)');
//            jQuery('body').find('.validatePrice').eq(1).removeClass('error-class')
//        }


    })

</script>