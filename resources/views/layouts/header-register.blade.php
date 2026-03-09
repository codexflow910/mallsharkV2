<!doctype html>
<html lang="en">
    <head>
        <title>@yield('title')</title>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link rel="shortcut icon" type="image/x-icon" href="{{ URL::asset('assets/images/mallsharkicon.png') }}">
        <meta name="title" content="@yield('title')"/>
        <meta name="description" content="@yield('description')"/>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" type="text/css">
        <link rel="stylesheet" href="{{ URL::asset('assets/css/register.css',env('HTTPS_STATUS')) }}" type="text/css">
        <link rel="stylesheet" href="{{ URL::asset('assets/css/theme-style.css' ,env('HTTPS_STATUS')) }}" type="text/css">
        <link rel="stylesheet" href="{{asset('assets/css/style.css',env('HTTPS_STATUS'))  }}" type="text/css"> 
        <link rel="stylesheet" href="{{asset('assets/css/checkout.css',env('HTTPS_STATUS'))  }}" type="text/css"> 
        <link href="https://fonts.googleapis.com/css?family=Roboto:400,700" rel="stylesheet">  
        <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
        <link type="text/css" rel="stylesheet" href="https://cdn.jsdelivr.net/jquery.jssocials/1.4.0/jssocials.css" />
        <link type="text/css" rel="stylesheet" href="https://cdn.jsdelivr.net/jquery.jssocials/1.4.0/jssocials-theme-flat.css" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
        <script type="text/javascript" src="{{ URL::asset('assets/js/sweetalert.js') }}"></script>
    <link rel="stylesheet" href="{{ URL::asset('assets/css/sweetalert.css') }}">
       <style>
        body {
          padding-top: 0px !important;
        }
        header .main-logo a {
            padding-top: 0;
            margin-top: 0px;
        }
        @media only screen and (max-width: 768px){
            body {
                padding-top: 0px !important;
            }
        }

       </style>

    </head>
    <body class="mall-shark-section" >
    <header class="d-flex justify-content-between bd-highlight mallshark-logo">
        <input type="hidden" name="site_url" id="site_url" value="<?php echo URL::to('/'); ?>">
        <div class="col text-center main-logo register-forgort-logo"><a class="navbar-brand text-center" href="{{URL::to('/'),env('HTTPS_STATUS')}}">
        <img src="{{ " alt="MallShark icon" title="MallShark icon" style="width:120px"> </a></div>
    </header>


 <?php rediredctUrls(); ?>

  @yield('content')

  @include('layouts.new-footer')
