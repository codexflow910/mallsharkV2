<!--Footer Starts -->
<style>
    .jssocials-share-link{ border-radius: 50% !important;}
</style>
<footer id="footer" class="footer">

    <div class="container">
    <div class="row">
    <div class="col-md-12">
        
                        <div class="  iconsSocialMedia ">
                        <a class="icon fb-icon" target="_blank" href="https://www.facebook.com/ShopMallShark"><i class="fa fa-facebook"></i></a>
                        <a target="_blank" href="https://twitter.com/ShopMallShark" class="icon t-icon"><i class="fa fa-twitter"></i></a>
                        <a target="_blank" href="https://www.instagram.com/shopmallshark/" class="icon ins-icon"><i class="fa fa fa-instagram"></i></a>
                        <a target="_blank" href="https://discord.gg/hW9DZapKyH" class="icon dis-icon"><img src="{{URL::asset('/assets/images/discord.svg')}}" style="width: 42px; height: 42px;"></a>
                        </div> 
                   
    </div>    
    </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-md-12 justify-content-between">
                <div class="mobile-full-width"> 
                    <div class="social-icons ">
                    <p>   
                        <div class="d-flex align-items-center iconsSocialMedia ">
                        <a class="icon fb-icon" target="_blank" href="https://www.facebook.com/ShopMallShark"><i class="fa fa-facebook"></i></a>
                        <a target="_blank" href="https://twitter.com/ShopMallShark" class="icon t-icon"><i class="fa fa-twitter"></i></a>
                        <a target="_blank" href="https://www.instagram.com/shopmallshark/" class="icon ins-icon"><i class="fa fa fa-instagram"></i></a>
                        <a target="_blank" href="https://discord.gg/hW9DZapKyH" class="icon dis-icon"><img src="{{URL::asset('/assets/images/discord.svg')}}" style="width: 42px; height: 42px;"></a>
                        </div> 
                    </p> 
                </div>

                    <p class="small hidden-mobile">Copyright © <?php echo date("Y"); ?> <a class="copyright" href="{{ URL::to('/') }}">Mallshark.net,</a> Inc. All Rights Reserved.</p>
                </div>
                <div class="footnav">
                    <a href="<?php echo URL::to('/'); ?>">Home</a>
                    <!-- <a href="<?php //echo URL::to('/content-page/about-mallshark'); ?>">About</a> -->
                    <?php if (!Session::has('userData')) {?>
                  
                        <a href="{{URL::to('/signup')}}">Sign Up </a>

                        <?php } ?>
                    <a href="<?php echo URL::to('/generalfaq'); ?>">General FAQs</a>
                    <a href="<?php echo URL::to('/sellerfaq'); ?>">Seller FAQs</a>
                    <a href="<?php echo URL::to('/buyerfaq'); ?>">Buyer FAQs</a>
                    <!-- <a href="#">Learn</a>
                    <a href="#">Help</a> -->
                    <a href="<?php echo URL::to('/terms'); ?>">Terms</a>
                    <a href="<?php  echo URL::to('content-page/about-us'); ?>">About Us</a><br>
                    <!-- <a href="<?php //echo URL::to('contact-us'); ?>">Contact</a><br> -->
                              <!-- Temporary hide link -->
                    <p class="small" style="display:none;">
                        <a href="<?php echo URL::to('/comingsoon'); ?>">Privacy</a>
                        <a href="#">Legal</a>
                    </p>
                    <p class="small hidden-desktop">Copyright © <?php echo date("Y"); ?> <a class="copyright" href="{{ URL::to('/') }}">Mallshark.net,</a> Inc. All Rights Reserved.</p>                    

                </div>
            </div>
        </div>
    </div>
    <!-- <script type="text/javascript" src="{{ URL::asset('assets/js/sweetalert.js') }}"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

     <!-- <link rel = "stylesheet" href = "{{ URL::asset('assets/css/sweetalert.css') }}" >  -->
    <script type="text/javascript" src="{{ URL::asset('assets/js/offset.js') }}"></script>
   
</footer>
<!-- Footer Ends -->
<div class="hidden-mobile">
    <br><br> 
</div>
<div id="main_header"><div class="wrapper"></div></div>
@include('layouts.signin-register')
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery.jssocials/1.4.0/jssocials.min.js"></script>


<script type="text/javascript">
 $("#share").jsSocials({
    showLabel: false,
            showCount: false,
            shares: ["facebook", "twitter", "googleplus", "pinterest"]
    });
$.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});

function sendreq(data, controller, action, functiontocall, async) {
    var sitebaseurl = $('#site_url').val();
    $.ajax({
        url: sitebaseurl + '/' + controller + '/' + action,
        type: 'POST',
        data: data,
        success: function (data) {
            eval(functiontocall)(data);
        },
        beforeSend: function () {
        }
    });
}


$('#explore-search').click(function () {
    $('.search-container').show();
    $(".search-inner-body").delay(200).fadeIn();
    $('body').addClass("ovfw-hde");
});

$('#close-search-panel,.close-icon-search').click(function () {
    $('body').removeClass("ovfw-hde");
    $(".search-inner-body").delay().fadeOut();
    $('.search-container').hide();
});




// script forr checked unchecked exoplore search

