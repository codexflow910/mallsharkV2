
@extends('layouts.new-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Mint NFTs')
@section('google-analytics')
{{-- <script src="https://code.jquery.com/jquery-3.5.1.min.js" crossorigin="anonymous"></script>
<script src="https://js.stripe.com/v3/"></script> --}}
<link href="{{ mix('css/app.css') }}" rel="stylesheet">
@include('layouts.google-analytics')
@stop


<script src="{{ mix('js/app.js') }}" defer></script>
<div class="large-loader">
    <span>
        <img src=" {{ asset('assets/images/loader-large.svg',env('HTTPS_STATUS')) }}" class="img-fluid" alt="load icon" title="load icon">
    </span>
</div>
<div id="test"></div>


@endsection