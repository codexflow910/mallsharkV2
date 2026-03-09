@extends('layouts.new-main')
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
            <img class="zanimals" src="{{ asset('assets/images/coming-soon.png',env('HTTPS_STATUS')) }}" alt="zanimals">

            </div>
       
    </div>
</section>
@endsection