@extends('layouts.mobile-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'No Record Found')

    @include('Store.mobile.sidebar')
<div class="col-12 text-center no-record-found">
    <div class="inr-bdy-fnd" style=" background-color:#f7f7f7; padding:30px; margin-top:30px; ">
        <p><img src="{{asset('assets/images/glass.png')}}" alt="not find any results matching" title="not find any results matching"></p>
        <h2 style="color:#a2a2a2;font-size:30px; font-weight:400">Sorry!</h2>
        <p style="color:#a2a2a2;font-size:18px ">We could not find any results matching.<br>
            Please try another search term or adjust the search filters.</p>
        <p></p>
    </div>
</div>
@endsection