    

              jQuery(document).on('click', '.plus-icon', function() {
                
                var product_type = $(this).attr('data-product-type');
                var on_sale =  $(this).attr('data-on-sale');
                var product_price = parseFloat($(this).attr('data-price'));
                var product_price_sale = parseFloat($(this).attr('data-price-sale'));
                var cart_id = $(this).attr('data-cart-id');
                var data_maxquantity = $(this).attr('data-maxquantity_simple');
            
                var currentStock = 0;
                if (product_type == 'virtual') {
                    currentStock = 10;
                } else if(product_type == 'variant') {
                    currentStock = parseInt($(this).attr('data-maxquantity_simple'));
                
                } else {
                    currentStock = parseInt($(this).attr('data-maxquantity_simple'));
                }
              
                var plusVal = parseInt($(this).siblings(".quantity-input").val());
                plusVal++;
                var cartCount = 1;
            
                if (plusVal <= currentStock) {
                   if(plusVal <= cartCount){
                    $(this).siblings(".quantity-input").val(plusVal);
                    if (on_sale == 1){
                        
                        let totalprice = plusVal * product_price;
                        let totalpriceSale = plusVal * product_price_sale;
                        let discount_amount = totalprice - totalpriceSale;
                        $('.classtotal-' + cart_id).text('$' + totalpriceSale.toFixed(2));
                        $('.actualPrice-' + cart_id).text('$' + totalprice.toFixed(2));
                        $('.discount-amount-' + cart_id).text('$' + discount_amount.toFixed(2));

                    }else{
                        let totalprice = plusVal * product_price;
                        $('.classtotal-' + cart_id).text('$' + totalprice.toFixed(2));
                    }
                    var data = {};
                        data['product_id'] = $(this).attr('data-pro-id');
                        data['store_id'] = $(this).attr('data-stor-id');
                        data['variation_id'] = $(this).attr('variation-id');

                        data['product_quantity_plus'] = plusVal;
                      
                        $(".large-loader").show();
                            sendreq(data, 'cart', 'add', 'callbackBasketResponse');  // Send request for add to cart
                        // if (currentStock == plusVal) {
                        //     $(".large-loader").hide();
                        //     $(this).siblings(".quantity-input").val(currentStock);
                            
                        //     $(this).addClass('disabled');
    
                        //     $(this).parent().addClass('cntr-txt-fld');
                        // }
                   }else{
                    $(".large-loader").hide();
                    $(this).siblings(".quantity-input").val(cartCount);
                    
                    $(this).addClass('disabled');

                    $(this).parent().addClass('cntr-txt-fld');
                    toastr.options = {
                        closeButton: true, // Add close button
                        disableTimeOut: true,
                        tapToDismiss: true,
                        positionClass: 'toast-top-right',
                        toastClass: 'toast-icon custom-toast-success'
                        };
                    toastr.success('You have reached maximum quantity limit');
                   }
                
                } else {
                    $(".large-loader").hide();
                    $(this).siblings(".quantity-input").val(currentStock);
                    
                    $(this).addClass('disabled');

                    $(this).parent().addClass('cntr-txt-fld');
                    toastr.options = {
                        closeButton: true, // Add close button
                        disableTimeOut: true,
                        tapToDismiss: true,
                        positionClass: 'toast-top-right',
                        toastClass: 'toast-icon custom-toast-success'
                        };
                    toastr.success('You have reached maximum quantity limit');
                }
            });
                

    jQuery(document).on('click','.minus-icon',function(){    
        // $('.max-quantity-error').addClass('d-none');
        var minusVal =  $(this).siblings(".quantity-input").val();
        var cart_id = $(this).attr('data-cart-id');
        if (minusVal > 1) {
            
            minusVal--;
            var product_price = $(this).attr('data-price');
            var on_sale =  $(this).attr('data-on-sale');
            var product_price_sale = parseFloat($(this).attr('data-price-sale'));

            if (on_sale == 1){
                let totalprice = minusVal * product_price;
                let totalpriceSale = minusVal * product_price_sale;
                let discount_amount = totalprice - totalpriceSale;
                $('.classtotal-' + cart_id).text('$' + totalpriceSale.toFixed(2));
                $('.actualPrice-' + cart_id).text('$' + totalprice.toFixed(2));
                $('.discount-amount-' + cart_id).text('$' + discount_amount.toFixed(2));

            }else{
                let totalprice = minusVal * product_price;
                
                $('.classtotal-' + cart_id).text('$' + totalprice.toFixed(2));
            }
            // Calculate and display the total price of all cart products
            var cartTotal = 0;
            var discountTotal =0 ;
            $('.actualPrice').each(function() {
                var priceText = $(this).text().replace('$', '').replace(',', '');
                
                var priceValue = parseFloat(priceText);
                if (!isNaN(priceValue)) {
                    cartTotal += priceValue;
                }
            });
            $('.discount-amount').each(function() {
                var priceText = $(this).text().replace('$', '').replace(',', '');
                var priceValue = parseFloat(priceText);
                if (!isNaN(priceValue)) {
                    discountTotal += priceValue;
                }
            });
           
            $('.total-price-cart').text('$' + cartTotal.toFixed(2));
            $('.total-discount-cart').text('$' + discountTotal.toFixed(2));

            var data = {};

            data['product_id'] = $(this).attr('data-pro-id');
            data['store_id'] = $(this).attr('data-stor-id');
            data['variation_id'] = $(this).attr('variation-id');
            data['product_quantity_minus'] = minusVal;
            $(".large-loader").show();
            $(this).siblings(".plus-icon").removeClass('disabled');
            $(this).parent().removeClass('cntr-txt-fld');
            setTimeout(function () {
                sendreq(data, 'cart', 'add', 'callbackBasketResponseUpdate');
               }, 1000);
            $(this).siblings(".quantity-input").val(minusVal);
           
        } else {
            
            // $(this).closest('.show-quantitiy-panel').removeClass('show-quantitiy-panel');
           
            $(this).siblings(".plus-icon").removeClass('disabled');
            $(this).parent().removeClass('cntr-txt-fld');
            $(this).siblings(".quantity-input").val(1);
            
            currentDeletedItem = $(this).closest('.cart-item-table');
            currentContainer = $(this).closest('.main').find('.total-container');
            sideBar = $(this).closest('.main');
            var data = {};
            // data['product_id'] = $(this).attr('data-pro-id');
            data['cart_id'] = cart_id;
            // data['cart_id'] = $(this).attr('data-cart-id');
            var totalProductlenght = 0;
            //var result = confirm(" Are you sure want to remove this product from cart.?");
            swal({
                title: "Are you sure?",
                text: "Are you sure want to remove this product from cart?",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes, Do it!",
                closeOnConfirm: true
            },
                    function () {
                        $(".large-loader").show();
                        sendreq(data, 'cart', 'delete', 'delproducts');
                    });

        }
    });

