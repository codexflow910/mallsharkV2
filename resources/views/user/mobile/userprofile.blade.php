@extends('layouts.mobile-main')
@section('content')
@section('title', 'User Profile')

<?php
$url_image = '';
$name = '';
$streetAddress = '';
$userCity = '';
$userState = '';
$userCountry = '';
$address = '';
if (!empty($userData)) {
    $user_avatar = $userData->user_avatar;
    $url_image = getAttachmentUrlById((int) $user_avatar, 'avatar');
    $name = $userData->name;
    $streetAddress = $userData->physical_address;
    $userCity = $userData->user_city_id;
    $userCountry = getCountryById($userData->user_country_id);
    $userCountry = $userCountry['name'];
    $userState = getStatebyId($userData->user_state_id);
    $userState = $userState['state_name'];
 $joinedDate= strtotime($userData->created_at);
}
?>
<section class="user-profile-mbt ">
    <div class="strhd header-strip">
        <div class="container-fluid">

            <div class="row d-flex">

                <div class="hdlft col-4  ">
                    <div class="left-stripping-span common-span-style">
                        <p class="small" style="margin-bottom:0">Supporting: 23</p>
                        <p class="small" style="margin-bottom:0">From: Russia</p>
                        <p class="small" style="margin-bottom:0">Since: <?php  echo $date = date('M Y', $joinedDate); ?></p>  
                    </div>        
                </div>

                <div class="col-4 hdcnt">
                    <div class="box-cont-header text-center">
                        <span class="circle-image"><img class="img-fluid" src="{{$url_image}}" alt="{{$name}}" title="{{$name}}"></span> 
                    </div>
                </div>

                <!-- <div class="  col-4 text-right"> 
                    <button class="btn btn-primary btn-simple" style="margin-top:10px">Contact Me</button>
                </div> -->

            </div>

            <div class="row">
                <div class="col-12 nopad text-center">
                    <div class="name-user">
                        <h4>{{$name}}</h4>
                        <p class="city-county">
                            <?php
                            echo isset($streetAddress) ? $streetAddress . '<br> ' : '';
                            echo isset($userCity) ? $userCity . '  ' : '';
                            echo isset($userState) ? $userState . '  ' : '';
                            echo isset($userCountry) ? $userCountry : '';
                            ?>


                        </p>
                        <div class="score-sec">
                            <a href="<?php echo URL::to('/user-account'); ?>">Edit profile</a>
                        </div>
                    </div> 
                </div> 
            </div> 
        </div>
    </div>
    <!-- <section class="user-profile-mbt ">    
        <div class="tabs-data" style="margin-top:8px">
            <div class="container">
                <div class="row">
                    <div class="col-sm-12 nopad">
                        <ul class="nav nav-tabs custom-tabs justify-content-center">
                            <//?php if (isset($userData->about)) { ?>
                                <li class="nav-item">
                                    <a data-toggle="tab" href="#about-tab" class="nav-link active">About</a>
                                </li>
                            <//?php } ?>
                            <li class="nav-item">
                                <a data-toggle="tab" href="#supporting-tab" class="nav-link">Herd</a>
                            </li>
                            <li class="nav-item">
                                <a data-toggle="tab" href="#promoting-tab" class="nav-link">Associate</a>
                            </li>
                            <li class="nav-item">
                                <a data-toggle="tab" href="#review-tab" class="nav-link">Reviews</a>
                            </li>
                            <li class="nav-item">
                                <a data-toggle="tab" href="#wallet-tab" class="nav-link">Your Pocket</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="tab-content">
                    <div id="about-tab" class="tab-pane in mt-30 active"> 
                        <div class="col-sm-7">
                            <h2 class="mb-25">About</h2>
                            <p>
                                <//?php
                               // echo isset($userData->about) ? $userData->about : '';
                                ?>   
                            </p>
                        </div>
                    </div>

                    <div id="supporting-tab" class="tab-pane in mt-30">  
                        <div class="row brands-logoes"> 

                            <div class="col-md-2 text-center">
                                <span class="anml"> <span class="pwr">100</span> <span class="prg"><span class="up">▲</span> 100%</span><a href="javascript:;">
                                        <img class="img-fluid" src="images/icon1.png" alt="Project">
                                    </a>
                                </span>
                                <p class="text-center" style="margin-top:20px">23 Products</p>
                                <a href="#" class="btn btn-primary btn-simple btn-block " style="padding:5px 0" data-toggle="modal" data-target="#myModal">Get Associate</a> 
                                <p class="text-center"><img src="{{asset('assets/images/logo.png',env('HTTPS_STATUS'))}}"></p> 
                            </div>
                            <div class="col-md-2 text-center">
                                <span class="anml"> <span class="pwr">100</span> <span class="prg"><span class="up">▲</span> 100%</span><a href="javascript:;">
                                        <img class="img-fluid" src="{{asset('assets/images/icon2.png',env('HTTPS_STATUS'))}}" alt="Project">
                                    </a>
                                </span>
                                <p class="text-center" style="margin-top:20px">23 Products</p>
                                <a href="#" class="btn btn-primary btn-simple btn-block " style="padding:5px 0" data-toggle="modal" data-target="#myModal2">Get Associate</a> 
                                <p class="text-center"><img src="{{asset('assets/images/logo.png',env('HTTPS_STATUS'))}}"></p>  
                            </div>
                            <div class="col-md-2 text-center">
                                <span class="anml"> <span class="pwr">100</span> <span class="prg"><span class="up">▲</span> 100%</span><a href="javascript:;">
                                        <img class="img-fluid" src="{{asset('assets/images/icon3.png',env('HTTPS_STATUS'))}}" alt="Project">
                                    </a>
                                </span> 
                                <p class="text-center" style="margin-top:20px">23 Products</p>
                                <a href="#" class="btn btn-primary btn-simple btn-block " style="padding:5px 0" data-toggle="modal" data-target="#myModal3">Get Associate</a> 
                                <p class="text-center"><img src="{{asset('assets/images/logo.png',env('HTTPS_STATUS'))}}"></p> 
                            </div>
                            <div class="col-md-2 text-center">
                                <span class="anml"> <span class="pwr">100</span> <span class="prg"><span class="up">▲</span> 100%</span><a href="javascript:;">
                                        <img class="img-fluid" src="{{asset('assets/images/icon4.png',env('HTTPS_STATUS'))}}" alt="Project">
                                    </a>
                                </span>
                                <p class="text-center" style="margin-top:20px">23 Products</p>
                                <a href="#" class="btn btn-primary btn-simple btn-block " style="padding:5px 0" data-toggle="modal" data-target="#myModal4">Get Associate</a> 
                                <p class="text-center"><img src="{{asset('assets/images/logo.png',env('HTTPS_STATUS'))}}"></p> 
                            </div>
                            <div class="col-md-2 text-center">
                                <span class="anml"> <span class="pwr">100</span> <span class="prg"><span class="up">▲</span> 100%</span><a href="javascript:;">
                                        <img class="img-fluid" src="{{asset('assets/images/icon5.png',env('HTTPS_STATUS'))}}" alt="Project">
                                    </a>
                                </span>
                                <p class="text-center" style="margin-top:20px">23 Products</p>
                                <a href="#" class="btn btn-primary btn-simple btn-block " style="padding:5px 0" data-toggle="modal" data-target="#myModal5">Get Associate</a> 
                                <p class="text-center"><img src="{{asset('assets/images/logo.png',env('HTTPS_STATUS'))}}"></p>
                            </div>
                            <div class="col-md-2 text-center">
                                <span class="anml"> <span class="pwr">100</span> <span class="prg"><span class="up">▲</span> 100%</span><a href="javascript:;">
                                        <img class="img-fluid" src="{{asset('assets/images/icon6.png',env('HTTPS_STATUS'))}}" alt="Project">
                                    </a>
                                </span>
                                <p class="text-center" style="margin-top:20px">23 Products</p>
                                <a href="#" class="btn btn-primary btn-simple btn-block " style="padding:5px 0">Get Associate</a>
                                <p class="text-center"><img src="{{asset('assets/images/logo.png',env('HTTPS_STATUS'))}}"></p>    
                            </div>

                        </div> 
                    </div>
                    <div id="promoting-tab" class="tab-pane  in mt-30"> 

                         Nav tabs -->
                       <!-- <ul class=" prd nav nav-pills nav-fill">
                            <li class="nav-item col-6 nopad">
                                <a class="nav-link nopad active" data-toggle="tab" href="#home">Associate With You</a>
                            </li>
                            <li class="nav-item col-6 nopad">
                                <a class="nav-link nopad" data-toggle="tab" href="#menu1">You Associate With</a>
                            </li>

                        </ul> -->

                        <!-- Tab panes -->
                        <!-- <div class="tab-content">
                            <div id="home" class="container tab-pane active"><br><br><br> 
                                <div class="row ">
                                    <div class="col-md-2 text-center">
                                        <span class="anml"> <span class="pwr">100</span> <span class="prg"><span class="up">▲</span> 100%</span><a href="javascript:;">
                                                <img class="img-fluid" src="{{asset('assets/images/icon1.png',env('HTTPS_STATUS'))}}" alt="Project">
                                            </a>
                                        </span>
                                        <p class="text-center" style="margin-top:20px">23 Products</p>
                                        <a href="#" class="btn btn-primary btn-simple btn-block " style="padding:5px 0" data-toggle="modal" data-target="#myModal">Get Associate</a> 
                                        <p class="text-center"><img src="{{asset('assets/images/logo.png',env('HTTPS_STATUS'))}}"></p> 
                                        <span class="arwp"><img src="{{asset('assets/images/arrow-p.png',env('HTTPS_STATUS'))}}"></span>     
                                    </div>

                                    <div class="col-md-10">

                                        <span class="usrmg text-center">
                                            <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS'))}}">
                                                Tommy</a>    
                                        </span>
                                        <span class="usrmg text-center">
                                            <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS'))}}">
                                                Tommy</a>    
                                        </span>
                                        <span class="usrmg text-center">
                                            <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS'))}}">
                                                Tommy</a>    
                                        </span>
                                        <span class="usrmg text-center">
                                            <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS'))}}">
                                                Tommy</a>    
                                        </span>
                                        <span class="usrmg text-center">
                                            <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS'))}}">
                                                Tommy</a>    
                                        </span>
                                        <span class="usrmg text-center">
                                            <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS'))}}">
                                                Tommy</a>    
                                        </span>
                                        <span class="usrmg text-center">
                                            <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS'))}}">
                                                Tommy</a>    
                                        </span>
                                        <span class="usrmg text-center">
                                            <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS'))}}">
                                                Tommy</a>    
                                        </span>
                                        <span class="usrmg text-center">
                                            <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS'))}}">
                                                Tommy</a>    
                                        </span>
                                        <span class="usrmg text-center">
                                            <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS'))}}">
                                                Tommy</a>    
                                        </span>
                                        <span class="usrmg text-center">
                                            <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS'))}}">
                                                Tommy</a>    
                                        </span>
                                        <span class="usrmg text-center">
                                            <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS'))}}">
                                                Tommy</a>    
                                        </span>
                                        <span class="usrmg text-center">
                                            <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS'))}}">
                                                Tommy</a>    
                                        </span>
                                        <span class="usrmg text-center">
                                            <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS'))}}">
                                                Tommy</a>    
                                        </span>
                                        <span class="usrmg text-center">
                                            <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS'))}}">
                                                Tommy</a>    
                                        </span>
                                        <span class="usrmg text-center">
                                            <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS'))}}">
                                                Partheon</a>    
                                        </span>
                                        <span class="usrmg text-center">
                                            <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS'))}}">
                                                Hussain</a>    
                                        </span>
                                        <span class="usrmg text-center">
                                            <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS'))}}">
                                                Mark</a>    
                                        </span>
                                        <span class="usrmg text-center">
                                            <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS'))}}">
                                                Juliasanth</a>    
                                        </span>
                                        <span class="usrmg text-center">
                                            <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS'))}}">
                                                Tommy</a>    
                                        </span>
                                    </div> 
                                </div> 
                                <hr>
                                <div class="row ">
                                    <div class="col-md-2 text-center">
                                        <span class="anml"> <span class="pwr">98</span> <span class="prg"><span class="up">▲</span> 100%</span><a href="javascript:;">
                                                <img class="img-fluid" src="{{asset('assets/images/icon2.png',env('HTTPS_STATUS'))}}" alt="Project">
                                            </a>
                                        </span>
                                        <p class="text-center" style="margin-top:20px">23 Products</p>
                                        <a href="#" class="btn btn-primary btn-simple btn-block " style="padding:5px 0" data-toggle="modal" data-target="#myModal">Get Associate</a> 
                                        <p class="text-center"><img src="{{asset('assets/images/logo.png',env('HTTPS_STATUS'))}}"></p> 
                                        <span class="arwp"><img src="{{asset('assets/images/arrow-p.png',env('HTTPS_STATUS'))}}"></span>     
                                    </div>

                                    <div class="col-md-10">

                                        <span class="usrmg text-center">
                                            <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS'))}}">
                                                Tommy</a>    
                                        </span>
                                        <span class="usrmg text-center">
                                            <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS'))}}">
                                                Tommy</a>    
                                        </span>
                                        <span class="usrmg text-center">
                                            <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS'))}}">
                                                Tommy</a>    
                                        </span>
                                        <span class="usrmg text-center">
                                            <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS'))}}">
                                                Tommy</a>    
                                        </span>
                                        <span class="usrmg text-center">
                                            <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS'))}}">
                                                Tommy</a>    
                                        </span>
                                        <span class="usrmg text-center">
                                            <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS'))}}">
                                                Tommy</a>    
                                        </span>
                                        <span class="usrmg text-center">
                                            <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS'))}}">
                                                Tommy</a>    
                                        </span>
                                        <span class="usrmg text-center">
                                            <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS'))}}">
                                                Tommy</a>    
                                        </span>
                                        <span class="usrmg text-center">
                                            <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS'))}}">
                                                Tommy</a>    
                                        </span>
                                        <span class="usrmg text-center">
                                            <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS'))}}">
                                                Tommy</a>    
                                        </span>
                                        <span class="usrmg text-center">
                                            <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS'))}}">
                                                Tommy</a>    
                                        </span>
                                        <span class="usrmg text-center">
                                            <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS'))}}">
                                                Tommy</a>    
                                        </span>
                                        <span class="usrmg text-center">
                                            <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS'))}}">
                                                Tommy</a>    
                                        </span>
                                        <span class="usrmg text-center">
                                            <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS'))}}">
                                                Tommy</a>    
                                        </span>
                                        <span class="usrmg text-center">
                                            <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS'))}}">
                                                Tommy</a>    
                                        </span>
                                        <span class="usrmg text-center">
                                            <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS'))}}">
                                                Tommy</a>    
                                        </span>
                                        <span class="usrmg text-center">
                                            <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS'))}}">
                                                Tommy</a>    
                                        </span>
                                        <span class="usrmg text-center">
                                            <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS'))}}">
                                                Tommy</a>    
                                        </span>
                                        <span class="usrmg text-center">
                                            <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS'))}}">
                                                Tommy</a>    
                                        </span>
                                        <span class="usrmg text-center">
                                            <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS'))}}">
                                                Tommy</a>    
                                        </span>

                                    </div> 
                                </div>
                            </div> -->
                            <!-- <div id="menu1" class="container tab-pane fade"><br><br><br> 
                                <div class="row ">

                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="col-md-4 text-center">
                                                <span class="anml"> <span class="pwr">100</span> <span class="prg"><span class="up">▲</span> 100%</span><a href="javascript:;">
                                                        <img class="img-fluid" src="{{asset('assets/images/icon2.png',env('HTTPS_STATUS'))}}" alt="Project">
                                                    </a>
                                                </span>
                                                <p class="text-center" style="margin-top:20px">23 Products</p>
                                                <p class="text-center"><img src="{{asset('assets/images/logo.png',env('HTTPS_STATUS'))}}"></p> 
                                            </div>
                                            <div class="col-md-8" style="padding-top:15px">
                                                <p style="margin-bottom:5px">Last Earning: <b>10 ZIT</b></p>
                                                <p style="margin-bottom:5px">Total Earnign: <b>451 ZIT</b> </p>        
                                                <p style="margin-bottom:15px">Progress: <b><span class="up" style="color:#7ce600">▲</span>10% Up Last Week</b></p>
                                                <a href="#" class="btn btn-primary btn-simple" data-toggle="modal" data-target="#myModal">Add More ZIT</a> 
                                            </div>    
                                        </div>
                                    </div> -->
                                    <!-- <div class="col-md-6">
                                        <div class="row">
                                            <div class="col-md-4 text-center">
                                                <span class="anml"> <span class="pwr">100</span> <span class="prg"><span class="up">▲</span> 100%</span><a href="javascript:;">
                                                        <img class="img-fluid" src="{{asset('assets/images/icon1.png',env('HTTPS_STATUS'))}}" alt="Project">
                                                    </a>
                                                </span>
                                                <p class="text-center" style="margin-top:20px">23 Products</p>
                                                <p class="text-center"><img src="{{asset('assets/images/logo.png',env('HTTPS_STATUS'))}}"></p> 
                                            </div>
                                            <div class="col-md-8" style="padding-top:15px">
                                                <p style="margin-bottom:5px">Last Earning: <b>10 ZIT</b></p>
                                                <p style="margin-bottom:5px">Total Earnign: <b>451 ZIT</b> </p>        
                                                <p style="margin-bottom:15px">Progress: <b><span class="up" style="color:#7ce600">▲</span>10% Up Last Week</b></p>
                                                <a href="#" class="btn btn-primary btn-simple" data-toggle="modal" data-target="#myModal">Add More ZIT</a> 
                                            </div>    
                                        </div>
                                    </div>  -->
                                <!-- </div> 
                            </div> 
                        </div>                 
                    </div> -->

                    <!-- menu2 -->
