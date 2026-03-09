<!--Footer Starts -->
<style>
    .jssocials-share-link {
        border-radius: 50% !important;
    }
    #accept-cookies{
        display: none;
    }
</style>
</div>


<footer id="footer" class="footer">
    <div class="payment-method-footer">
        <div class="container">
            <div class="row justify-content-between align-items-center">
                <div class="col-sm-4">
                    <div class="payment-text d-flex align-items-center">
                        <span class="txt-p">Payment Methods:</span>
                        <span class="pt-p d-flex align-items-center">
                            {{-- <img class="paypal" src="{{ URL::asset('assets/images/paypal.svg')}}" alt="payments">    --}}
                            <img class="visaPay" src="{{ URL::asset('assets/images/visa_icon.svg')}}" alt="payments" >   
                            <img class="paymentPay" src="{{ URL::asset('assets/images/payment_icon.svg')}}" alt="payments">   
                            <img class="googlePay" src="{{ URL::asset('assets/images/google-pay.svg')}}" alt="payments">   
                            <img class="applePay" src="{{ URL::asset('assets/images/apple-pay.svg')}}" alt="payments">   
                        </span>                        
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="googleDownload row justify-content-end">
                        <div class="col-end">
                            <div class="payment-text d-flex align-items-center">
                                <span class="txt-p">Download App:</span>
                                <div >
                                    <ul class="pt-p d-flex align-items-center">
                                    <li><a target="_blank" href="https://play.google.com/store/apps/details?id=com.mallsharkmobile&hl=en"><img src="{{ URL::asset('assets/images//google-play.svg')}}" alt="Google Play"> &nbsp;</a></li>
                                    <li><a target="_blank" href="https://apps.apple.com/pk/app/mallshark/id6479016187"><img src="{{ URL::asset('assets/images/app-store.svg')}}" alt="App Store"></a></li>
                                    </ul>
                                </div>                        
                            </div>                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<div class="footer-content">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between footerRow1">
            <div class="fLogo">
                <a class="navbar-brand nav-logo" href="{{ URL::to('/') }}">
                     <img src=" {{ asset('assets/images/MS-Logo.png', env('HTTPS_STATUS')) }}" alt="MallShark logo" title="MallShark logo" style="max-width: 120px">  
                </a>
            </div>

            <div class="row align-items-center d-flex hiddenMobile">
                <h6 style="margin: 0;padding-right:10px"><b>Follow Us</b></h6>
                <div class="followUs" style="margin-top: 0;">
                    <a class="icon fb-icon" target="_blank" href="https://web.facebook.com/mallsharkofficial"><i class="fa fa-facebook"></i></a>
                    <a target="_blank" href="https://twitter.com/ShopMallShark" class="icon t-icon"><i class="fa fa-twitter"></i></a>
                    <a target="_blank" href="https://www.instagram.com/mallsharkofficial" class="icon ins-icon"><i class="fa fa fa-instagram"></i></a>
                    <a target="_blank" href="https://discord.gg/hW9DZapKyH" class="icon dis-icon"><img width="50" src="{{URL::asset('/assets/images/discord.svg')}}"></a>
                </div>
            </div>


        </div>


        <div class="row">


            <div class="singsUpInfo col-6 col-md-2">
                <h6><b>My Account</b></h6>
                    <ul>
                        <?php if (!Session::has('userData')) { ?>
                            <li><a href="{{URL::to('/signup')}}">Sign Up</a></li>
                        <?php } else { ?>
                            <li><a href="{{URL::to('/login')}}">Login</a></li>
                        <?php } ?>
                        
                        <?php if (Session::has('userData')) {
                            $userid = isset(Session::get('userData')['user_id']) ? Session::get('userData')['user_id'] : "";
                            $storeData = getStoreDataByUserId($userid);
                            if (!empty($storeData)) {  ?>
                                <li><a href="{{URL::to('/store-dashboard')}}">Become a Seller</a></li>
                            <?php
                            } else { ?>
                                <li><a href="{{URL::to('/sellonmallshark')}}">Become a Seller</a></li>
                            <?php  }
                        } else { ?>
                            <li><a href="{{URL::to('/sellonmallshark')}}">Become a Seller</a></li>
                        <?php } ?>
                    </ul>
            </div>


        
            <div class="col-md-2 col-6 faqs cmn">
                <h6><b>MallShark FAQs</b></h6>
                <ul>
                <li><a href="<?php echo URL::to('/generalfaq'); ?>">General</a></li>
                    <li><a href="<?php echo URL::to('/buyerfaq'); ?>">Buyer</a></li>
                    <li><a href="<?php echo URL::to('/sellerfaq'); ?>">Seller</a></li>
                    
                </ul>
            </div>
            <div class="col-md-2 col-6 company cmn">
                <h6><b>Company</b></h6>
                <ul>
                    <li><a href="<?php echo URL::to('/about-us'); ?>">About</a></li>
                    <li><a href="<?php echo URL::to('/terms'); ?>">Terms</a></li>
                    <li><a href="<?php echo URL::to('/privacy-policy'); ?>">Privacy Policy</a></li>
                    <li><a href="{{URL::to('/features')}}">Features</a></li>
                </ul>
            </div>

            <div class="col-md-2 col-6 userfullLinke cmn">
                <h6><b>USEFUL LINKS</b></h6>
                <ul>
               
                    <li><a href="<?php echo URL::to('contact-us'); ?>">Contact Us</a></li>
                    <li><a href="<?php echo URL::to('seller-guide'); ?>">Seller Guide</a></li>
                    <!-- <li><a href="<?php echo URL::to('/change-log'); ?>">Changelog</a></li> -->
                   
                    <!-- <li><a href="<?php echo URL::to('/comingsoon'); ?>">MallShark Blog</a></li> -->
                </ul>
            </div>

            <div class="col-md-4 col-12 playStoreLinks footerNewsLetter">
                    <div class="text-right tphed">
                        Subscribe and Get Discounts,<br>
                        Recommendations & New products<br>
                        <small> 
                    Note! We never share your data to any third party
                </small>
                    </div>

                 <div class="form-newsLetter">   
                <form class="" id="subscriptionForm" method="post">
                    @csrf
                    <div class="form-group form-inline">
                        <input type="email" name="email" class="form-control" id="email"
                            placeholder="Your E-Mail Address">
                            <button type="submit" class="btn btn-primary join-button">JOIN</button>
                        </div>
                        <div class="error" id="email-error" style="display: none;"></div>
                </form> 
                 </div> 

                 <div class="align-items-center d-flex hiddenDesktop justify-content-center" style="margin-bottom:0.5rem;">
                    <div class="followUs" style="margin-top: 0;">
                        <a class="icon fb-icon" target="_blank" href="https://www.facebook.com/ShopMallShark"><i class="fa fa-facebook"></i></a>
                        <a target="_blank" href="https://twitter.com/ShopMallShark" class="icon t-icon"><i class="fa fa-twitter"></i></a>
                        <a target="_blank" href="https://www.instagram.com/shopmallshark/" class="icon ins-icon"><i class="fa fa fa-instagram"></i></a>
                        <a target="_blank" href="https://discord.gg/hW9DZapKyH" class="icon dis-icon"><img width="50" src="{{URL::asset('/assets/images/discord.svg')}}"></a>
                    </div>
                </div>

            

            </div>


            </div>
        </div>
        <div class="min-footer">
            <div class="container">
            <div class="row align-items-center justify-content-between  ">
                <div class="copy-right col-left set-cont-mb-s reset-last-child">
                     Copyright © <?php echo date("Y"); ?> <a class="copyright" href="{{ URL::to('/') }}"><b>Mallshark.com,</b></a> Inc. All Rights Reserved.
                </div>
                <!-- <div class="col-right set-cont-mb-s reset-last-child">
                    <ul class="d-flex align-items-center justify-content-end">
                        <li>
                            <a href="<?php echo URL::to('contact-us'); ?>">
                                Help Center
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo URL::to('contact-us'); ?>">
                              Disputes
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo URL::to('contact-us'); ?>">
                             Reports 
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo URL::to('/buyerfaq'); ?>">
                              Buyer Protection
                            </a>
                        </li>



                    </ul>

                    {{-- <img src="{{ URL::asset('assets/images//payments.png')}}" alt="payments"></div>                 --}}
                </div> -->
            </div>

        </div>



        
    </div>