$(document).on('click', '.cartadded', function () {
    
    $(this).parent().addClass("show-quantitiy-panel");
    currentBody = $(this).closest('.crtbtn');
    storeId = $(this).attr('data-store-id');
//        currentStock = currentBody.attr('data-stock');
    quantity = currentBody.find("input[type=text]").val();
    //jQuery('.uploadImages').show();
    var data = {};
    data['product_id'] = $(this).attr('data-product-id');
    if (storeId != "") {
        data['store_id'] = $(this).attr('data-store-id');
    }
    data['product_quantity'] = 1;

        let product_title = $(this).closest('.cartbxup').find('.product_title').data('title');
        let product_brand = $(this).closest('.cartbxup').find('.product_store').data('store');
        let product_price = $(this).closest('.cartbxup').find('.product_price').data('product-price');
        let product_parent_category = $(this).closest('.cartbxup').find('.product_parent_category').data('category');
        let product_child_category = $(this).closest('.cartbxup').find('.product_child_category').data('category');
        let product_stock = $(this).closest('.cartbxup').find('.product_stock').data('stock');
        let product_id = $(this).closest('.cartbxup').find('.p_id').data('product-id');
         if (product_parent_category === '') {
             product_parent_category = product_child_category;
             }
             

            dataLayer.push({ ecommerce: null });  
            dataLayer.push({
            event: "add_to_cart",
            ecommerce: {
            currency: "USD",
                    value: 7.77,
                    items: [
                        {
                        item_id: product_id,
                        item_name: product_title,
                        index: 0,
                        item_brand: product_brand,
                        item_category: product_parent_category,
                        item_list_id: "add_to_cart",
                        item_list_name: "Add to cart",
                        price: product_price,
                        quantity: product_stock
                        }
                    ]
                }
            });
    sendreq(data, 'cart', 'add', 'callbackBasketResponse');

});


