@extends('layouts.mobile-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Start Selling')
@section('description', 'Mallshark')
@section('google-analytics')
@include('layouts.google-analytics')
@stop

<section class="mnbnr">
    <div class="">
        <div class="">
            <div class="">

                <!-- Main Banner Starts -->
                <div class="jumbotron jmbo2">
                    <div class="container">
                        <h1 class="display-4">Give a purpose to your product</h1>
                        <h2>Explore creative projects and help bring bold new ideas into the world with the Mallshark app.</h2>
                        <br>
                        <p><a class="btn btn-primary btn-lg " href="{{URL::to('/store/create')}}" role="button">Start Selling</a></p>
                    </div>
                </div>
                <!-- Main Banner Ends -->
            </div>      
        </div>
    </div>

</section>
<main style="display:none;">
    <!-- Recent Products -->
    <div class="recnt">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="text-center comhd">A Mallshark prducts does more than just buying. It builds community around your work.</h2>
                    <!-- Listing Box Starts -->
                    <div class="row">         
                    </div>   
                    <!-- Listing Box Ends -->    
                </div>    
            </div>
        </div>
    </div>
    <!-- Recent Products Ends -->
    <!-- Most Popular Starts -->
    <div class="container">
        <div class="row">
            <div class="col-md-12">


                <!-- Listing Box Starts -->
                <div class="row tstmnl">


                    <div class="col text-center">
                        <p><img src="{{ asset('assets/images/rd1.jpg',env('HTTPS_STATUS')) }}" alt="brander image" title="brander image"></p><p>Ian William</p><p><img src="{{ asset('assets/images/lg1.jpg',env('HTTPS_STATUS')) }}" alt="brander image" title="brander image"></p>
                        <p class="tstxt">“The community we have all built in the past 30 days has been the greatest surprise.”</p>
                    </div>
                    <div class="col text-center">
                        <p><img src="{{ asset('assets/images/rd2.jpg',env('HTTPS_STATUS')) }}" alt="brander image" title="brander image"></p><p>Timmy Johan</p><p><img src="{{ asset('assets/images/lg2.jpg',env('HTTPS_STATUS')) }}" alt="brander image" title="brander image"></p>
                        <p class="tstxt">“creates a community of people interested in what you’re doing, and the community that’s created is important.”</p>    
                    </div>
                    <div class="col text-center">
                        <p><img src="{{ asset('assets/images/rd3.jpg',env('HTTPS_STATUS')) }}" alt="brander image" title="brander image"></p><p>Caprry Own</p><p><img src="{{ asset('assets/images/lg3.jpg',env('HTTPS_STATUS')) }}" alt="brander image" title="brander image"></p>
                        <p class="tstxt">“A powerful tool to connect with a community passionate about our work.”</p>    
                    </div> 


                </div>   
                <!-- Listing Box Ends -->     
            </div>      
        </div>




    </div> 
    <!-- Most Popular Ends -->


    <hr>


    <!-- How it works Content -->
    <div class="container ybx">
        <div class="row"> 
            <div class="col-md-12">
                <br>
                <h2 class="text-center">Why Join S6 Network?</h2>
                <br>
                <br>
                <div class="row">    
                    <div class="col-md-5">
                        <h3>Its just for creative projects.</h3>    
                        <p>We built Kickstarter as a tool for artists, designers, makers, musicians, and creative people everywhere. We’re proud to be the only platform that’s fully dedicated to building community around creative projects.</p>
                    </div>
                    <div class="col-md-2"> </div>
                    <div class="col-md-5"><h4>Our community wants to support you.</h4>    
                        <p>We built Kickstarter as a tool for artists, designers, makers, musicians, and creative people everywhere. We’re proud to be the only platform that’s fully dedicated to building community around creative projects.</p></div>       
                </div>
                <br>
                <div class="row">    
                    <div class="col-md-5">
                        <h4>All-or-nothing funding works.</h4>    
                        <p>We built Kickstarter as a tool for artists, designers, makers, musicians, and creative people everywhere. We’re proud to be the only platform that’s fully dedicated to building community around creative projects.</p>
                    </div>
                    <div class="col-md-2"> </div>
                    <div class="col-md-5"><h4>Its growing and improving every day.</h4>    
                        <p>We built Kickstarter as a tool for artists, designers, makers, musicians, and creative people everywhere. We’re proud to be the only platform that’s fully dedicated to building community around creative projects.</p></div>       
                </div>
                <br>
            </div> 
        </div>
    </div>
    <!-- How it works Content Ends -->


    <!-- Bottom Sign Up Section Starts -->
    <div class="btmsinup slrsubnr">

        <a href="{{URL::to('/store/create')}}" class="btn btn-primary">Start Selling Today</a>
        <p style="max-width:200px; margin:auto; margin-top:10px; font-size:12px">This way, you don’t get stuck without enough funds 
            to realize your project.</p>

    </div>
    <!-- Bottom Sign Up Section Ends -->  


    <!-- Vendor Rules Starts -->
    <div class="vndrls text-center">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h4>Vendor Rules</h4> 
                </div>
                <div class="col-md-4">
                    <ul>
                        <li>What can I use Kickstarter to fund?</li>
                        <li>Do I have to make a video?</li>
                        <li> What can I use Kickstarter to fund?</li> 
                        <li> What can I use Kickstarter to fund?</li> 
                        <li>Do I have to make a video?</li> 
                        <li> What can I use Kickstarter to fund?</li>  
                    </ul>    
                </div> 
                <div class="col-md-4">
                    <ul>
                        <li>What can I use Kickstarter to fund?</li>
                        <li>Do I have to make a video?</li>
                        <li> What can I use Kickstarter to fund?</li> 
                        <li> What can I use Kickstarter to fund?</li> 
                        <li>Do I have to make a video?</li> 
                        <li> What can I use Kickstarter to fund?</li>  
                    </ul>    
                </div> 
                <div class="col-md-4">
                    <ul>
                        <li>What can I use Kickstarter to fund?</li>
                        <li>Do I have to make a video?</li>
                        <li> What can I use Kickstarter to fund?</li> 
                        <li> What can I use Kickstarter to fund?</li> 
                        <li>Do I have to make a video?</li> 
                        <li> What can I use Kickstarter to fund?</li>  
                    </ul>    
                </div> 
            </div>    
        </div>
    </div>  
    <!-- Vendor Rules Ends -->  
</main>
@endsection