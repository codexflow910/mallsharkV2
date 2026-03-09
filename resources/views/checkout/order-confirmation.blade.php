@extends('layouts.frontend-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Checkout Confirmation')
@section('google-analytics')
@stop
<main>

<div class="container">
    <div class="confirmation-heading-section">
        <div class="heading">
            <h1>Your Order Has Been Sent!</h1>
        </div>
        <div class="details">
            <h4 class="confirmation-detail-text"> <span style="font-weight:600">Congratulations!</span> Check your email for your order details! If you don’t see an email in your inbox, please check your spam or junk folder—sometimes emails like to play hide and seek!

            </h4>
        </div>
    </div>
</div>

<div class="confirmation_images">
    <div class="container">
    <div class="images-section">
        <div class="confirmaion-shark-img">
            <img src="{{ URL::asset('assets/images/Sharkflip.png') }}" alt="image" srcset="">

            <div class="congragulationImg">
                <img src="{{ URL::asset('assets/images/congragulation-img.PNG') }}" alt="image" srcset="">

            </div>
        </div>
        <div class="confirmaion-CoinBox-img">
            <img src="{{ URL::asset('assets/images/wooden-treasure-chest copy.png') }}" alt="image" srcset="">
            
        </div>
    </div>
    </div>
</div>
</main>

@endsection