////////////////////////////////////////////////////////////
$(document).on('click', '.quantity-minus', function () {
    $(".large-loader").show();
    var minusVal = $(this).next(".input-counter").find("input[type=text]").val();
    minusVal--;
    if (minusVal >= 1) {
        $(this).next(".input-counter").find("input[type=text]").val(minusVal);
        variationId = $(this).attr('data-variation-id');
        totalPrice = $(this).closest('.cart-item-table').find('.product-price');
        shipPrice = $(this).closest('.cart-item-table').find('.ship-price');
        currentContainer = $(this).closest('.main').find('.total-container');
        cartId = $(this).attr('data-cart-id');
        storeId = $(this).attr('data-store-id');
        data = {};
        if (variationId != "") {
            data['variation_id'] = variationId;
        }
        data['product_quantity'] = minusVal;
        data['cart_id'] = cartId;
        sendreq(data, 'cart', 'update', 'callBackupdatecart');
    } else {
        $(".large-loader").hide();
        $(this).addClass('disable-btn');
        return false;
    }
});
$(document).on('change', '.qty-add', function () {
    $(".large-loader").show();
    variationId = $(this).attr('data-variation-id');
    totalPrice = $(this).closest('.cart-item-table').find('.product-price');
    shipPrice = $(this).closest('.cart-item-table').find('.ship-price');
    currentContainer = $(this).closest('.main').find('.total-container');
    quantity = $(this).val();
    cartId = $(this).attr('data-cart-id');
    storeId = $(this).attr('data-store-id');
    data = {};
    if (variationId != "") {
        data['variation_id'] = variationId;
    }
    data['product_quantity'] = quantity;
    data['cart_id'] = cartId;
    sendreq(data, 'cart', 'update', 'callBackupdatecart');
});