</div>
    <div id="accept-cookies" class="alert-dismissible alert-info fixed-bottom mb-0">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8" style="text-align: justify; font-size: 14px;
            word-spacing: -1px;">
                We use cookies to make your browsing experience sweeter! By continuing to devour our website, you give us your virtual consent to indulge in some cookie goodness. Don't worry, they won't make you gain virtual calories! If you're craving more information about our cookie policy, you can find it at the bottom of the page.
            </div>
            <div class="row cookie_accpet_row">

                <div class="col-md-6 text-md-end">
                    <button id="accept-cookies-btn" class="btn btn-primary btn-sm">Accept</button>
                </div>
                <div class="col-md-6 text-md-end">
                    <button id="reject-cookies-btn" class="btn btn-primary btn-sm" style="width: 100%;">Reject</button>
                </div>
            </div>
        </div>
    </div>
</div>
    </div>
 
    <script type="text/javascript" src="{{ URL::asset('assets/js/offset.js') }}"></script>

</footer>
<!-- Footer Ends -->

<div id="main_header">
    <div class="wrapper"></div>
</div>
@include('layouts.signin-register')
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery.jssocials/1.4.0/jssocials.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/masonry-layout@4.2.2/dist/masonry.pkgd.min.js" integrity="sha384-GNFwBvfVxBkLMJpYMOABq3c+d3KnQxudP/mGPkzpZSTYykLBNsZEnG2D9G/X/+7D" crossorigin="anonymous" async></script>