<!-- 
                    <div id="review-tab" class="tab-pane  in mt-30">
                        <img src="http://127.0.0.1:8000/assets/images/review-tab-one.png" alt="image" class="">

                    </div> -->
                    <!-- Pocket Starts --> 
                    <!-- <div id="wallet-tab" class="tab-pane  in mt-30">
                        <div class="bxst">
                            <div class="row">
                                <div class="col-md-6  text-center"><span style="line-height:38px"><span>Zit Available: 156</span>
                                        <span class="clrgry"> &nbsp;&nbsp; | &nbsp;&nbsp;</span>
                                        <span>Zit Value: $0.1</span></span></div>
                                <div class="col-md-6  text-center"><a class="btn btn-primary" style="color:#fff">Add More ZIT</a> </div> 
                            </div> 
                        </div>      -->
                        <!-- <div class="table-responsive thtbl" style="margin-top:30px" >
                            <h3>Transaction History</h3> 
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col">Date</th>
                                        <th scope="col">From</th>
                                        <th scope="col">To</th>
                                        <th scope="col">Deposit</th>
                                        <th scope="col">Withdrawal</th>
                                        <th scope="col">Current ZIT</th>    

                                    </tr>
                                </thead>
                                <tbody> -->
                                    <!-- <tr>
                                        <td>12 Jul 2018</td>
                                        <td></td>
                                        <td>James Johan</td>
                                        <td>-</td>
                                        <td>67</td>
                                        <td>159</td>    
                                    </tr>
                                    <tr>
                                        <td>12 Jul 2018</td>
                                        <td>-</td>
                                        <td>Kurak Jamesthon</td>
                                        <td>6</td>
                                        <td>-</td>
                                        <td>60</td>    
                                    </tr> 
                                    <tr>
                                        <td>12 Jul 2018</td>
                                        <td>James Johan</td>
                                        <td>-</td>
                                        <td>6</td>
                                        <td>-</td>
                                        <td>34</td>    
                                    </tr> 
                                </tbody>
                            </table>
                        </div> 
                    </div> -->
                    <!-- Pocket Starts Ends -->
                <!-- </div>
            </div>
        </div> 
    </section> -->
</section>
@endsection