$(document).on('click', '.quantity-add', function () {
    $(".large-loader").show();

    var plusVal = $(this).prev(".input-counter").find("input[type=text]").val();
    plusVal++;
    $(this).prev(".input-counter").find("input[type=text]").val(plusVal);
    variationId = $(this).attr('data-variation-id');
    totalPrice = $(this).closest('.cart-item-table').find('.product-price');
    shipPrice = $(this).closest('.cart-item-table').find('.ship-price');
    currentContainer = $(this).closest('.main').find('.total-container');
    cartId = $(this).attr('data-cart-id');
    storeId = $(this).attr('data-store-id');
    data = {};
    if (variationId != "") {
        data['variation_id'] = variationId;
    }
    data['product_quantity'] = plusVal;
    data['cart_id'] = cartId;
    sendreq(data, 'cart', 'update', 'callBackupdatecart');
});
$(document).on('click', '.remove-item', function () {

    // Remove cart item GTM Event
    const items = [];
    $('.cart-item-table').each(function () {
        let productId = $(this).data('product_id');
        let product_index = $(this).attr('data-index');
        let store = $(this).data('store_id');
        let item_name =  $(this).find('.cart-item-name').attr('data-item-name');
        let category = $(this).find('.cart-category').attr('data-category-name');
        let product_price = $(this).find('.product-cart-price').attr('data-price');
        let product_quantity = $(this).find('.quantity-input ').val();
        
        const item = {
            item_id: productId,
            item_name: item_name,
            item_category: category,
            index: product_index,
            price: parseFloat(product_price),
            quantity: parseInt(product_quantity),
            item_brand: store,
            item_category: category,
            item_list_id: "remove_from_cart",
            item_list_name: "Delete Cart Product",
        };
        Object.assign(items, item);
    });

    dataLayer.push({ ecommerce: null }); 
    dataLayer.push({
    event: "remove_from_cart",
        ecommerce: {
            currency: "USD",
            value: 7.77,
            items: items,
        }
    });

    currentDeletedItem = $(this).closest('.cart-item-table');
    // currentContainer = $(this).closest('.main').find('.total-container');
    sideBar = $(this).closest('.main');
    data = {};
    data['cart_id'] = $(this).attr('data-cart-id');
    var totalProductlenght = 0;
    
    //var result = confirm(" Are you sure want to remove this product from cart.?");
    swal({
        title: "Are you sure?",
        text: "Are you sure want to remove this product from cart?",
        type: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn-success",
        confirmButtonText: "Yes, Do it!",
        closeOnConfirm: false
    },
    
            function () {
                
                $(".large-loader").show();
                //jQuery('.uploadImages').show();



                $(".cart-item-table").each(function () {
                    totalProductlenght = totalProductlenght + $(this).length;
                });

                sendreq(data, 'cart', 'delete', 'delproducts');
                if (totalProductlenght <= 1) {
                    sideBar.remove();
                    $(".cart-class").html("No record Found");
                }


            });
    /*
     if (!result) {
     $(".large-loader").hide();
     return false;
     }
     */

});

// (function () {
//     // "use strict";
//     function Guantity($root) {
      
//       const element = $root;
//       const quantity = $root.first("data-quantity");
//       const quantity_target = $root.find("[data-quantity-target]");
//       const quantity_minus = $root.find("[data-quantity-minus]");
//       const quantity_plus = $root.find("[data-quantity-plus]");
//       var quantity_ = quantity_target.val();
  
//       $(quantity_minus).click(function () {
  
//           var minusVal = $(this).next(".input-counter").find("input[type=text]").val();
//           minusVal--;
//           if (minusVal >= 1) {
//               $(this).next(".input-counter").find("input[type=text]").val(minusVal);
//               variationId = $(this).attr('data-variation-id');
//               totalPrice = $(this).closest('.cart-item-table').find('.product-price');
//               shipPrice = $(this).closest('.cart-item-table').find('.ship-price');
//               currentContainer = $(this).closest('.main').find('.total-container');
//               cartId = $(this).attr('data-cart-id');
//               storeId = $(this).attr('data-store-id');
//               data = {};
//               if (variationId != "") {
//                   data['variation_id'] = variationId;
//               }
//               data['product_quantity'] = minusVal;
//               data['cart_id'] = cartId;
//               sendreq(data, 'cart', 'update', 'callBackupdatecart');
//           } else {
//               $(".large-loader").hide();
//               $(this).addClass('disable-btn');
//               return false;
//           }
  
  
//           if (quantity_ > 1) { 
//           quantity_target.val(--quantity_);
//         }
//       });
  
//       $(quantity_plus).click(function () {
        
          
          
//          let product_type =  $(this).attr('data-product-type');
  
//          let variable_maximum_quantity = $(this).attr('data-maxquantitylimit');
//          let variable_current_quantity = $(this).attr('data-current_stock');
//          let sproduct_maximum_quantity = $(this).attr('data-maxquantity_simple');
  
//          var currunt_stock = 0;
//          if (product_type === 'virtual') {
//           // For virtual products, keep the quantity unchanged (not increase or decrease)
//           return;
//         }
//         if(product_type === 'variant'){
  
