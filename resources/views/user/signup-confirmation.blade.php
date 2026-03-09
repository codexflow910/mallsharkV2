@extends('layouts.frontend-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Signup Conformation')

<main>

@if(session('dataLayerUserEvent'))
    <script>
        dataLayer = dataLayer || [];
        dataLayer.push(@json(session('dataLayerUserEvent')));
    </script>
@endif

<div class="container">       
    <div class="confirmation-heading-section signup-heading">
        <div class="heading">
            <h1>Welcome to MallShark</h1>
        </div>
        <div class="details">
            <h4 style="color:#0510FA !important;font-weight:500!important; "> <span style="font-weight:600!important;">Congratulations!</span> Check your email to confirm your new MallShark account!</h4>
            <p>Kindly review your spam/promotion folders if you are unable to locate the received email in your inbox.</p>
        </div>
    </div>
</div>

<div class="confirmation_images signup-confirmation">
    <div class="container">
    <div class="images-section">
        <div class="confirmaion-shark-img">
            <!-- <img src="{{ URL::asset('assets/images/Sharkflip.png') }}" alt="image" srcset=""> -->

            <div class="congragulationImg">
                <!-- <img src="{{ URL::asset('assets/images/welcomsignup.PNG') }}" alt="image" srcset=""> -->
            </div>
        </div>
        <div class="confirmaion-CoinBox-img">
            <!-- <img src="{{ URL::asset('assets/images/wooden-treasure-chest copy.png') }}" alt="image" srcset=""> -->
            
        </div>
    </div>
    </div>
</div>
</main>

@endsection