<script type="text/javascript">
// subscribe Email
// $(document).ready(function() {
//     $('.join-button').on('click', function(e) {
//         e.preventDefault(); // Prevent the default form submission

//         // Client-side validation
//         let email = $('#email').val();
        
//         $.ajax({
//             url: "{{ route('join.in') }}", // Use the Laravel route helper to generate the correct URL
//             method: "POST",
//             data: {
//                 email: email,
//                 _token: '{{ csrf_token() }}'
//             },
//             success: function(response) {
                
//                 // Handle success response
                
//                 // You can also clear the form fields if needed
//                 $('#email').val('');
//             },
//         });
//     });
// });
$(document).ready(function() {
    $('.join-button').on('click', function(e) {
        e.preventDefault(); // Prevent the default form submission

        // Client-side validation
        let email = $('#email').val();
        
        // Email format validation using regex
        let emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
        
        if (!emailPattern.test(email)) {
            // Display inline error below the email input field without disturbing the layout
            $('#email-error').text('Please enter a valid email address.').show();
            return; // Stop the form submission if the email is invalid
        }

        // Hide the error if the email is valid
        $('#email-error').hide();

        $.ajax({
            url: "{{ route('subscriber') }}", // Use the Laravel route helper to generate the correct URL 
            method: "POST",
            data: {
                email: email,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                
                // Handle success response
                
                // You can also clear the form fields if needed
                $('#email').val('');

                toastr.options = {
                    closeButton: true,
                    tapToDismiss: true,
                    positionClass: 'toast-top-right',
                    progressBar: true,
                };

                if (response.status) {
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr, status, error) {
                toastr.error('An error occurred. Please try again later.');
            }
        });
    });
});



$(document).ready(function() {
        $('.delete-notification').on('click', function() {
            var row = $(this).closest(".list-notifications");
            var id = $(this).data('id'); // If you have a data attribute with the item ID

            // Send an AJAX request to mark the list item as read
            $.ajax({
                url: '/delete-notification',
                type: 'POST',
                data: { notification_id: id,_token: '{{ csrf_token() }}' }, // Data to be sent in the request body
                dataType: 'json',
                success: function(data) {
                    
                    if(data.status){
                        
                        if(data.notiCounter == 0){
                            $(".notification_message").html(data.message);
                            // $(".entypo-bell").html(data.notiCounter);
                            $(".delete-notification").remove();
                            $(".notification_year").remove();
                        }else{
                            // $(".entypo-bell").html(data.notiCounter);
                        row.slideUp("slow", function() {
                                row.remove(); // Remove the row after animation
                            });  
                        }
                        
                         
                              
                                // $("#new-noti").addClass("zero");
                            

                    }else{
                        
                    }
                    

                },
                error: function(xhr, status, error) {
                  
                }
            });
        });
    });


$('#accept-cookies-btn').on('click',function(){
    
    createCookie('cookie-consent','1',90);
    $('#accept-cookies').css('display', 'none');

})
$('#reject-cookies-btn').on('click',function(){
    
    createCookie('cookie-consent','0',30);
    $('#accept-cookies').css('display', 'none');

})
$(document).ready(function() {
    let cookieConsent = getCookie('cookie-consent');
    
    if (!cookieConsent) {
        // Display the popup here
        $('#accept-cookies').css('display', 'block');
    }
});
function createCookie(name, value, expirationDays) {
    var expires = '';
    if (expirationDays) {
        var date = new Date();
        date.setTime(date.getTime() + (expirationDays * 24 * 60 * 60 * 1000));
        expires = '; expires=' + date.toUTCString();
    }
    if(typeof value === 'object'){
        let serializedObject = JSON.stringify(value);
        document.cookie = name + '=' + encodeURIComponent(serializedObject) + expires + '; path=/';
    }else{

        document.cookie = name + '=' + encodeURIComponent(value) + expires + '; path=/';
    }
}
function getCookie(name,isObject=false) {
    var cookieName = name + '=';
    var decodedCookie = decodeURIComponent(document.cookie);
    var cookieArray = decodedCookie.split(';');
    for (var i = 0; i < cookieArray.length; i++) {
        var cookie = cookieArray[i];
        while (cookie.charAt(0) === ' ') {
            cookie = cookie.substring(1);
        }
        if (cookie.indexOf(cookieName) === 0) {
            if(isObject){
              let serializedObject =  cookie.substring(cookieName.length, cookie.length);
              return JSON.parse(decodeURIComponent(serializedObject))
            }
            return cookie.substring(cookieName.length, cookie.length);
        }
    }
    return '';
}
    $("#share").jsSocials({
        showLabel: false,
        showCount: false,
        shares: ["facebook", "twitter", "googleplus", "pinterest"]
    });
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function sendreq(data, controller, action, functiontocall, async) {
        var sitebaseurl = $('#site_url').val();
        $.ajax({
            url: sitebaseurl + '/' + controller + '/' + action,
            type: 'POST',
            data: data,
            success: function(data) {
                eval(functiontocall)(data);
            },
            beforeSend: function() {}
        });
    }


    $('#explore-search').click(function() {
        $('.search-container').show();
        $(".search-inner-body").delay(200).fadeIn();
        $('body').addClass("ovfw-hde");
    });

    $('#close-search-panel,.close-icon-search').click(function() {
        $('body').removeClass("ovfw-hde");
        $(".search-inner-body").delay().fadeOut();
        $('.search-container').hide();
    });


    

    // script forr checked unchecked exoplore search

    $('.left-chek-span input[type="radio"]').on("click", function(event) {
        $('.art input:checkbox').removeAttr('checked');
        $('.left-chek-span input[type="radio"]:not(:checked)').parent().parent().parent().parent().removeClass('active-child');
        $('.left-chek-span input[type="radio"]:checked').parent().parent().parent().parent().addClass('active-child');
        $('.left-chek-span input[type="radio"]:checked').parent().parent().parent().parent().next();

    });

    $('#other').click(function() {
        if ($(this).is(':checked')) {
            $(".other-option").show();
        } else {
            $(".other-option").hide();
        }
    });

    $('#full-time-job,#part-time,#I-like-it').click(function() {
        if ($(this).is(':checked')) {
            $(".other-option").hide();
        } else {
            $(".other-option").hide();
        }
    });



    $("div.product-repeater").each(function() {
        $(this).hide();
        if ($(this).attr('id') == 'art-load') {
            $(this).fadeIn();
        }
    });

    $('.CatLst li a').on("click", function(e) {
        e.preventDefault();
        $(".CatLst li a").removeClass("crnt");
        var id = $(this).attr('data-related');
        $("div.product-repeater").each(function() {
            $(this).hide();
            if ($(this).attr('id') == id) {
                $(this).fadeIn();
            }
        });
        $(this).addClass('crnt')


    });


    // script for close popover tooltip
    $('body').on('click', function(e) {
        $('[data-toggle="popover"]').each(function() {
            //the 'is' for buttons that trigger popups
            //the 'has' for icons within a button that triggers a popup
            if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
                $(this).popover('hide');
            }
        });
    });


    $('a.remove-item-mbt').click(function() {
        $(this).parent().parent().parent().toggleClass('show-asdf');
        $(this).parent().parent().parent().parent().toggleClass('show-asdf');
    });


    // $('.slider-for img').on('click', function() {
        
    //     $("body").addClass('overfollow-hidden');
    // });

    
