    
        // jQuery(document).on('click','.plus-icon',function(){
            $(".plus-icon").click(function(){
         
                    var currentStock = $(this).closest('.card-body').attr('data-stock');
                    var plusVal = $(this).prev(".input-counter").find("input[type=text]").val();
            
                    plusVal++;
                    if (currentStock >= plusVal) {
                        var data = {};
                        data['product_id'] = $(this).attr('data-pro-id');
                        data['store_id'] = $(this).attr('data-stor-id');
                        data['product_quantity_plus'] = plusVal;
                        sendreq(data, 'cart', 'add', 'callbackBasket');
                        $(this).prev(".input-counter").find("input[type=text]").val(plusVal);
                        if (currentStock == plusVal) {
                            $(this).addClass('disabled');
                            $(this).parent().addClass('cntr-txt-fld');
                        }
                    } else {
                        $(this).addClass('disabled');
                        $(this).parent().addClass('cntr-txt-fld');
                    }
                });
            
            // jQuery(document).on('click','.minus-icon',function(){     
                $(".minus-icon").click(function(){   
                    var minusVal = $(this).next(".input-counter").find("input[type=text]").val();
                    if (minusVal > 1) {
                        minusVal--;
                        var data = {};
                        data['product_id'] = $(this).attr('data-pro-id');
                        data['store_id'] = $(this).attr('data-stor-id');
                        data['product_quantity_minus'] = minusVal;
                        sendreq(data, 'cart', 'add', 'callbackBasket');
                        $(this).next(".input-counter").find("input[type=text]").val(minusVal);
                        $(this).closest(".card-body").find(".plus-icon").removeClass('disabled');
                        $(this).parent().removeClass('cntr-txt-fld');
            
                    } else {
                        $(this).closest('.show-quantitiy-panel').removeClass('show-quantitiy-panel');
                        $(this).closest(".card-body").find(".plus-icon").removeClass('disabled');
                        $(this).parent().removeClass('cntr-txt-fld');
                        $(this).next(".input-counter").find("input[type=text]").val(1);
                        var data = {};
                        data['product_id'] = $(this).attr('data-pro-id');
                        sendreq(data, 'cart', 'deletebasket', 'callBackDelBasket');
            
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
                sendreq(data, 'cart', 'add', 'callbackBasket');
            
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
            
            
            
            function callBackDelBasket(data) {
                $(".cartmenu").html(data);
            }
            function callbackBasket(data) {
                $(".cartmenu").html(data);
                $(".cart-list-nav").addClass("bounce-up");
                setTimeout(function () {
                    $(".cart-list-nav").removeClass("bounce-up");
                }, 200);
                // $("html, body").animate({scrollTop: 0}, "slow");
                //jQuery('.uploadImages').hide();
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
                currentContainer.html(data);
                updateBasket();
            }
            function updateBasket() {
                res = {};
                res['cuneter'] = 1;
                sendreq(res, 'cart', 'updatebasket', 'callBackupdatebasket');
            }
            function callBackupdatebasket(data) {
                $(".cartmenu").html(data);
                $(".large-loader").hide();
            }
            function delproducts(data) {
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
                console.log('clean_uri',clean_uri);
                //jQuery('.search-filters')[0].reset();
                jQuery('#recent-addition').prop('checked', false);
                jQuery('#supported-by').prop('checked', false);
                jQuery('#productMainCategory').prop('checked', false);
                jQuery('.search-filters').find(":input[type=number],select,input[type=hidden]").filter(function () {
                    jQuery(this).val('');
                });
                location.reload();
            
                return true;
            
            });
            
               