//           if (variable_maximum_quantity == null || variable_maximum_quantity === '') {
//               // If max quantity is not defined, use current stock      
//               variable_current_quantity = variable_current_quantity;
//           }else if(variable_maximum_quantity == 0){
//               variable_current_quantity = 1;
//           } else if (variable_current_quantity > variable_maximum_quantity) {        
//               // If current stock is greater than max quantity, use max quantity
//               variable_current_quantity = variable_maximum_quantity;
//           }else if(variable_current_quantity < variable_maximum_quantity){
//               variable_current_quantity = variable_current_quantity;
//           }
  
//           if (quantity_ < variable_current_quantity) { 
//            quantity_target.val(++quantity_);
//            }
//         }else{
  
//           if (quantity_ < sproduct_maximum_quantity) { 
//           quantity_target.val(++quantity_);
//            }
//         }
//       });
//     }
  
//     $.fn.Guantity = jQueryPlugin("Guantity", Guantity);
//     $("[data-quantity]").Guantity();
//   })();

$(document).on('click', '.remove-item-crypto', function () {

    currentDeletedItem = $(this).closest('.cart-item-table');
    currentContainer = $(this).closest('.main').find('.total-container');
    sideBar = $(this).closest('.main');
    data = {};
    data['cart_id'] = $(this).attr('data-cart-id');
    var totalProductlenght = 0;
    //var result = confirm(" Are you sure want to remove this product from cart.?");
    swal({
        title: "Are you sure?",
        text: "Are you sure want to remove this product from cart?",
        type: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn-success",
        confirmButtonText: "Yes, Do it!",
        closeOnConfirm: false
    },
            function () {
                $(".large-loader").show();
                //jQuery('.uploadImages').show();



                $(".cart-item-table").each(function () {
                    totalProductlenght = totalProductlenght + $(this).length;
                });

                sendreq(data, 'cart', 'deletecrypto', 'delproducts');
                if (totalProductlenght <= 1) {
                    sideBar.remove();
                    $(".cart-class").html("No record Found");
                }


            });
    /*
     if (!result) {
     $(".large-loader").hide();
     return false;
     }
     */

});



function callBackDelBasket(data) {
    if(data.status){
        var cart_id = data.cart_id;
        $(".product-cart_id-" + cart_id).fadeOut(400, function() {
            $(this).remove();
            var cartTotal = 0;
            $('.classtotal').each(function() {
                var priceText = $(this).text().replace('$', '').replace(',', '');
                var priceValue = parseFloat(priceText);
                console.log("priceText",priceText);
                console.log("priceValue",priceValue);
                if (!isNaN(priceValue)) {
                    cartTotal += priceValue;
                }
            });
            $('.total-price-cart').text('$' + cartTotal.toFixed(2));
        });
        
        // $(".product-cart_id-"+data.cart_id).remove();
            toastr.options = {
            closeButton: true, // Add close button
            disableTimeOut: true,
            tapToDismiss: true,
            positionClass: 'toast-top-right',
            toastClass: 'toast-icon custom-toast-success'
            };
        toastr.error(data.message);
        $(".cartmenu").html(data.view);
        $("#cart_counter_ajax").html(data.cartCounter);
    }

}

