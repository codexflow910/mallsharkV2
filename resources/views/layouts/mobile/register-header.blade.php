<!doctype html>
<html lang="en">
    <head>
            <title>@yield('title')</title>
        <meta name="title" content="@yield('title')"/>
        <meta name="description" content="@yield('description')"/>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link rel="shortcut icon" type="image/x-icon" href="{{ URL::asset('assets/images/mallsharkicon.png') }}">
        <link rel='canonical' href="{{url()->current()}}" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
        <link rel="stylesheet" href="{{asset('assets/css/style.css',env('HTTPS_STATUS'))  }}" type="text/css">
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Roboto:400,700" rel="stylesheet">

        <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <link rel="stylesheet" href="{{ URL::asset('assets/css/custom.css' ,env('HTTPS_STATUS')) }}" type="text/css">

        <link rel="stylesheet" href="{{ URL::asset('assets/css/theme-style.css' ,env('HTTPS_STATUS')) }}" type="text/css">
        <link rel="stylesheet" href="{{ URL::asset('assets/css/reject.css' ,env('HTTPS_STATUS')) }}" type="text/css">
        <link rel="stylesheet" href="{{ URL::asset('assets/css/ishi-style.css' ,env('HTTPS_STATUS')) }}" type="text/css">
        <link rel="stylesheet" href="{{asset('assets/css/checkout.css',env('HTTPS_STATUS'))  }}" type="text/css"> 
        <!--Mobile Style-->        
        <link rel="stylesheet" href="{{ URL::asset('assets/css/mobile-style.css' ,env('HTTPS_STATUS')) }}" type="text/css">      
        <link rel="stylesheet" href="{{asset('assets/css/checkout.css',env('HTTPS_STATUS'))  }}" type="text/css">   

        <!--HEADER SCRIPTS -->
        <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <script src="{{ URL::asset('assets/js/autocomplete.min.js' ,env('HTTPS_STATUS')) }}"></script>
        <script src="{{ URL::asset('assets/js/reject.js' ,env('HTTPS_STATUS')) }}"></script>
    </head>
    <header class="d-flex justify-content-between bd-highlight ">
        <input type="hidden" name="site_url" id="site_url" value="<?php echo URL::to('/'); ?>">
        <div class="col text-center main-logo register-logo"><a class="navbar-brand text-center" href="{{URL::to('/'),env('HTTPS_STATUS')}}"><img src="{{ asset('assets/images/MallShark-Beta-new.svg',env('HTTPS_STATUS')) }}" alt="MallShark icon" title="MallShark icon"> </a></div>
    </header>
    <?php rediredctUrls(); ?>