setTimeout(() => {
    
    $('body').on('click', '.slick-lightbox-close', function() {
        
        $("body").removeClass('overfollow-hidden');
    });
}, 1000);

    (function($) {
        // console.log(navigator.userAgent);
        /* Adjustments for Safari on Mac */
        if (navigator.userAgent.indexOf('Safari') != -1 && navigator.userAgent.indexOf('Mac') != -1 && navigator.userAgent.indexOf('Chrome') == -1) {
            // console.log('Safari on Mac detected, applying class...');
            $('html').addClass('safari-mac'); // provide a class for the safari-mac specific css to filter with

        }
    })(jQuery);
    <?php if (Session::has('userData')) { ?>
        $(document).ready(function() {
            setInterval(ajaxCall, 3000);
        });

        function ajaxCall() {
            var data = {};
            sendreq(data, 'user', 'check', 'callbackres');
        }

        function callbackres(data) {
            // console.log(data.status);
            // if(data.status){
            //     $("#mark-read").removeClass("new-arrow-explore").addClass("arrow-explore");
            // }else{
            //     $("#mark-read").removeClass("arrow-explore").addClass("new-arrow-explore");
            // }
            
        
            // $("#mark-read").removeClass("new-arrow-explore").addClass("arrow-explore");
            if (data != "") {
                $(".msg-noti").html(data);
            }
        }
    <?php } ?>
    $(document).on('click', '.notbuy', function() {
        //alert("You cannot buy your own product");
        swal("You cannot buy your own product");
    });
    // jQuery(document).on('click','.plus-icon',function(){
               
    //            var product_type = $(this).attr('data-product-type');
    //            var data_maxquantity = $(this).attr('data-maxquantity');
    //            console.log("data_maxquantity", data_maxquantity);
    //            var currentStock = 0;
    //            if(product_type=='virtual') {
    //               currentStock = 10;
    //            } else {
    //               currentStock = $(this).closest('.card-body').attr('data-stock');
    //            }
    //           var plusVal = $(this).prev(".input-counter").find("input[type=text]").val();
      
    //           plusVal++;
             
              
         
    //           if (data_maxquantity == null || data_maxquantity === '') {
    //               // If max quantity is not defined, use current stock      
    //               currentStock = currentStock;
    //             }else if(data_maxquantity == 0){
    //               currentStock = 1;
    //             } else if (currentStock > data_maxquantity) {        
    //               // If current stock is greater than max quantity, use max quantity
    //               currentStock = data_maxquantity;
    //             }else if(currentStock < data_maxquantity){
    //               currentStock = data_maxquantity;
    //             }
        
              
    //           if (currentStock >= plusVal) {
    //               var data = {};
    //               data['product_id'] = $(this).attr('data-pro-id');
    //               data['store_id'] = $(this).attr('data-stor-id');
    //               data['product_quantity_plus'] = plusVal;
    //               sendreq(data, 'cart', 'add', 'callbackBasket');
    //               $(this).prev(".input-counter").find("input[type=text]").val(plusVal);
    //               if (currentStock == plusVal) {
    //                   $(this).addClass('disabled');
    //                   $(this).parent().addClass('cntr-txt-fld');
    //               }
    //           } else {
    //               $(this).addClass('disabled');
    //               $(this).parent().addClass('cntr-txt-fld');
    //           }
    //       });
      
    //   jQuery(document).on('click','.minus-icon',function(){        
    //           var minusVal = $(this).next(".input-counter").find("input[type=text]").val();
    //           if (minusVal > 1) {
    //               minusVal--;
    //               var data = {};
    //               data['product_id'] = $(this).attr('data-pro-id');
    //               data['store_id'] = $(this).attr('data-stor-id');
    //               data['product_quantity_minus'] = minusVal;
    //               sendreq(data, 'cart', 'add', 'callbackBasket');
    //               $(this).next(".input-counter").find("input[type=text]").val(minusVal);
    //               $(this).closest(".card-body").find(".plus-icon").removeClass('disabled');
    //               $(this).parent().removeClass('cntr-txt-fld');
      
    //           } else {
    //               $(this).closest('.show-quantitiy-panel').removeClass('show-quantitiy-panel');
    //               $(this).closest(".card-body").find(".plus-icon").removeClass('disabled');
    //               $(this).parent().removeClass('cntr-txt-fld');
    //               $(this).next(".input-counter").find("input[type=text]").val(1);
    //               var data = {};
    //               data['product_id'] = $(this).attr('data-pro-id');
    //               sendreq(data, 'cart', 'deletebasketajax', 'callBackDelBasket');
      
    //           }
    //       });
    $(document).on('click', '.cartadd-home', function() {
        window.dataLayer = window.dataLayer || [];
        let product_title = $(this).closest('.splopt').find('.product_title').data('title');
        let product_brand = $(this).closest('.splopt').find('.product_store').data('store');
        let product_price = $(this).closest('.splopt').find('.product_price').data('product-price');
        let product_parent_category = $(this).closest('.splopt').find('.product_parent_category').data('category');
        let product_child_category = $(this).closest('.splopt').find('.product_child_category').data('category');
        let product_stock = $(this).closest('.splopt').find('.product_stock').data('stock');
        let product_id = $(this).closest('.splopt').find('.p_id').data('product-id');
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
        

        $(this).parent().addClass("show-quantitiy-panel");
        currentBody = $(this).closest('.crtbtn');
        storeId = $(this).attr('data-store-id');
        // currentStock = currentBody.attr('data-stock');
        quantity = currentBody.find("input[type=text]").val();
        //jQuery('.uploadImages').show();
        var data = {};
        data['product_id'] = $(this).attr('data-product-id');
        if (storeId != "") {
            data['store_id'] = $(this).attr('data-store-id');
        }
        data['product_quantity'] = 1;
        data['product_quantity_plus']=data['product_quantity'];
        sendreq(data, 'cart', 'add', 'callbackBasket');

    });



    $(document).on('click', '.adtofv-home', function() {
        var current = $(this);
        var productId = current.data('product-id');

        var data = {
            'product_id': productId
        };

        if (current.hasClass('fillheart')) {
            // Already favorited, remove it
            sendreq(data, 'favourite', 'add', 'callbackfavourites');
            current.removeClass('fillheart');
        } else {
            // Not favorited, add it
            sendreq(data, 'favourite', 'add', 'callbackfavourites');
            current.addClass('fillheart');
        }
    });

    //User Notification
    let isProcessing = false;
    $(document).on('click', '#user_notification', function() {
        if (isProcessing) {
        return;
    }
    isProcessing = true;
        $.ajax({
          url: "/user/check-notification", 
          type: "POST", 
          dataType: "json", 
          success: function(data) {

            $("#mark-read").removeClass("arrow-explore").addClass("new-arrow-explore");
            $('.entypo-bell').html(0);
          },
          complete: function() {
            isProcessing = false;
          },
          error: function(xhr, status, error) {
            // This function will be called if there is an error with the request
            $("#result").text("Error: " + error);
          }
        });
    });
    //  Fav Store
    $(document).on('click', '.adtofv-home-store', function() {
        var current = $(this);
        var storeId = current.data('store-id');
        var iconElement = current.find('i');

        var data = {
            'store_id': storeId
        };

        if (iconElement.hasClass('fa-heart')) {
            // Already favorited, remove it
            sendreq(data, 'favouritestore', 'remove', 'callBackFavouritesStore');
            iconElement.removeClass('fa-heart');
            iconElement.addClass('fa-heart-o');
        } else {
            // Not favorited, add it
            sendreq(data, 'favouritestore', 'add', 'callBackFavouritesStore');
            iconElement.addClass('fa-heart');
            iconElement.removeClass('fa-heart-o');
        }
        });


    function callbackfavourites(data) {

        if (data.status == 'success') {
            toastr.options = {
            closeButton: true, // Add close button
            disableTimeOut: true,
            tapToDismiss: true,
            positionClass: 'toast-top-right',
            toastClass: 'toast-icon custom-toast-success'
            };

            toastr.success(data.message);
           
        } else if (data.status == 'error') {
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
    function callBackFavouritesStore(data) {

        if (data.status == 'success') {
            toastr.options = {
            closeButton: true, // Add close button
            disableTimeOut: true,
            tapToDismiss: true,
            positionClass: 'toast-top-right',
            toastClass: 'toast-icon custom-toast-success'
            };
            toastr.success(data.message);
        } else if (data.status == 'error') {
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

    $(document).ready(function() {
        // Hide the div
        $(".page-search-results,.home-banner-box").css('opacity', '0');

        // Show the div in 5s
        $(".page-search-results, .home-banner-box, .zomeed-slider-home, .filtered-boxed").delay(500).css('opacity', '1');
    });
    $(document).ready(function() {
        $("body").tooltip({
            selector: '[data-toggle=tooltip]'
        });
    });
    //$(function() {
    //    $('.reward-conainer-filter ul').each(function(){
    //       var contentWidth = $(this).width();
    //       var contentHeight = $(this).height();
    //       console.log(contentWidth)       
    //       if ($(".reward-conainer-filter ul").width() < 800) {
    //            $(this).parent().addClass('shw-chld-hde-cntent');
    //       }        
    //       else {
    //           
    //       }        
    //       
    //       
    //   }); 
    //   
    //
    //
    //
    //    });
    //   





    $(document).on("click", ".cke_button.cke_button__maximize.cke_button_off", function() {
        $('body').removeClass('maximize-activate');
        alert("Un-click");

    });

    $(document).on("click", ".cke_button.cke_button__maximize.cke_button_on", function() {
        $('body').addClass('maximize-activate');
        alert("click");
    });

    function urlParam(name) {
        return (location.search.split(name + '=')[1] || '').split('&')[0];
    }
    jQuery(document).ready(function() {

        reload = urlParam('reload2');
        url_change = window.location.pathname;
        if (reload) {
            url = '/search';
            window.history.pushState({
                urlPath: url
            }, null, url);
            /*
            window.history.pushState({urlPath: url}, null, url);
            window.history.pushState({urlPath: url}, null, url);
            window.history.pushState({urlPath: url}, null, url);
            window.history.pushState({urlPath: url}, null, url);
            window.history.pushState({urlPath: url}, null, url);
            */
            window.history.pushState({
                urlPath: url_change
            }, null, url_change);


            location.href = url;
            //location.href='/';
            $(window).on('popstate', function(event) {

                if (url == '/search') {

                    location.reload();
                }
            });
        }


    });


    window.addEventListener("pageshow", function(event) {
        console.log(window.performance.navigation);
        var historyTraversal = event.persisted || (typeof window.performance != "undefined" && window.performance.navigation.type === 2);
        if (historyTraversal) {
            window.location.reload();
        }
    });

    if ($(".slickSliderForMobile").length > 0) {
        jQuery('.slickSliderForMobile ul').slick({
            infinite: true,
            slidesToShow: 3,
            variableWidth: true,
            slidesToScroll: 1
        });
    }

    function callbackBasket(data) {

        if(data.status){
           
            $(".cartmenu").html(data.view);
            $("#cart_counter_ajax").html(data.cartCounter);
            
            toastr.clear();
            toastr.options = {
                closeButton: true,
                timeOut: 3000, // Duration of 3 seconds
                extendedTimeOut: 0, // No delay on hover
                tapToDismiss: true,
                positionClass: 'toast-top-left',
                toastClass: 'toast-icon custom-toast-success',
                newestOnTop: false, // Prevent stacking on top
                // Disable animations for instant clear and display
                showDuration: 0,
                hideDuration: 0,
                progressBar: true
            };
            toastr.success(data.message);
        
            setTimeout(function() {
                $(".cart-list-nav").removeClass("bounce-up");
                $("#cart_counter_ajax").removeClass("bounce-up");
            }, 200);
        }else{
            $(".cartmenu").html(data.view);
            $("#cart_counter_ajax").html(data.cartCounter);
            toastr.options = {
            closeButton: true, // Add close button
            disableTimeOut: true,
            tapToDismiss: true,
            positionClass: 'toast-top-right',
            showEasing: 'swing',
            hideEasing: 'linear',
            toastClass: 'toast-icon custom-toast-success'
            };
            toastr.error(data.message);
        
            setTimeout(function() {
                $(".cart-list-nav").removeClass("bounce-up");
                $("#cart_counter_ajax").removeClass("bounce-up");
            }, 200);
        }
    }

 



    // for Super flash deals
    if ($(".productItems").length > 0) {
        jQuery('.productItems .slideItem').slick({
            infinite: true,
            slidesToShow: 2,
            slidesToScroll: 1

        });
        $('.productItems .slick-arrow').text('');
    }
    if ($(".flashDealSlide").length > 0) {
        jQuery('.flashDealSlide .slideItem').slick({
            infinite: true,
            slidesToShow: 1,
            slidesToScroll: 1

        });
        $('.flashDealSlide .slick-arrow').text('');
    }


 
</script>
<!-- Counter Script Starts -->

<!-- Counter Script Ends -->
<style>
    /***********Media query***********/

    @media only screen and (min-width : 768px) {
        .hidden-desktop {
            display: none;
        }
    }

    @media only screen and (max-width : 768px) {
        .hidden-mobile {
            display: none;
        }
    }

    .copyright {
        color: blue;
    }
</style>

<script id="ze-snippet" src="https://static.zdassets.com/ekr/snippet.js?key=060e751b-0ff7-46cf-b3cb-916b5c883c1b"> </script>
<script>
    // Function to detect if the device is a mobile phone
    function isMobileDevice() {
        return (typeof window.orientation !== "undefined") || (navigator.userAgent.indexOf('Mobile') !== -1);
    }

    function checkOrientation() {
        // Only check orientation if it's a mobile device
        if (isMobileDevice()) {
            if (window.innerWidth > window.innerHeight) {
                $('#landscapeWarning').fadeIn(); // Show the overlay in landscape mode
            } else {
                $('#landscapeWarning').fadeOut(); // Hide the overlay in portrait mode
            }
        }
    }

    $(document).ready(function() {
        // Initial check
        checkOrientation();
        
        // Listen for orientation changes
        $(window).on('resize orientationchange', function() {
            checkOrientation();
        });
    });
</script>
</body>

</html>  