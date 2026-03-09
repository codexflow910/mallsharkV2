@extends('layouts.new-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Store Subscription')



<section class="store-subscription">

    <div class="full-background-image display-table height50vh" style="background:url({{ asset('assets/images/bg-subs.jpg',env('HTTPS_STATUS')) }}); background-size: cover; ">
        <div class="display-table-cell vertical-middle">

            <div class="container">
                <div class="row">
                    <div class="col-sm-12">				
                        <h1>Subscription <span class="text-blue">Packages</span><br>
                            Which Suits your Sale Needs
                        </h1>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-50">
        <div class="row">
            <div class="col-sm-10 mb-50">
                <h2>What you get after subscription</h2>
                <!-- <p>Het is al geruime tijd een bekend gegeven dat een lezer, tijdens het bekijken van de layout van een pagina, afgeleid wordt door de tekstuele inhoud. Het belangrijke punt van het gebruik van Lorem Ipsum is dat het uit een min of meer normale verdeling van letters bestaat, in tegenstelling tot "Hier uw tekst, hier uw tekst" wat het tot min of meer leesbaar nederlands maakt. Veel desktop publishing pakketten en web pagina editors gebruiken tegenwoordig Lorem Ipsum als hun standaard model tekst, en een zoekopdracht naar "lorem ipsum" ontsluit veel websites die nog in aanbouw zijn.  -->
                </p>
            </div>
            <div class="col-sm-5">
                <h3 class="mb-15"> <span class="icon-good"><i class="fa fa-check" aria-hidden="true"></i></span> Dealer Website/Portal</h3>
                <!-- <p>Het is al geruime tijd een bekend gegeven dat een lezer, tijdens het bekijken van de layout van een pagina, afgeleid wordt door de tekstuele inhoud. Het belangrijke punt van het gebruik van Lorem Ipsum is dat het uit een min of meer normale verdeling van letters bestaat, in tegenstelling tot "Hier uw tekst, hier uw tekst" wat het tot min of meer leesbaar nederlands maakt. </p> -->

            </div>

            <div class="col-sm-5 mb-30">
                <h3 class="mb-15"> <span class="icon-good"><i class="fa fa-check" aria-hidden="true"></i></span> Grow Your Social Network</h3>
                <!-- <p>Het is al geruime tijd een bekend gegeven dat een lezer, tijdens het bekijken van de layout van een pagina, afgeleid wordt door de tekstuele inhoud. Het belangrijke punt van het gebruik van Lorem Ipsum is dat het uit een min of meer normale verdeling van letters bestaat, in tegenstelling tot "Hier uw tekst, hier uw tekst" wat het tot min of meer leesbaar nederlands maakt. </p> -->

            </div>

            <div class="col-sm-5 mb-30">
                <h3 class="mb-15"> <span class="icon-good"><i class="fa fa-check" aria-hidden="true"></i></span> Work In A Team</h3>
                <!-- <p>Het is al geruime tijd een bekend gegeven dat een lezer, tijdens het bekijken van de layout van een pagina, afgeleid wordt door de tekstuele inhoud. Het belangrijke punt van het gebruik van Lorem Ipsum is dat het uit een min of meer normale verdeling van letters bestaat, in tegenstelling tot "Hier uw tekst, hier uw tekst" wat het tot min of meer leesbaar nederlands maakt. </p> -->

            </div>

            <div class="col-sm-5">
                <h3 class="mb-15"> <span class="icon-good"><i class="fa fa-check" aria-hidden="true"></i></span> Adverties To Millions</h3>
                <!-- <p>Het is al geruime tijd een bekend gegeven dat een lezer, tijdens het bekijken van de layout van een pagina, afgeleid wordt door de tekstuele inhoud. Het belangrijke punt van het gebruik van Lorem Ipsum is dat het uit een min of meer normale verdeling van letters bestaat, in tegenstelling tot "Hier uw tekst, hier uw tekst" wat het tot min of meer leesbaar nederlands maakt. </p> -->

            </div>
        </div>
        <?php
        if (Session::has('msg')) {
            echo '<div class="alert alert-success">
  ' . Session::get("msg") . '
</div>';
            ?>
            <script>
                jQuery(document).ready(function () {
                    jQuery("html, body").animate({scrollTop: jQuery(document).height() - jQuery(window).height()}, 1000);
                });
            </script>
            <?php
        }
        ?>
        @if(count($data)>0 && $user_id!='')

        <div class="row mt-40">

            <?php
            //$userinfo = getUserById(getCurrentUserId());
            // print_rr($userinfo);
            foreach ($data as $key => $val) {
                ?>

                <div class="col-md-4 ">
                    <div class="card br-all br-blue text-center pb-30 pt-20 radious-3">
                        <p class="text-blue"><?php echo $val->nickname; ?> Subscription</p>
                        <h4 class="text-blue mb-0 mt-20">$<?php echo $val->amount / 100; ?></h4>
                        <?php
                        if (isset($val->metadata->save_amount)) {
                            $margi = 0;
                        } else {
                            $margi = 80;
                        }
                        ?>
                        <span  class="text-blue display-block mb-<?php echo $margi; ?>">per <?php echo $val->interval; ?></span>
                        <?php if (isset($val->metadata->save_amount)) { ?>
                            <div class="you-save-money mt-20 mb-15">
                                <span class="text-blue">You Save $<?php echo $val->metadata->save_amount; ?></span>
                            </div>
                        <?php } ?>
                        <!-- <div class="space-128"></div> -->
                        <?php if ($customer_id == '') { ?>
                            <form action="/stripe-create-customer" method="POST">
                                <script
                                    src="https://checkout.stripe.com/checkout.js" class="stripe-button"
                                    data-key="{{env('STRIPE_API_KEY_PUBLISHABLE')}}"
                                    data-label="Subscribe <?php echo $val->interval; ?>ly"
                                    data-amount="<?php echo $val->amount; ?>"
                                    data-name="Mallshark"
                                    data-description="Membership Subscription"
                                    data-image="https://stripe.com/img/documentation/checkout/marketplace.png"
                                    data-classes="btn btn-primary text-white fnt-14 mt-35"
                                    data-locale="auto">
                                </script>
                                <input type="hidden" name="plan_id" value="<?php echo $val->id; ?>">
                                {{ csrf_field() }}

                            </form>
                            <?php
                        } else {
                            ?>
                            <form action="/stripe-subscripe-plan" method="POST">
                                <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
                                <input type="hidden" name="plan_id" value="<?php echo $val->id; ?>">
                                {{ csrf_field() }}
                                <input type="submit" class="btn btn-primary text-white fnt-14" value="Subscribe <?php echo $val->interval; ?>" name="submit">
                            </form>
                            <?php
                        }
                        ?>


                    </div>
                </div>
            <?php } ?>
            <!--            <div class="col-md-4">
                            <div class="card br-all br-blue text-center pb-30 pt-20 radious-3">
                                <p class="text-blue">Monthly Subscription</p>
                                <h4 class="text-blue mb-0 mt-20">$</h4>
                                <span  class="text-blue display-block">Per month</span>
                                <div class="you-save-money mt-20 mb-50">
                                    <span class="text-blue">You Save $60</span>
                                </div>
                                 <div class="space-128"></div> 
                                <a href="javascript:;" class="btn btn-primary text-white fnt-14">Subscribe Monthly</a>
            
                            </div>
                        </div>-->

        </div>
        @endif
        @if($user_id=='')
        <h2> Please Connect your stripe account first for subscription</h2>
        @endif

    </div>
</section>



<!-- Content Ends-->
@endsection