$('.left-chek-span input[type="radio"]').on("click", function (event) {
    $('.art input:checkbox').removeAttr('checked');
    $('.left-chek-span input[type="radio"]:not(:checked)').parent().parent().parent().parent().removeClass('active-child');
    $('.left-chek-span input[type="radio"]:checked').parent().parent().parent().parent().addClass('active-child');
    $('.left-chek-span input[type="radio"]:checked').parent().parent().parent().parent().next();

});

$('#other').click(function () {
    if ($(this).is(':checked')) {
        $(".other-option").show();
    } else {
        $(".other-option").hide();
    }
});

$('#full-time-job,#part-time,#I-like-it').click(function () {
    if ($(this).is(':checked')) {
        $(".other-option").hide();
    } else {
        $(".other-option").hide();
    }
});



$("div.product-repeater").each(function () {
    $(this).hide();
    if ($(this).attr('id') == 'art-load') {
        $(this).fadeIn();
    }
});

$('.CatLst li a').on("click", function (e) {
    e.preventDefault();
    $(".CatLst li a").removeClass("crnt");
    var id = $(this).attr('data-related');
    $("div.product-repeater").each(function () {
        $(this).hide();
        if ($(this).attr('id') == id) {
            $(this).fadeIn();
        }
    });
    $(this).addClass('crnt')


});


// script for close popover tooltip
$('body').on('click', function (e) {
    $('[data-toggle="popover"]').each(function () {
        //the 'is' for buttons that trigger popups
        //the 'has' for icons within a button that triggers a popup
        if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
            $(this).popover('hide');
        }
    });
});


$('a.remove-item-mbt').click(function () {
    $(this).parent().parent().parent().toggleClass('show-asdf');
    $(this).parent().parent().parent().parent().toggleClass('show-asdf');
});


$('.slider-for img').on('click', function () {
    $("body").addClass('overfollow-hidden');
});



$(document).on('click', '.slick-lightbox-close ,.slick-lightbox-slick-item', function () {
    $("body").removeClass('overfollow-hidden');
});

(function ($) {
    // console.log(navigator.userAgent);
    /* Adjustments for Safari on Mac */
    if (navigator.userAgent.indexOf('Safari') != -1 && navigator.userAgent.indexOf('Mac') != -1 && navigator.userAgent.indexOf('Chrome') == -1) {
        // console.log('Safari on Mac detected, applying class...');
        $('html').addClass('safari-mac'); // provide a class for the safari-mac specific css to filter with

    }
})(jQuery);
<?php if (Session::has('userData')) { ?>
    $(document).ready(function () {
        //setInterval(ajaxCall, 30000);
    });
    function ajaxCall() {
        var data = {};
        sendreq(data, 'user', 'check', 'callbackres');
    }
    function callbackres(data) {
        if (data != "") {
            $(".msg-noti").html(data);
        }
    }
<?php } ?>
$(document).ready(function () {
    // Hide the div
    $(".page-search-results,.home-banner-box").css('opacity', '0');

    // Show the div in 5s
    $(".page-search-results, .home-banner-box, .zomeed-slider-home, .filtered-boxed").delay(500).css('opacity', '1');
});
$(document).ready(function () {
    $("body").tooltip({selector: '[data-toggle=tooltip]'});
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





$(document).on("click", ".cke_button.cke_button__maximize.cke_button_off", function () {
    $('body').removeClass('maximize-activate');
    alert("Un-click");

});

$(document).on("click", ".cke_button.cke_button__maximize.cke_button_on", function () {
    $('body').addClass('maximize-activate');
    alert("click");
});

function urlParam(name) {
    return (location.search.split(name + '=')[1] || '').split('&')[0];
}
jQuery(document).ready(function () {

    reload = urlParam('reload2');
    url_change = window.location.pathname;
    if (reload) {
        url = '/search';
        window.history.pushState({urlPath: url}, null, url);
        /*
        window.history.pushState({urlPath: url}, null, url);
        window.history.pushState({urlPath: url}, null, url);
        window.history.pushState({urlPath: url}, null, url);
        window.history.pushState({urlPath: url}, null, url);
        window.history.pushState({urlPath: url}, null, url);
        */
        window.history.pushState({urlPath: url_change}, null, url_change);


         location.href = url;
        //location.href='/';
        $(window).on('popstate', function (event) {

            if (url == '/search') {

            location.reload();
            }
        });
    }


});


window.addEventListener( "pageshow", function ( event ) {
    console.log(window.performance.navigation);
  var historyTraversal = event.persisted || ( typeof window.performance != "undefined" && window.performance.navigation.type === 2 );
  if ( historyTraversal ) {
    window.location.reload();
  }
});

    if ($(".slickSliderForMobile").length > 0){
        jQuery('.slickSliderForMobile ul').slick({
            infinite: true,
            slidesToShow: 3,
            variableWidth: true,
            slidesToScroll: 1
        });
    }



</script>
<style>
    
    /***********Media query***********/

@media only screen and (min-width : 768px){
    .hidden-desktop{ display: none; }    
}
@media only screen and (max-width : 768px){
    .hidden-mobile{ display: none; }
}
.icons-class-mbt{
    width:42px;
    height:42px;
}
</style>

</body>
</html>
