@extends('layouts.mobile-main')
@section('content')
@section('title', 'Mallshark - The Best Crypto Enabled Shopping Portal')
<style>
    img.zanimals {
    width: 100%;
}  
</style>
<section>
    <div class="full-container gray-background ">
            <div class="row">
            <img  class="zanimal" src="{{ asset('assets/images/coming-soon-mobile.png',env('HTTPS_STATUS')) }}" alt="zanimals">
            </div>
       
    </div>
</section>
@endsection