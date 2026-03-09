@extends('layouts.frontend-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Cart')
@section('google-analytics')
    @include('layouts.google-analytics')
@stop
<!-- Content Starts -->
<script src="{{ URL::asset('assets/js/cart.js', env('HTTPS_STATUS')) }}"></script>
<!--<link rel="stylesheet" href="{{ URL::asset('assets/css/ability-theme-style.css', env('HTTPS_STATUS')) }}" type="text/css">-->
<section class="main-container">

    <div class="checkout-custom-btn text-center">
        <button class="btn btn-primary">
            <a href="https://mallshark.net/search?keyword=">Continue Shopping</a>
            </button>
        </div>
    </div>



</section>
@section('meta')
    @include('layouts.cart-meta')
@stop
<!-- Content Ends-->

@endsection
