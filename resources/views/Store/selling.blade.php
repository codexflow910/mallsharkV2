@extends('layouts.frontend-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Sell on MallShark: Expand Your Reach and Boost Your Sales!')
@section('description', 'Tap into the immense selling potential of MallShark. Boost your online sales, expand your reach, and maximize your selling opportunities with our ecommerce platform.')
@section('keyword', "Sell on MallShark,MallShark.net,Online selling, Ecommerce platform, Increase sales, Sell products online, Expand reach, Boost sales, Online marketplace, Selling opportunities, MallShark seller, Selling platform, Ecommerce website, Grow business, Sell online effectively")

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











<section class=" terms-condition-page">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <div class="terms-and-condition-heading">
                        <h1>Welcome to MallShark</h1>
                        <h2>Your Destination for Next-Level E-commerce!</h2>
                        <p>We're on a mission to transform the way you shop online, providing an enjoyable and hassle-free experience for both buyers and sellers. With a cutting-edge e-commerce platform, we've crafted a world where online shopping is simple, convenient, and downright enjoyable for all. Our commitment to simplifying the buying and selling process means e-commerce has never been easier!</p>
                    </div>
                    <div class=" terms-wrapper  mt-40 pt-30"> 
                        <h2>Benefits of Using MallShark</h2>
                        <h3> Lower Transaction Fees: Unlike traditional payment methods, MallShark offers significantly
                        lower transaction fees, allowing you to maximize your profits. Compare our fees to popular platforms:</h3>
                        <ul class="site-lists" >
                           
                            <li style="font-size: 18px!important;color:#073ba5"><svg xmlns="http://www.w3.org/2000/svg" width="6" height="6" fill="currentColor" class="bi bi-circle-fill" viewBox="0 0 16 16">
                                <circle cx="8" cy="8" r="8"/>
                            </svg> &nbsp;Shopify: Starting at $39/month and 2.9% + $0.30 per transaction</li>
                            <li style="font-size: 18px!important;color:#073ba5"><svg xmlns="http://www.w3.org/2000/svg" width="6" height="6" fill="currentColor" class="bi bi-circle-fill" viewBox="0 0 16 16">
                                <circle cx="8" cy="8" r="8"/>
                            </svg> &nbsp;Amazon: 10-15% per transaction</li>
                            <li style="font-size: 18px!important;color:#073ba5"><svg xmlns="http://www.w3.org/2000/svg" width="6" height="6" fill="currentColor" class="bi bi-circle-fill" viewBox="0 0 16 16">
                                <circle cx="8" cy="8" r="8"/>
                            </svg> &nbsp;Etsy: 6.5% per transaction</li>
                            <li style="font-size: 18px!important;color:#073ba5"><svg xmlns="http://www.w3.org/2000/svg" width="6" height="6" fill="currentColor" class="bi bi-circle-fill" viewBox="0 0 16 16">
                                <circle cx="8" cy="8" r="8"/>
                            </svg> &nbsp;eBay: 13.25% + $0.30 per transaction</li>
                            <li style="font-size: 18px!important;color:#eb2274; font-weight:bold"> <svg xmlns="http://www.w3.org/2000/svg" width="6" height="6" fill="currentColor" class="bi bi-circle-fill" viewBox="0 0 16 16">
                                <circle cx="8" cy="8" r="8"/>
                            </svg> &nbsp;MallShark: 4.5% <small>only</small></li>
                        </ul> 
 
                        <ul style="margin-top:40px;">
                            <li style="font-size: 16px!important; margin-bottom:10px"><b>Easy Integration</b>: Already have an e-commerce store? Excellent! With MallShark, integrating your shop is a breeze. In just a few clicks, connect your existing store to our platform and start accepting cryptocurrency payments right away.</li>
                            <li style="font-size: 16px!important; margin-bottom:10px"><b>Increased Revenue</b>: By offering a new payment option, you'll attract a broader customer base and boost your revenue streams. Embrace the future  of finance and watch your business grow over time.</li>
                            <li style="font-size: 16px!important; margin-bottom:10px"><b>Dual Currency Support</b>: Our platform caters to a wide range of customer preferences and payment options. Seamlessly accept both cryptocurrency  and fiat payments, ensuring a frictionless experience of your customers. </li>
                            <li style="font-size: 16px!important; margin-bottom:10px"><b>Increased Security</b>: MallShark prioritizes your business’s safety. We utilize advanced security protocols to protect your transactions against fraud and cyber security threats. Feel confident knowing your payments are secure.</li>
                        </ul>



                        <h2 style="margin-top:40px;">How to Join MallShark: Step-by-Step Instructions</h2>
                        <ul>
                        <li style="font-size: 16px!important; margin-bottom:10px"><b>Sign Up:</b> Visit our website at mallshark.net and click on the "Sign Up" button. Provide the necessary information to create your account.</li>
                        <li style="font-size: 16px!important; margin-bottom:10px"><b>Verify Your Account:</b> After signing up, you'll receive a verification email. Click on the link provided to verify your account and gain access to the MallShark platform.</li> 
                        <li style="font-size: 16px!important; margin-bottom:10px"><b>Set Up Your Shop:</b> Once logged in, navigate to the "Shop Settings" section and customize your shop's details, including business information, product listings, and pricing.</li> 
                        <li style="font-size: 16px!important; margin-bottom:10px"><b>Integrate Your Store:</b> If you already have an e-commerce Store, integrating it with MallShark is a breeze. Simply follow the step by step integration guide in the "Integration" tab of your MallShark dashboard.</li>
                        <li style="font-size: 16px!important; margin-bottom:10px"><b>Configure Payment Settings:</b> Customize your payment preferences, including the acceptance of cryptocurrencies and fiat currencies. Choose the coins you want to accept and set your desired conversion options.</li>
                        <li style="font-size: 16px!important; margin-bottom:10px"><b>Start Accepting Payments:</b> Congratulations! Your shop is now ready to accept cryptocurrency payments. Display the MallShark payment option  on your website and checkout pages, and watch as your revenue soars.</li>
                        </ul>    
                        




                         
                                     
                                    <div class="about-us-btn text-left mt-50">
                                        <p><i>Discover the future of e-commerce with MallShark. Join us today and be a part of the e-commerce revolution!</i></p>
                                        <a href="{{$url}}"><button class="btn btn-primary">Become a MallShark</button></a>
                                    </div>
                    </div>
                </div>
            </div> 
        </div>
    </section>
 

@endsection
