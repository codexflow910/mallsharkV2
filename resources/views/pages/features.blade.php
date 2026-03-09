@extends('layouts.frontend-main')
@section('content')
@section('title', 'features')
<main>


    <style>
        @media only screen and (min-width : 767px) and (max-width : 10000px) {}
    </style>




<section class=" terms-condition-page ftrset">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <div class="terms-and-condition-heading">
                        <h1>Why MallShark?</h1> 
                        <h4>It’s the wave of the future.</h4>
                    </div>
                    <div class=" terms-wrapper faqsec  mt-40 pt-30"> 
                        <div class="container">
        <div class="row">
        <div class="col-sm-12">            
                            <div class="box-text-with-icons">
                                <div class="icon-box-wrapper">
                                    <div class="icon">
                                        <img src="{{ URL::asset('assets/images/secure.PNG') }}" alt="image" srcset="">

                                    </div>
                                    <div class="icon-content">
                                        <h5>Secure Transactions</h5>
                                        <p>MallShark is powered by blockchain technology, which ensures secure and transparent transactions. This means that all transactions made on the platform are immutable, and cannot be altered or tampered with.</p>
                                    </div>
                                </div>
                                <div class="icon-box-wrapper">
                                    <div class="icon">
                                        <img src="{{ URL::asset('assets/images/secure2.PNG') }}" alt="image" srcset="">

                                    </div>
                                    <div class="icon-content">
                                        <h5>Seamless Crypto Shopping Experience</h5>
                                        <p>MallShark is the first-ever crypto e-commerce platform that accepts cryptocurrency payments alongside fiat payments. This makes it more convenient for customers who hold cryptocurrencies to shop without having to convert them to fiat first.</p>
                                    </div>
                                </div>
                                <div class="icon-box-wrapper">
                                    <div class="icon">
                                        <img src="{{ URL::asset('assets/images/dollar.PNG') }}" alt="image" srcset="">

                                    </div>
                                    <div class="icon-content">
                                        <h5>Lower Transaction Fees</h5>
                                        <p>MallShark’s transaction fees are lower compared to traditional e-commerce platforms. This is because blockchain technology eliminates intermediaries, resulting in lower transaction fees.</p>
                                    </div>

                                </div>
                                <div class="icon-box-wrapper">
                                    <div class="icon">
                                        <img src="{{ URL::asset('assets/images/decentralize.PNG') }}" alt="image" srcset="">

                                    </div>
                                    <div class="icon-content">
                                        <h5>Decentralized Platform</h5>
                                        <p>MallShark is a decentralized platform, meaning that it is not controlled by any central authority. This ensures that the platform is resistant to censorship and provides users with more privacy.</p>
                                    </div>

                                </div>
                                <div class="icon-box-wrapper">
                                    <div class="icon">
                                        <img src="{{ URL::asset('assets/images/chart.PNG') }}" alt="image" srcset="">

                                    </div>
                                    <div class="icon-content">
                                        <h5>Smart Contracts</h5>
                                        <p>MallShark uses smart contracts to facilitate transactions on the platform. Smart contracts are self-executing contracts that automate the process of verifying, executing, and enforcing the terms of an agreement. This ensures that transactions are transparent, secure, and tamper-proof.</p>
                                    </div> 
                                </div> 
                                                        
                            </div>
                                           <div>
                                                <div style="margin-top: 30px;">
                                                <?php
                                                $url = '';
                                                if (!Session::has('userData')) {
                                                    $url = URL::to('/login');
                                                } else {
                                                    $userid = isset(Session::get('userData')['user_id']) ? Session::get('userData')['user_id'] : "";
                                                    $storeData = getStoreDataByUserId($userid);
                                                    if (empty($storeData)) {
                                                        $url = URL::to('/sellonmallshark');
                                                    } else {
                                                        $url = URL::to('/store-dashboard');
                                                    }
                                                }
                                                ?>
                                                <a href="{{$url}}"> <button class="btn btn-primary">Become a Seller</button></a>
                                                </div>
                                             </div>
        </div>
         
    </div>
                      
 
                       
                    </div>
                </div>
            </div> 
        </div>
    </section> 

</main>
@endsection
<!-- Content Ends-->