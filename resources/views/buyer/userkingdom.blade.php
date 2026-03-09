@extends('layouts.new-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'User Kingdom')
<?php
$url_image = '';
$name = '';
$streetAddress = '';
$userCity = '';
$userState = '';
$userCountry = '';
$address = '';
$joinedDate = '';
//print_rr($userData);
if (!empty($userData)) {
    $user_avatar = $userData->user_avatar;
    if ($user_avatar)
        $url_image = getAttachmentUrlById((int) $user_avatar, 'avatar');
    $name = $userData->name;
    $streetAddress = $userData->physical_address;
    $userCity = $userData->user_city_id;
    $userCountry = getCountryById($userData->user_country_id);
    $userCountry = $userCountry['name'];
    $userState = getStatebyId($userData->user_state_id);
    $userState = $userState['state_name'];
    $joinedDate = strtotime($userData->created_at);
//echo  date('d-M-Y', $joinedDate);
}
?>
<section class="user-profile-mbt kingdom-page">
    <div class="strhd header-strip">
        <div class="container-fluid">
            <div class="row d-flex">
                <div class="hdlft justify-content-center align-self-center">
                    <div class="left-stripping-span common-span-style">
                        <span>Since <?php echo $date = date('M Y', $joinedDate); ?></span>
                    </div>        
                </div>
                <div class="col strlgo hdcnt hidden">
                    <div class="box-cont-header text-center">
                        <span class="circle-image"><img class="img-fluid" src="{{$url_image}}" alt="{{$name}}" title="{{$name}}"></span>
                        <div class="name-user">
                            <h4>{{$name}}</h4>
                            <p class="city-county"><?php
                                if ($userType == 'current') {
                                    echo isset($streetAddress) ? $streetAddress . '<br> ' : '';
                                    echo isset($userCity) ? $userCity . '  ' : '';
                                    echo isset($userState) ? $userState . '  ' : '';
                                    echo isset($userCountry) ? $userCountry : '';
                                } else {
                                    echo isset($userCity) ? $userCity . '  ' : '';
                                    echo isset($userCountry) ? $userCountry : '';
                                }
                                ?>
                            </p>
                        </div>
                        <?php if ($userType == 'other') { ?>
                            <div class="score-sec"><a href="<?php echo URL::to('/user-account'); ?>">Edit profile</a></div>
                        <?php } ?>
                    </div><!-- box-cont-header -->
                </div><!-- strlgo hdcnt -->
                <div class="hdrght justify-content-center align-self-center common-span-style">
                    <span>Kingdom:<?php echo $userHerdsCount; ?></span>
                    <?php /* ?><span>His connections:4</span>
                      <span class="br1px"></span>
                      <span>Connected to him:6</span>
                      <button class="btn btn-primary btn-simple">Connect With Me</button><?php */ ?>
                </div>
            </div>

        </div>
    </div>
    <div class="tabs-data">
        <div class="container">

            <div class="tab-content">
                <div id="about-tab" class="in<?php if (isset($userData->about) && $userData->about) { ?> active<?php } ?> mt-30"> 
                    <div class="col-sm-12">
                        <?php if($userData->about){ ?>
                                 <h2 class="mb-25">About</h2>
                        <p>
                        <?php } ?>
                        <?php
                        echo isset($userData->about) ? $userData->about : '';
                        ?>
                        </p>
                    </div>
                </div>
                <div id="supporting-tab" class="in<?php if (!(isset($userData->about) && $userData->about)) { ?> active<?php } ?> mt-30">

                    <div class="row brands-logoes list-plg-vt"> 
                        <?php
                        if (count($userHerds)) {
                            foreach ($userHerds as $userHerd) {
                                $iconImageUrl = '';
                                $storeImageUrl = '';
                                $icon_image_id = getImageIdByIconId((int) $userHerd->icon_id);
                                if ($icon_image_id)
                                    $iconImageUrl = getAttachmentUrlById((int) $icon_image_id, 'icons');
                                if ($userHerd->herdInfo['storeImageId']) {
                                    $storeImageUrl = getAttachmentUrlById((int) $userHerd->herdInfo['storeImageId'], 'medium');
                                }
                                ?>
                                <div class="col-md-12 row icbx">
                                    <div class="col-md-2 text-center">
                                        <span class="anml"> <span class="pwr"><?php echo $userHerd->icon_price; ?></span> <span class="prg"><span class="up">▲</span> <?php echo $userHerd->perc_inc_dec; ?>%</span><a href="javascript:;">
                                                <img class="img-fluid icon_img_url" src="<?php echo $iconImageUrl; ?>" alt="{{$userHerd->perc_inc_dec}}" title="{{$userHerd->perc_inc_dec}}">
                                            </a>
                                        </span>
                                        <p class="text-center icon_store_products" style="margin-top:20px; margin-bottom:0"><?php echo $userHerd->herdInfo['noOfProducts']; ?> Product(s)</p> 
                                       
                                    </div>
                                    <div class="col-md-8">
        <?php /* ?><h3>Ranked 3rd in Super Supporter Pool</h3><?php */ ?>
                                        <table class="inftbl">
                                            <tr><td>Last Increase:</td><td class="text-center icon_details_last_increase"><?php echo $userHerd->perc_inc_dec; ?>%</td> <td>Last Transaction:</td><td class="text-center icon_details_last_transaction"><?php echo $userHerd->herdInfo['lastTransaction'] ? $userHerd->herdInfo['lastTransaction'] : 0; ?>USDC</td></tr> 
                                            <tr><td>Animal Icon Debt:</td><td class="text-center"><?php echo $userHerd->icon_debt; ?></td> <td>Total Earning:</td><td class="text-center icon_details_total_earning"><?php echo $userHerd->total_earning; ?>USDC</td></tr>
                                            <tr><td>In Your Herd:</td><td class="text-center"><?php echo $userHerd->herdInfo['inHerd']; ?></td> <td>Herd You Are Part Of:</td><td class="text-center"><?php echo $userHerd->herdInfo['partOfHerd']; ?></td></tr>
                                            <tr><td></td><td></td><td></td><td></td></tr> 
                                        </table>
                                        <table class="inftbl"> 
                                            <tr>
                                                <td><?php if ($userType == 'current') { ?><a href="javascript:;" data-herd-id="<?php echo $userHerd->herd_id; ?>" class="btn btn-primary open-detail-sup">View Details</a><?php } else if ($userType == 'other' && $userHerd->herdInfo['userIconAvailable'] == 'yes') { ?><a href="javascript:;" data-herd-id="<?php echo $userHerd->herd_id; ?>" class="btn btn-primary btn-green icon-purchase">Add To Your Herd</a><?php } ?></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                            </tr>  
                                        </table>     
                                    </div>
        <?php if (is_object($userHerd->campaign)) { ?>
                                        <div class="col-md-2">
                                            <input type="number" min="1" max="100" class="icon_details_campaign_pledge form-control text-center" value="1" style="margin-bottom:10px;" placeholder="Pledges">
                                            <a href="javascript:;" data-campaign-id="<?php echo $userHerd->campaign->store_campaign_id; ?>" class="btn btn-primary btn-simple icon_details_pledge_campaign">Pledge Support</a>
                                        </div>
                                <?php } ?>
                                </div>
    <?php } ?>
                            <div class="row">
                                <div class="custom-pagination col-sm-12 mt-15">
                                    <?php
                                    echo $userHerds->links('pagination.defaultpagination');
                                    ?>
                                </div>
                            </div>
                        <?php
                        } else {
                            if ($userType == 'current')
                                echo 'You have no animals in your kingdom,&nbsp;<a href="' . URL::to('/q/') . '">click here</a>&nbsp;to pledge for support.';
                            else
                                echo 'No animals in his kingdom';
                        }
                        ?>
                        <?php /* ?><div class="col-md-12 row icbx">
                          <div class="col-md-2 text-center">
                          <span class="anml"> <span class="pwr">100</span> <span class="prg"><span class="up">▲</span> 100%</span><a href="javascript:;">
                          <img class="img-fluid" src="{{ asset('assets/images/icon1.png',env('HTTPS_STATUS')) }}" alt="Project">
                          </a>
                          </span>
                          <p class="text-center" style="margin-top:20px; margin-bottom:0">23 Products</p>
                          <p class="text-center"> <img src="{{ asset('assets/images/dummy-small.png',env('HTTPS_STATUS')) }}"></p>
                          </div>
                          <div class="col-md-8">
                          <h3>Ranked 3rd in Super Supporter Pool</h3>
                          <table class="inftbl">
                          <tr><td>Last Increase:</td><td class="text-center">1%</td> <td>Last Transaction:</td><td class="text-center">50ZIT</td></tr>
                          <tr><td>Animal Icon Debt:</td><td class="text-center">0</td> <td>Total Earning:</td><td class="text-center">1250ZIT</td></tr>
                          <tr><td>In Your Herd:</td><td class="text-center">4</td> <td>Herd You Are Part Of:</td><td class="text-center">5</td></tr>
                          <tr><td></td><td></td><td></td><td></td></tr>
                          </table>
                          <table class="inftbl">
                          <tr>
                          <td><a href="#" class="btn btn-primary ">View Details</a> &nbsp; </td>
                          <td> &nbsp; <a href="#" class="btn btn-primary btn-green">Add To Your Herd</a></td>
                          <td></td>
                          <td></td>
                          </tr>
                          </table>
                          </div>
                          <div class="col-md-2">
                          <a href="#" class="btn btn-primary btn-simple ">Pledge Support</a>
                          </div>
                          </div>  <?php */ ?>
                    </div>                     

                    <div class="pld-view-detail">

                        <div class="  brands-logoes"> 
                            <div class="icbx row noborder nopad mt-60"> 
                                <div class="col-md-2 text-center">
                                    <span class="anml"> 
                                        <span id="icon_detail_icon_price" class="pwr"></span>
                                        <img id="icon_detail_img" class="img-fluid" src="" alt="">
                                    </span>
                                    <p id="icon_store_products" class="text-center" style="margin-top:20px; margin-bottom:0"></p> 
                                    <p class="text-center"><img id="icon_detail_store_img" style="max-width:150px;" src=""></p> 
                                </div>
                                <div class="col-md-8">
                                    <h3 id="icon_detail_icon_name" class="mb-10"></h3>
<?php /* ?><h6><b class="text-blue">100</b> animal icon price required to qualify for super supporter pool </h6><?php */ ?>
                                    <table class="inftbl">
                                        <tr><td>Last Increase:</td><td class="text-center" id="icon_details_last_increase"></td> <td>Last Transaction:</td><td class="text-center" id="icon_details_last_transaction">0ZIT</td></tr> 
                                        <tr><td>Animal Icon Debt:</td><td class="text-center" id="icon_details_debt">0</td> <td>Total Earning:</td><td class="text-center" id="icon_details_total_earning">0ZIT</td></tr>
                                        <tr><td>In Your Herd:</td><td class="text-center" id="icon_details_in_herd">0</td> <td>Herd You Are Part Of:</td><td class="text-center" id="icon_details_part_of_herd"></td></tr>
                                        <tr><td></td><td></td><td></td><td></td></tr> 
                                    </table>
                                </div>
                                <div class="col-md-2 text-right">
                                    <a href="javascript:;" class="btn btn-primary btn-simple go-back-t-list ">Go Back</a>    
                                </div> 
                            </div>   

                            <div id="promoting-tab1" class="tab-pane  in  active"> 
                                <!-- Nav tabs -->
                                <ul class=" prd nav nav-pills nav-fill">
                                    <li class="nav-item">
                                        <a class="nav-link active" data-toggle="tab" href="#0001">Transaction History</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#0002">AIP History</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link " data-toggle="tab" href="#0003">In Your Herd</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#0004">Herd You Part Of</a>
                                    </li>
                                </ul>

                                <!-- Tab panes -->
                                <div class="tab-content">
                                    <div id="0001" class="container tab-pane active"><br><br> 
                                        <div id="icon_details_transactions" class="row ">

                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">Date</th>
                                                        <th scope="col">Transaction Type</th>
                                                        <th scope="col">Amount</th>

                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>12 Jul 2018</td>
                                                        <td>Julie M Connected </td>
                                                        <td>12</td> 
                                                    </tr>
                                                    <tr>
                                                        <td>12 Jul 2018</td>
                                                        <td>Johan Booth Connected</td>
                                                        <td>17</td> 
                                                    </tr> 
                                                    <tr>
                                                        <td>12 Jul 2018</td>
                                                        <td>Revenue From SSP</td>
                                                        <td>175</td> 
                                                    </tr> 
                                                    <tr>
                                                        <td> </td>
                                                        <td> </td>
                                                        <td>Total: 204</td> 
                                                    </tr> 
                                                </tbody>
                                            </table>

                                        </div> 


                                    </div>
                                    <div id="0002" class="container tab-pane fade"><br><br> 
                                        <div id="icon_details_aip_history" class="row ">

                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">Date</th>
                                                        <th scope="col">Progresse</th>
                                                        <th scope="col">Debt</th>
                                                        <th scope="col">By</th> 
                                                        <th scope="col">Current AIP</th>   

                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>12 Jul 2018</td>
                                                        <td>1%</td>
                                                        <td>0</td>
                                                        <td>Pledge</td>
                                                        <td>65</td> 
                                                    </tr>
                                                    <tr>
                                                        <td>12 Jul 2018</td>
                                                        <td>2%</td>
                                                        <td>0</td>
                                                        <td>Pledge</td>
                                                        <td>63</td> 
                                                    </tr>
                                                    <tr>
                                                        <td>12 Jul 2018</td>
                                                        <td>5%</td>
                                                        <td>0.8</td>
                                                        <td>Julie M Connected </td>
                                                        <td>62</td> 
                                                    </tr>
                                                    <tr>
                                                        <td> </td>
                                                        <td> </td>
                                                        <td> </td>
                                                        <td> </td>
                                                        <td>Current AIP: 65</td> 
                                                    </tr> 
                                                </tbody>
                                            </table>

                                        </div> 
                                    </div> 


                                    <div id="0003" class="container tab-pane fade"><br><br>
                                        <div class="row ">
                                            <div id="icon_details_in_your_herd_users" class="col-md-12">

                                                <span class="usrmg text-center">
                                                    <span class="pwrusr">100</span>
                                                    <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{ asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS')) }}" alt="user review image" title="user review image">
                                                        Tommy</a>    
                                                </span>
                                                <span class="usrmg text-center">
                                                    <span class="pwrusr">100</span>
                                                    <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{ asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS')) }}" alt="user review image" title="user review image">
                                                        James</a>    
                                                </span>

                                                <span class="usrmg text-center">
                                                    <span class="pwrusr">100</span>
                                                    <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{ asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS')) }}" alt="user review image" title="user review image">
                                                        Peter</a>    
                                                </span>

                                                <span class="usrmg text-center">
                                                    <span class="pwrusr">100</span>
                                                    <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{ asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS')) }}" alt="user review image" title="user review image">
                                                        Jun</a>    
                                                </span>  

                                            </div>

                                        </div> 
                                    </div> 

                                    <div id="0004" class="container tab-pane fade"><br><br> 
                                        <div class="row ">
                                            <div id="icon_details_part_of_herd_users" class="col-md-12">

                                                <span class="usrmg text-center">
                                                    <span class="pwrusr">100</span>
                                                    <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{ asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS')) }}" alt="user review image" title="user review image">
                                                        Tommy</a>    
                                                </span>
                                                <span class="usrmg text-center">
                                                    <span class="pwrusr">100</span>
                                                    <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{ asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS')) }}" alt="user review image" title="user review image">
                                                        Tommy</a>    
                                                </span>

                                                <span class="usrmg text-center">
                                                    <span class="pwrusr">100</span>
                                                    <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{ asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS')) }}" alt="user review image" title="user review image">
                                                        Tommy</a>    
                                                </span>

                                                <span class="usrmg text-center">
                                                    <span class="pwrusr">100</span>
                                                    <a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title=""><img src="{{ asset('assets/images/dum-usr.jpg',env('HTTPS_STATUS')) }}" alt="user review image" title="user review image">
                                                        Tommy</a>    
                                                </span> 

                                            </div>

                                        </div>  
                                    </div> 
                                </div>

                            </div> 


                        </div>                         


                    </div>




                </div>

                <div id="promoting-tab" class="tab-pane  in mt-30">

                    <div class="row brands-cards ">
                        <div class="col-sm-4">
                            <div class="card">
                                <div class="brand-logo-container">
                                    <div class="brands-header text-center  d-flex h-100">
                                        <div class="justify-content-center align-self-center w100p">
                                            <img src="{{ asset('assets/images/logo2.png',env('HTTPS_STATUS')) }}" alt="logo image" title="logo image" class="img-fluid" >
                                        </div>     
                                    </div>
                                    <div class="brands-boday">
                                        <p class="paper-rtn">Paper<span>(144)</span></p>
                                        <div class="brand-midle-head">
                                            <div class="brands-small-logo display-inline-block">
                                                <img src="{{ asset('assets/images/logo-3.png',env('HTTPS_STATUS')) }}" alt="logo image" title="logo image" class="img-fluid" >                                          
                                            </div>
                                            <div class="display-inline-block">
                                                <p>Merchant ID:34543</p>
                                            </div>
                                            <div class="pull-right stars-rating-cnt text-blue">
                                                <span class="fa fa-star"></span>
                                            </div>
                                        </div><!-- brand-midle-head -->
                                        <div class="justify-content-between list-brands">
                                            <div class="row mt-5 mb-5 ">
                                                <div class="col-sm-6">
                                                    <p>Per Sale:</p>
                                                </div>
                                                <div class="col-sm-6">
                                                    <p class="text-blue text-right"><b>40.00% to 45.00%</b></p>
                                                </div>
                                            </div>
                                            <div class="row mt-5 mb-5">
                                                <div class="col-sm-6">
                                                    <p>Power Rank:</p>
                                                </div>
                                                <div class="col-sm-6">
                                                    <p class="text-blue text-right"><b>15</b></p>
                                                </div>
                                            </div>
                                            <div class="row mt-5 mb-5">
                                                <div class="col-sm-6">
                                                    <p>7 Days EPC</p>
                                                </div>
                                                <div class="col-sm-6">
                                                    <p class="text-blue text-right"><b>$456</b></p>
                                                </div>
                                            </div>                                  
                                        </div>
                                    </div><!-- brands-boday -->
                                    <div class="brands-footer mt-25 mb-15">
                                    </div>

                                </div>
                            </div>
                        </div>


                        <div class="col-sm-4">
                            <div class="card">
                                <div class="brand-logo-container">
                                    <div class="brands-header text-center  d-flex h-100">
                                        <div class="justify-content-center align-self-center w100p">
                                            <img src="{{ asset('assets/images/logo1.png',env('HTTPS_STATUS')) }}" alt="logo image" title="logo image" class="img-fluid" >
                                        </div>     
                                    </div>
                                    <div class="brands-boday">
                                        <p class="paper-rtn">Paper<span>(144)</span></p>
                                        <div class="brand-midle-head">
                                            <div class="brands-small-logo display-inline-block">
                                                <img src="{{ asset('assets/images/logo-4.png',env('HTTPS_STATUS')) }}" alt="logo image" title="logo image" class="img-fluid" >                                          
                                            </div>
                                            <div class="display-inline-block">
                                                <p>Merchant ID:34543</p>
                                            </div>
                                            <div class="pull-right stars-rating-cnt text-blue">
                                                <span class="fa fa-star"></span>
                                            </div>
                                        </div><!-- brand-midle-head -->
                                        <div class="justify-content-between list-brands">
                                            <div class="row mt-5 mb-5 ">
                                                <div class="col-sm-6">
                                                    <p>Per Sale:</p>
                                                </div>
                                                <div class="col-sm-6">
                                                    <p class="text-blue text-right"><b>40.00% to 45.00%</b></p>
                                                </div>
                                            </div>
                                            <div class="row mt-5 mb-5">
                                                <div class="col-sm-6">
                                                    <p>Power Rank:</p>
                                                </div>
                                                <div class="col-sm-6">
                                                    <p class="text-blue text-right"><b>15</b></p>
                                                </div>
                                            </div>
                                            <div class="row mt-5 mb-5">
                                                <div class="col-sm-6">
                                                    <p>7 Days EPC</p>
                                                </div>
                                                <div class="col-sm-6">
                                                    <p class="text-blue text-right"><b>$456</b></p>
                                                </div>
                                            </div>                                  
                                        </div>
                                    </div><!-- brands-boday -->
                                    <div class="brands-footer mt-25 mb-15">
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="card">
                                <div class="brand-logo-container">
                                    <div class="brands-header text-center  d-flex h-100">
                                        <div class="justify-content-center align-self-center w100p">
                                            <img src="{{ asset('assets/images/logo5.png',env('HTTPS_STATUS')) }}" alt="logo image" title="logo image" class="img-fluid" >
                                        </div>     
                                    </div>
                                    <div class="brands-boday">
                                        <p class="paper-rtn">Paper<span>(144)</span></p>
                                        <div class="brand-midle-head">
                                            <div class="brands-small-logo display-inline-block">
                                                <img src="{{ asset('assets/images/logo-1.png',env('HTTPS_STATUS')) }}" alt="logo image" title="logo image" class="img-fluid" >                                          
                                            </div>
                                            <div class="display-inline-block">
                                                <p>Merchant ID:34543</p>
                                            </div>
                                            <div class="pull-right stars-rating-cnt text-blue">
                                                <span class="fa fa-star"></span>
                                            </div>
                                        </div><!-- brand-midle-head -->
                                        <div class="justify-content-between list-brands">
                                            <div class="row mt-5 mb-5 ">
                                                <div class="col-sm-6">
                                                    <p>Per Sale:</p>
                                                </div>
                                                <div class="col-sm-6">
                                                    <p class="text-blue text-right"><b>40.00% to 45.00%</b></p>
                                                </div>
                                            </div>
                                            <div class="row mt-5 mb-5">
                                                <div class="col-sm-6">
                                                    <p>Power Rank:</p>
                                                </div>
                                                <div class="col-sm-6">
                                                    <p class="text-blue text-right"><b>15</b></p>
                                                </div>
                                            </div>
                                            <div class="row mt-5 mb-5">
                                                <div class="col-sm-6">
                                                    <p>7 Days EPC</p>
                                                </div>
                                                <div class="col-sm-6">
                                                    <p class="text-blue text-right"><b>$456</b></p>
                                                </div>
                                            </div>                                  
                                        </div>
                                    </div><!-- brands-boday -->
                                    <div class="brands-footer mt-25 mb-15">
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="card">
                                <div class="brand-logo-container">
                                    <div class="brands-header text-center  d-flex h-100">
                                        <div class="justify-content-center align-self-center w100p">
                                            <img src="{{ asset('assets/images/logo7.png',env('HTTPS_STATUS')) }}" alt="logo image" title="logo image" class="img-fluid" >
                                        </div>     
                                    </div>
                                    <div class="brands-boday">
                                        <p class="paper-rtn">Paper<span>(144)</span></p>
                                        <div class="brand-midle-head">
                                            <div class="brands-small-logo display-inline-block">
                                                <img src="{{ asset('assets/images/logo-6.png',env('HTTPS_STATUS')) }}" alt="logo image" title="logo image" class="img-fluid" >                                          
                                            </div>
                                            <div class="display-inline-block">
                                                <p>Merchant ID:34543</p>
                                            </div>
                                            <div class="pull-right stars-rating-cnt text-blue">
                                                <span class="fa fa-star"></span>
                                            </div>
                                        </div><!-- brand-midle-head -->
                                        <div class="justify-content-between list-brands">
                                            <div class="row mt-5 mb-5 ">
                                                <div class="col-sm-6">
                                                    <p>Per Sale:</p>
                                                </div>
                                                <div class="col-sm-6">
                                                    <p class="text-blue text-right"><b>40.00% to 45.00%</b></p>
                                                </div>
                                            </div>
                                            <div class="row mt-5 mb-5">
                                                <div class="col-sm-6">
                                                    <p>Power Rank:</p>
                                                </div>
                                                <div class="col-sm-6">
                                                    <p class="text-blue text-right"><b>15</b></p>
                                                </div>
                                            </div>
                                            <div class="row mt-5 mb-5">
                                                <div class="col-sm-6">
                                                    <p>7 Days EPC</p>
                                                </div>
                                                <div class="col-sm-6">
                                                    <p class="text-blue text-right"><b>$456</b></p>
                                                </div>
                                            </div>                                  
                                        </div>
                                    </div><!-- brands-boday -->
                                    <div class="brands-footer mt-25 mb-15">
                                    </div>

                                </div>
                            </div>
                        </div>              


                        <div class="col-sm-4">
                            <div class="card">
                                <div class="brand-logo-container">
                                    <div class="brands-header text-center  d-flex h-100">
                                        <div class="justify-content-center align-self-center w100p">
                                            <img src="{{ asset('assets/images/logo8.png',env('HTTPS_STATUS')) }}" alt="logo image" title="logo image" class="img-fluid" >
                                        </div>     
                                    </div>
                                    <div class="brands-boday">
                                        <p class="paper-rtn">Paper<span>(144)</span></p>
                                        <div class="brand-midle-head">
                                            <div class="brands-small-logo display-inline-block">
                                                <img src="{{ asset('assets/images/logo-4.png',env('HTTPS_STATUS')) }}" alt="logo image" title="logo image" class="img-fluid" >                                          
                                            </div>
                                            <div class="display-inline-block">
                                                <p>Merchant ID:34543</p>
                                            </div>
                                            <div class="pull-right stars-rating-cnt text-blue">
                                                <span class="fa fa-star"></span>
                                            </div>
                                        </div><!-- brand-midle-head -->
                                        <div class="justify-content-between list-brands">
                                            <div class="row mt-5 mb-5 ">
                                                <div class="col-sm-6">
                                                    <p>Per Sale:</p>
                                                </div>
                                                <div class="col-sm-6">
                                                    <p class="text-blue text-right"><b>40.00% to 45.00%</b></p>
                                                </div>
                                            </div>
                                            <div class="row mt-5 mb-5">
                                                <div class="col-sm-6">
                                                    <p>Power Rank:</p>
                                                </div>
                                                <div class="col-sm-6">
                                                    <p class="text-blue text-right"><b>15</b></p>
                                                </div>
                                            </div>
                                            <div class="row mt-5 mb-5">
                                                <div class="col-sm-6">
                                                    <p>7 Days EPC</p>
                                                </div>
                                                <div class="col-sm-6">
                                                    <p class="text-blue text-right"><b>$456</b></p>
                                                </div>
                                            </div>                                  
                                        </div>
                                    </div><!-- brands-boday -->
                                    <div class="brands-footer mt-25 mb-15">
                                    </div>

                                </div>
                            </div>
                        </div>  

                        <div class="col-sm-4">
                            <div class="card">
                                <div class="brand-logo-container">
                                    <div class="brands-header text-center  d-flex h-100">
                                        <div class="justify-content-center align-self-center w100p">
                                            <img src="{{ asset('assets/images/logo5.png',env('HTTPS_STATUS')) }}" alt="logo image" title="logo image" class="img-fluid" >
                                        </div>     
                                    </div>
                                    <div class="brands-boday">
                                        <p class="paper-rtn">Paper<span>(144)</span></p>
                                        <div class="brand-midle-head">
                                            <div class="brands-small-logo display-inline-block">
                                                <img src="{{ asset('assets/images/logo-6.png',env('HTTPS_STATUS')) }}" alt="logo image" title="logo image" class="img-fluid" >                                          
                                            </div>
                                            <div class="display-inline-block">
                                                <p>Merchant ID:34543</p>
                                            </div>
                                            <div class="pull-right stars-rating-cnt text-blue">
                                                <span class="fa fa-star"></span>
                                            </div>
                                        </div><!-- brand-midle-head -->
                                        <div class="justify-content-between list-brands">
                                            <div class="row mt-5 mb-5 ">
                                                <div class="col-sm-6">
                                                    <p>Per Sale:</p>
                                                </div>
                                                <div class="col-sm-6">
                                                    <p class="text-blue text-right"><b>40.00% to 45.00%</b></p>
                                                </div>
                                            </div>
                                            <div class="row mt-5 mb-5">
                                                <div class="col-sm-6">
                                                    <p>Power Rank:</p>
                                                </div>
                                                <div class="col-sm-6">
                                                    <p class="text-blue text-right"><b>15</b></p>
                                                </div>
                                            </div>
                                            <div class="row mt-5 mb-5">
                                                <div class="col-sm-6">
                                                    <p>7 Days EPC</p>
                                                </div>
                                                <div class="col-sm-6">
                                                    <p class="text-blue text-right"><b>$456</b></p>
                                                </div>
                                            </div>                                  
                                        </div>
                                    </div><!-- brands-boday -->
                                    <div class="brands-footer mt-25 mb-15">
                                    </div>

                                </div>
                            </div>
                        </div>  





                    </div><!-- brands-cards -->


                </div><!-- menu2 -->
            </div>
        </div>
    </div>  


    <!-- Modal 1 -->
    <div class="modal" id="myModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Add This To Your Herd</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <!-- Modal Header -->

                <br>
                <div class="text-center" style=" width:200px; height:200px; margin:auto">
                    <span class="anml"> <span class="pwr">100</span> <span class="prg"><span class="up">▲</span> 100%</span><a href="javascript:;">
                            <img class="img-fluid" src="images/icon1.png" alt="Project">
                        </a>
                    </span>
                </div>   
                <!-- Modal body -->
                <div class="modal-body">
                    <h2 class="text-center">Add This Animal To Your Herd </h2>
                    <h2 class="text-center" style="color:#006bf4">100USDC</h2>
                    <p  class="text-center">Required to include this to your herd </p>
                    <p class="text-center"><a href="#" class="btn btn-primary">Include this to my herd</a></p>
                </div>

                <!-- Modal footer -->
                <br>

            </div>
        </div>
    </div>
    <!--Model 1 Ends -->    


</section>
<?php
if (isset($_GET['page']) && $_GET['page'] != '') {
    ?>
    <script>
        jQuery(document).ready(function () {

            jQuery('#wallettab').click();
        });
    </script>
    <?php
}
?>
<script>
    jQuery(document).ready(function () {
        jQuery('.icon-purchase').on('click', function (event) {
            var seller_herd_id = jQuery(this).data('herd-id');
            var data = {};
            data['seller_herd_id'] = seller_herd_id;
            jQuery.ajax({
                url: '/icon-purchase',
                method: 'POST',
                type: 'POST',
                data: data,
                success: function (res) {
                    if (res.status == 'success') {
                        swal("", res.message, "success");
                        //alert(res.message);
                        //setTimeout(function(){
                        location.reload();
                        //}, 1000);
                    } else {
                        swal("", res.message, "error");
                        
                    }
                }
            });
        });
    });

    var y = $(window).scrollTop();
    jQuery('.open-detail-sup').on('click', function (event) {

        var current_icon = this;
        var user_herd_id = jQuery(this).data('herd-id');
        var data = {};
        data['user_herd_id'] = user_herd_id;
        jQuery.ajax({
            url: '/icon-details',
            method: 'POST',
            type: 'POST',
            data: data,
            success: function (res) {
                if (res.status == 'success') {
                    var icon_box = jQuery(current_icon).closest('.row.icbx');
                    jQuery('#icon_detail_img').attr('src', jQuery(icon_box).find('.icon_img_url').attr('src'));
                    jQuery('#icon_store_products').html(jQuery(icon_box).find('.icon_store_products').html());
                    jQuery('#icon_detail_store_img').attr('src', jQuery(icon_box).find('.icon_store_img_url').attr('src'));
                    jQuery('#icon_detail_icon_name').html(res.icon_name);
                    jQuery('#icon_details_last_increase').html(jQuery(icon_box).find('.icon_details_last_increase').html());
                    jQuery('#icon_details_last_transaction').html(jQuery(icon_box).find('.icon_details_last_transaction').html());
                    jQuery('#icon_details_debt').html(res.herd_details.icon_debt);
                    jQuery('#icon_details_total_earning').html(jQuery(icon_box).find('.icon_details_total_earning').html());
                    jQuery('#icon_details_in_herd').html(res.inHerd.length);
                    jQuery('#icon_details_part_of_herd').html(res.partOfHerd.length);
                    var current_icon_price = jQuery(icon_box).find('.pwr').html();
                    jQuery('#icon_detail_icon_price').html(current_icon_price);

                    //display transactions
                    jQuery('#icon_details_transactions').html('');
                    var transactions_html = '<table class="table table-striped"><thead><tr><th scope="col">Transaction ID</th><th scope="col">Date</th><th scope="col">Transaction Desc</th><th scope="col">Amount</th></tr></thead><tbody>';
                    jQuery.each(res.transactions.data, function (ind, val) {
                        transactions_html += '<tr><td>' + val.trans_id + '</td><td>' + val.date_display + '</td><td>' + val.desc + '</td><td>' + val.trans_amount + '</td></tr>';
                    });
                    jQuery('#icon_details_transactions').append(transactions_html + '</tbody></table><div class="row"><div class="custom-pagination col-sm-12 mt-15">' + res.transactions_pagination + '</div></div>');

                    //display aip history
                    jQuery('#icon_details_aip_history').html('');
                    var aip_history_html = '<table class="table table-striped"><thead><tr><th scope="col">Date</th><th scope="col">AIP</th><th scope="col">Debt</th><th scope="col">By</th><th scope="col">Current AIP</th></tr></thead><tbody>';
                    jQuery.each(res.aip_history, function (ind, val) {
                        aip_history_html += '<tr><td>' + val.date_display + '</td><td>' + val.aip_amount + '</td><td>' + val.aip_debt + '</td><td>' + val.aip_desc + '</td><td>' + val.aip_price + '</td></tr>';
                    });
                    jQuery('#icon_details_aip_history').append(aip_history_html + '<tr><td> </td><td> </td><td> </td><td> </td><td>Current AIP: ' + current_icon_price + '</td></tr></tbody></table>');

                    //display in your herd
                    jQuery('#icon_details_in_your_herd_users').html('');
                    jQuery.each(res.inHerd, function (ind, val) {
                        jQuery('#icon_details_in_your_herd_users').append('<span class="usrmg text-center"><span class="pwrusr">' + val.icon_price + '</span><a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title="" href="/user-profile/' + val.user_id + '/"><img src="' + val.user_img + '">' + val.name + '</a></span>');
                    });

                    //display part of herd
                    jQuery('#icon_details_part_of_herd_users').html('');
                    jQuery.each(res.partOfHerd, function (ind, val) {
                        jQuery('#icon_details_part_of_herd_users').append('<span class="usrmg text-center"><span class="pwrusr">' + val.icon_price + '</span><a class="small ppcnt" data-container="body" data-toggle="popover" data-placement="top" data-content=" " data-original-title="" title="" href="/user-profile/' + val.user_id + '/"><img src="' + val.user_img + '">' + val.name + '</a></span>');
                    });

                    jQuery(jQuery(current_icon).closest('.list-plg-vt')).hide();
                    jQuery('.pld-view-detail').fadeIn();
                    jQuery('html, body').animate({scrollTop: y + 350}, 1000);
                } else {
                    alert(res.message);
                }
            }
        });
    });

    jQuery('#icon_details_transactions').on('click', '.pagination a', function (event) {
        event.preventDefault();
        var url = $(this).attr('href') + '&listing_type=transactions';
        jQuery.ajax({
            url: url,
            success: function (res) {
                if (res.status == 'success') {
                    //display transactions
                    jQuery('#icon_details_transactions').html('');
                    var transactions_html = '<table class="table table-striped"><thead><tr><th scope="col">Transaction ID</th><th scope="col">Date</th><th scope="col">Transaction Desc</th><th scope="col">Amount</th></tr></thead><tbody>';
                    jQuery.each(res.transactions.data, function (ind, val) {
                        transactions_html += '<tr><td>' + val.trans_id + '</td><td>' + val.date_display + '</td><td>' + val.desc + '</td><td>' + val.trans_amount + '</td></tr>';
                    });
                    jQuery('#icon_details_transactions').append(transactions_html + '</tbody></table><div class="row"><div class="custom-pagination col-sm-12 mt-15">' + res.transactions_pagination + '</div></div>');
                } else {
                    alert(res.message);
                }
            }
        });
    });

    jQuery('#icon_details_aip_history').on('click', '.pagination a', function (event) {
        event.preventDefault();
        var url = $(this).attr('href') + '&listing_type=aip_history';
        jQuery.ajax({
            url: url,
            success: function (res) {
                if (res.status == 'success') {
                    //display aip history
                    jQuery('#icon_details_aip_history').html('');
                    var aip_history_html = '<table class="table table-striped"><thead><tr><th scope="col">Date</th><th scope="col">AIP</th><th scope="col">Debt</th><th scope="col">By</th><th scope="col">Current AIP</th></tr></thead><tbody>';
                    jQuery.each(res.aip_history, function (ind, val) {
                        aip_history_html += '<tr><td>' + val.date_display + '</td><td>' + val.aip_amount + '</td><td>' + val.aip_debt + '</td><td>' + val.aip_desc + '</td><td>' + val.aip_price + '</td></tr>';
                    });
                    jQuery('#icon_details_aip_history').append(aip_history_html + '<tr><td> </td><td> </td><td> </td><td> </td><td>Current AIP: ' + current_icon_price + '</td></tr></tbody></table>');
                } else {
                    alert(res.message);
                }
            }
        });
    });

    jQuery('.go-back-t-list').on('click', function (event) {
        jQuery(this).closest('.pld-view-detail').hide();
        jQuery('.list-plg-vt').fadeIn();
        jQuery('html, body').animate({scrollTop: y + 150}, 1000)

    });

    var pledging = 'no';
    jQuery(document).on('click', '.icon_details_pledge_campaign', function (e) {
        if (pledging == 'yes')
            return;
        var current_pledge = jQuery(this).parent();
        var campaign_id = jQuery(this).data('campaign-id');
        var pledge_amount = parseInt(jQuery('.icon_details_campaign_pledge').val());
        if (!pledge_amount || pledge_amount < 1) {
            jQuery(current_pledge).find('.icon_details_campaign_pledge').focus();
            alert('Please enter numeric value to pledge for support');
            return;
        }
        var data = {};
        pledging = 'yes';
        data['campaign_id'] = campaign_id;
        data['pledge_amount'] = pledge_amount;
        jQuery.ajax({
            'url': '/product/pledge-support',
            'Type': 'POST',
            'method': 'POST',
            'data': data,
            'success': function (res) {
                obj = JSON.parse(res);
                pledging = 'no';
                if (obj.status == 'success') {
                    jQuery(current_pledge).find('.icon_details_campaign_pledge').val('1');
                    swal("", obj.message, "success");
                    location.reload();
                    //jQuery('.response-div').html('<div class="alert alert-success">' + obj.message + '</div>');
                }
                if (obj.status == 'error') {
                   swal("", obj.message, "errorF");
                    //jQuery('.response-div').html('<div class="alert alert-danger">' + obj.message + '</div>');
                }
            }
        });
    });



</script>
@endsection