function callbackBasketResponse(data) {
    
    if(data.status){
        setTimeout(function () {
            
            $(".large-loader").hide();
        }, 1500);
        // Calculate and display the total price of all cart products
        let cartTotal = 0;
        let discountTotal = 0;  
        let discountentPrice = 0;          
        $('.discount-amount').each(function() {
            var priceText = $(this).text().replace('$', '').replace(',', '');
            
            var priceValue = parseFloat(priceText);
           
            if (!isNaN(priceValue)) {
                discountTotal += priceValue;
            }
        });
        $('.actualPrice').each(function() {
            var priceText = $(this).text().replace('$', '').replace(',', '');
            
            var priceValue = parseFloat(priceText);
           
            if (!isNaN(priceValue)) {
                cartTotal += priceValue;
            }
        });
        
        $('.total-price-cart').text('$' + cartTotal.toFixed(2));
        $('.total-discount-cart').text('$' + discountTotal.toFixed(2));
        $(".cartmenu").html(data.view);
        $("#cart_counter_ajax").html(data.cartCounter);
            toastr.options = {
            closeButton: true, // Add close button
            disableTimeOut: true,
            tapToDismiss: true,
            positionClass: 'toast-top-right',
            toastClass: 'toast-icon custom-toast-success'
            };
        toastr.success(data.message);
    
        setTimeout(function() {
            $(".cart-list-nav").removeClass("bounce-up");
            $("#cart_counter_ajax").removeClass("bounce-up");
        }, 200);
    }else{
        $(".large-loader").hide();
        $(".cartmenu").html(data.view);
        $("#cart_counter_ajax").html(data.cartCounter);
        toastr.options = {
            closeButton: true, // Add close button
            disableTimeOut: true,
            tapToDismiss: true,
            positionClass: 'toast-top-right',
            toastClass: 'toast-icon custom-toast-success'
            };
        toastr.error(data.message);
    
        setTimeout(function() {
            $(".cart-list-nav").removeClass("bounce-up");
            $("#cart_counter_ajax").removeClass("bounce-up");
        }, 200);
    }
}
function callbackBasketResponseUpdate(data) {

    if(data.status){
        $(".large-loader").hide();
        $(".cartmenu").html(data.view);
        $("#cart_counter_ajax").html(data.cartCounter);
        toastr.options = {
            closeButton: true, // Add close button
            disableTimeOut: true,
            tapToDismiss: true,
            positionClass: 'toast-top-right',
            toastClass: 'toast-icon custom-toast-success'
            };
        toastr.success("Product Cart Updated");

    }else{
        $(".large-loader").hide();
        $(".cartmenu").html(data.view);

        $("#cart_counter_ajax").html(data.cartCounter);
        toastr.options = {
            closeButton: true, // Add close button
            disableTimeOut: true,
            tapToDismiss: true,
            positionClass: 'toast-top-right',
            toastClass: 'toast-icon custom-toast-success'
            };
        toastr.error(data.message);
    }
}

function callbackfavourites(data) {
    current.toggleClass('fillheart');
}
function callBackupdatecart(data) {
    res = {};
    totalPrice.html(data['total_price']);
    shipPrice.html(data['ship_price_preproduct']);
    res['store_id'] = storeId;

    sendreq(res, 'cart', 'calculate', 'calculateproducts');
}
function calculateproducts(data) {
    $(".total-price-cart").html(data.totalProduct_amount);
    $(".total-discount-cart").html(data.discount_amount);
    $(".large-loader").hide();
    if(data.totalstoreproducts < 1){
        if(data?.redirect_url){
            window.location.href = data.redirect_url
        }
    }

    // updateBasket();
}
function updateBasket() {
    res = {};
    res['cuneter'] = 1;
    sendreq(res, 'cart', 'updatebasket', 'callBackupdatebasket');
}
function callBackupdatebasket(data) {
    $(".cartmenu").html(data.view);
    $("#cart_counter_ajax").html(data.cartCounter);

    $(".large-loader").hide();
}
function delproducts(data) {
    $("#cart_counter_ajax").html(data);
    
    res = {};
    res['store_id'] = 11;
    sendreq(res, 'cart', 'calculate', 'calculateproducts');
    currentDeletedItem.remove();
    swal("Success!", "Done.", "success");


}
jQuery(document).on('click', '#resetfilters', function () {
    var uri = window.location.toString();
    if (uri.indexOf("?") > 0) {
        var clean_uri = uri.substring(0, uri.indexOf("?"));
        window.history.pushState({}, document.title, clean_uri);
    }
    jQuery('#search-filters')[0].reset();
    jQuery('#recent-addition').prop('checked', false);
    jQuery('#supported-by').prop('checked', false);
    jQuery('#search-filters').find(":input[type=number],select,input[type=hidden]").filter(function () {
        jQuery(this).val('');
    });


    return true;

});
