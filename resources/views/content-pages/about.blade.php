@extends('layouts.frontend-main')
@section('content')
@section('title', 'About Us')
@section('google-analytics')
@include('layouts.google-analytics')
@stop
<main>
 

    <style>
        @media only screen and (min-width : 767px) and (max-width : 10000px) {

        }
    </style>









<section class=" terms-condition-page">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <div class="terms-and-condition-heading">
                        <h1>About MallShark</h1>
                        <p>It’s the wave of the future.</p>
                    </div>
                    <div class=" terms-wrapper  mt-40 pt-30"> 
                        <h2>Who We Are </h2>
                        <p>At MallShark, we are passionate about transforming the way people shop online. Our cutting-edge e-commerce platform is designed to provide an enjoyable and hassle-free shopping experience, catering to both buyers and sellers. With a mission to simplify the online buying and selling process, we aim to make e-commerce a breeze for everyone.</p>
                         

                        <h2 class="mb-10">Low Fees, Maximum Value</h2>
                        <p>We believe that our success lies in the success of our sellers. That’s why we are committed to offering significantly lower fees compared to other leading e-commerce platforms. By reducing costs and maximizing your earnings, we empower you to grow your business and reach new heights.</p>
                        <h2>Simplified Shopping, Elevated Experience</h2> 
                        <p>We envision a world where online shopping is simple, convenient, and enjoyable for all. With a user-friendly interface, our platform ensures a seamless shipping experience. Whether you are a buyer searching for the latest trends or a seller showcasing your products, MallShark has got you covered.</p>
                        <h2>Fast Growing E-commerce Platform</h2> 
                        <p>MallShark is experiencing rapid growth in the e-commerce industry, attracting a large and diverse community of buyers and sellers. Our user base is expanding exponentially, providing an ever-growing marketplace for unique products and innovative sellers. With a strong focus on customer satisfaction and seller success, MallShark continues to solidify its position as a prominent player in the e-commerce space.</p>
                        <h2>Unique Products Showcase</h2> 
                        <p>We take pride in offering a wide array of unique products that you won’t find on traditional e-commerce platforms. Our sellers are encouraged to bring their one-of-a-kind items to the platform, creating a diverse and fascinating selection for buyers. Whether you’re searching for handcrafted treasures, limited edition items, or niche products, MallShark is the go-to destination for discovering extraordinary goods.</p>
                        <h2>Secure Transactions, Peace of Mind</h2> 
                        <p>Your safety is our top priority. At MallShark, we go the extra mile to protect your sensitive information, ensuring a worry-free shopping experience for every user. Feel confident in every transaction, knowing that your data and payments are in safe hands.</p>
                        <h2>Tailored of Your Success</h2> 
                        <p> We embrace the power of customization. At MallShark, you have the freedom to personalize your online store to reflect your unique brand identity. Stand out from the crowd, present your products in the best light, and connect with your customers on a deeper level.</p>
                        <h2>Expert Support, Every Step of the Way</h2> 
                        <p> We understand the challenges of starting and growing an e-commerce venture. That’s why our team of experts is always available to assist you. From setting up your store to optimizing your listings, our support team is dedicated to helping you succeed.</p>
                        <h2>Join the MallShark Community</h2> 
                        <p>By joining MallShark, you become a part of our vibrant community of sellers and buyers. Connect with like-minded entrepreneurs, exchange valuable insights, and draw inspiration from success stories. Together, we believe we can achieve more.</p>
                        <h2></h2> 
                        <h2>Our Future Goals</h1>
                        <h2></h2>  
                        <p>MallShark is committed to shaping the future of e-commerce. By embracing cryptocurrency, we aim to make transactions quicker, more secure, and easier than ever before. Our vision for the future includes staying ahead of the technological curve and providing innovative solutions to enhance your shopping experience by seamlessly integrating cryptocurrency transactions.</p>
                        <h2>Join the MallShark Movement </h2> 
                        <p>We invite you to join us on this transformative journey. Whether you are a buyer seeking the latest trends or a seller looking to expand your business, MallShark is here to support you. Together let's reshape the world of e-commerce and create a future where shopping online is effortless and rewarding.</p>
                       
                        <h2></h2>              
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
                                    <div class="about-us-btn text-left">
                                        <p><i>Discover the future of e-commerce with MallShark. Join us today and be a part of the e-commerce revolution!</i></p>
                                        <a href="{{$url}}"><button class="btn btn-primary">Become a Seller</button></a>
                                    </div>
                    </div>
                </div>
            </div> 
        </div>
    </section>
 

</main>
@endsection
<!-- Content Ends-->




