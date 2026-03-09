@extends('layouts.frontend-main')

@section('content')
@section('title', 'Woocommerce Terms and Conditions')
@section('description', 'Woocommerce Terms and Conditions')
@section('google-analytics')
@include('layouts.google-analytics')
@stop
<main>
    <style>
       
        @media only screen and (min-width : 767px) and (max-width : 10000px) {}
    </style>
    <section class="aboutus-container terms-condition-page">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <div class="terms-and-condition-heading">
                        <h1>Connect MallShark with WooCommerce to import WooCommerce products in MallShark</h1>
                        <p>The WooCommerce Product Import Integration is a powerful feature within Mallshark, designed to simplify and streamline the process of importing products from your WooCommerce store into your Mallshark. With this feature, you can effortlessly synchronize your product catalog, ensuring that your Mallshark store remains up-to-date with your WooCommerce inventory.   </p>
                    </div>
                    <div class=" terms-wrapper pos-rel mt-40 pt-30">
                        {{-- <div class="terms-shark-img">
                            <img src="{{ URL::asset('assets/images/Shark2.png') }}" alt="image" srcset="">
    
                        </div> --}}
                        <h1 class="mb-10">How to Create WooCommerce Consumer Key & Secret?</h1>
                        
                        <h2 class="mb-10">Step 1:</h2>
                        <p class="padding-right-term">Log in to your WordPress dashboard. There, select <b>WooCommerce</b> open <b>Settings</b></p>
                        <div class="WooCommerceTermsImgBox">
                            <img src="<?php echo URL::asset('assets/images/1.jpg');?>" alt="" />
                        </div>


                        <h2 class="mb-10 mt-3">Step 2: Open REST API </h2>
                        <p class="padding-right-term">In <b>Settings</b>, you will see a bar including General, Product, Tax, Shipping, Payments, Account & Privacy, Emails, and Advanced. Open <b>Advanced</b>.</p>
                        <div class="WooCommerceTermsImgBox">
                            <img src="<?php echo URL::asset('assets/images/2.jpg');?>" alt="" />
                        </div>


                        <h2 class="mb-10 mt-3">Step 3: Create API keys</h2>
                        <p class="padding-right-term">In <b>REST API</b>, click on <b>Add key</b> to create a new API key.<br>
                            A window will appear on the screen, and you have to enter the essential details, which are description, users, and permissions.<br>
                            
                            In the permission section select <b>Read/ Write</b></p>
                        <div class="WooCommerceTermsImgBox">
                            <img src="<?php echo URL::asset('assets/images/3.jpeg');?>" alt="" />
                        </div>
                        <p class="padding-right-term mt-3">When it is done, click on <b>Generate API key</b>.</p>


                        <h2 class="mb-10">Step 4: Get the customer key and consumer secret</h2>
                        <p class="padding-right-term">After generating the API key, a new window of key details will be displayed on the screen. You will see the customer key and consumer secret there.</p>
                        <div class="WooCommerceTermsImgBox">
                            <img src="<?php echo URL::asset('assets/images/4.jpg');?>" alt="" />
                        </div>
                        <p class="padding-right-term mt-3">You can copy these keys and secrets to use in the cases that require the WooCommerce API. Please enter it in the necessary field, and the system will request your URL.</p>


                        <h2 class="mb-10">Step 5: Get Site Address (URL)</h2>
                        <p class="padding-right-term">In in worpress <b>Settings</b>, Click on <b>General</b> and here you can find <b>Site Address (URL)</b></p>
                        <div class="WooCommerceTermsImgBox">
                            <img src="<?php echo URL::asset('assets/images/5.jpeg');?>" alt="" />
                        </div>

                        
                        <h2 class="mb-10 mt-3">Step 6: Connect MallShark with WooCommerce</h2>
                        <p class="padding-right-term">Navigate to the <b>Import WooCommerce</b> section under <b>My Store</b> in MallShark Dashboard, Paste <b>Site Address (URL)</b>, <b>Consumer Key</b> and <b>Secret Key</b> and Click <b>Connect</b> button</p>
                        <div class="WooCommerceTermsImgBox">
                            <img src="<?php echo URL::asset('assets/images/6.jpeg');?>" alt="" />
                        </div>
                        <p class="padding-right-term">Once it’s connected you can import your products, easily categorize and assign shipping profiles to individual items. You can also use our bulk action feature for efficient management. Simply select products, choose categories, set shipping profiles, and apply actions in just a few clicks.</p> 
                    </div>
                </div>
            </div>
            <div class="backgroung-change-log"></div>
        </div>
    </section>

    <style>
        .show-more-results {
            display: none;
        }
    </style>

</main>
@endsection
<!-- Content Ends-->