@extends('layouts.new-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Sell on MallShark: Expand Your Reach and Boost Your Sales!')
@section('description', 'Tap into the immense selling potential of MallShark. Boost your online sales, expand your reach, and maximize your selling opportunities with our ecommerce platform.')
@section('keyword', "Sell on MallShark,MallShark.net,Online selling, Ecommerce platform, Increase sales, Sell products online, Expand reach, Boost sales, Online marketplace, Selling opportunities, MallShark seller, Selling platform, Ecommerce website, Grow business, Sell online effectively")
@section('google-analytics')
@include('layouts.google-analytics')
@stop

<section class="mnbnr">


    <div class="start-selling-page">
    <div class="container">
      <h1>Start Selling Today!</h1>

       <div class="row">
        <div class="col-md-4">
            <div class="start-seller-page-images">
                <div class="seller-home-img">
                    <img src="{{ URL::asset('assets/images/crypto-bank.png') }}" alt="image" srcset="">
                </div>

                <div class="seller-network-img">
                    <img src="{{ URL::asset('assets/images/seller_network.png') }}" alt="image" srcset="">
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="right-content">
                <h1>Transparent, Flexible, Secure</h1>

                <div class="box-text-with-icons">
                    <div class="icon-box-wrapper">
                        <div class="icon">
                            <img src="{{ URL::asset('assets/images/percent-icon.png') }}" alt="image" srcset="">

                        </div>
                        <div class="icon-content">
                            <h5>Low transaction processing fee of 4.5%</h5>
                            <p>When you sell an item, there’s a small commission and standard payment processing fee.</p>
                        </div>
                    </div>
                    <div class="icon-box-wrapper">
                        <div class="icon">
                            <img src="{{ URL::asset('assets/images/badge-icon.png') }}" alt="image" srcset="">

                        </div>
                        <div class="icon-content">
                            <h5>Listing Fee</h5>
                            <p>Listings are active for three months, or until they sell.</p>
                        </div>
                    </div>
                    <div class="icon-box-wrapper">
                        <div class="icon">
                            <img src="{{ URL::asset('assets/images/store-icon.png') }}" alt="image" srcset="">

                        </div>
                        <div class="icon-content">
                            <h5>On Site Advertising <div class="tooltip">
                                <i class="fa fa-info-circle"></i>
                                <span class="tooltiptext">Advertising fees and placement vary from front page to shopping list rankings.</span>
                              </div></h5>
                            <p>As a seller on MallShark, you can promote your products for <br> a small fee and reach a wider audience.</p>
                        </div>
                    </div>
                </div>

                <div class="icon-box-wrapper lock-with-text">
                    <div class="icon">
                        <img src="{{ URL::asset('assets/images/lock.png') }}" alt="image" srcset="">

                    </div>
                    <div class="icon-content">
                        <p>We process payments on our secure, blockchain server powered by Polygon, and have <br> security specialists and fraud detection systems to protect you and your buyers 24/7.</p>
                    </div>
                </div>

                <div class=" text-center">
                    <?php

use Google\Service\CloudSearch\Id;

                    $url = ''; 
                    $userId = isset(Session::get('userData')['user_id']) ?  Session::get('userData')['user_id']: "";
                    if(isset($userId) && !empty($userId)){
                          $url = URL::to('/store-name');
                    }else{
                         $url = URL::to('/signup?store=creation');
                         $id = "check-email-exist";
                    
                    }

                    ?>
                    <a href="{{$url}}"><button class="btn btn-primary" id="{{}}" style="background-color:#0510FA">Create My Store</button></a>
                </div>
            </div>
        </div>

    </div>

    <div class="background-light-back"></div>
</div>
</div>
</section>

@endsection