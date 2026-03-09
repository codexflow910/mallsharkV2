@extends('layouts.new-main')
@section('content')
@section('title', 'Support Dashboard')
<section class="admin-dashboard dshbrdin buyer mt-0">
    @include('support.supportsidebar')
    <div class="dshbrdinadmin">
        <div class="container">
            <div class="row mt-40">
                <div class="col-md-6">
                    <div class="wdgtbx border">
                        <h6>Sales<a class="pull-right" href="javascript:;"><i class="fa fa-pencil" aria-hidden="true"></i></a></h6>
                        <div class="wdgcnt ">
                            <div class="dshord">
                                <div class="usr">
                                </div>
                                <div class="shp">
                                    <p>This month</p>
                                    <p>This week </p>
<!--                                    <p>Supporting: 4 Products</p>   -->
                                </div>
                            </div>    
                        </div>    
                    </div>  
                </div>
                <div class="col-md-6">
                    <div class="wdgtbx border">
                        <h6>Tickets<a class="pull-right" href="javascript:;"><i class="fa fa-pencil" aria-hidden="true"></i></a></h6>
                        <div class="wdgcnt ">
                            <div class="dshord">
                                <div class="usr">
                                </div>
                                <div class="shp">
                                    <p>Open tickets</p>
                                    <p>Closed tickets</p>
<!--                                    <p>Supporting: 4 Products</p>   -->
                                    <a href="/user-profile" class="disable-link">View Profile</a>

                                </div>
                            </div>    

                        </div>    
                    </div>  
                </div>

                <div class="col-md-6">
                    <div class="wdgtbx border mb-15">
                        <h6>Users<a class="pull-right" href="<?php echo URL::to('/support/allusers') ?>"><i class="fa fa-pencil" aria-hidden="true"></i></a></h6>
                        <div class="wdgcnt ">
                            <div class="dshord">
                                <div class="usr">
                                </div>
                                <div class="shp">
                                    <?php if ($unactivatedUser > 0) { ?>
                                        <p>No of inactive users
                                            <?php
                                            echo $unactivatedUser;
                                        }
                                        ?></p>
                                    <p>No of active users</p><?php
                                        if ($activatedUser > 0) {
                                            echo $activatedUser;
                                        }
                                        ?>
<!--                                    <p>Supporting: 4 Products</p>   -->
                                </div>
                            </div>
                        </div>    
                    </div>  
                </div>
                <div class="col-md-6">
                    <div class="wdgtbx border mb-15">
                        <h6>Listings<a class="pull-right" href="<?php echo URL::to('/support/listing'); ?>"><i class="fa fa-pencil" aria-hidden="true"></i></a></h6>
                        <div class="wdgcnt ">
                            <div class="dshord">
                                <div class="usr">
                                </div>
                                <div class="shp">
                                    <?php if(!empty($activeListing)){
?>
                                    <p>Approved listing <?php echo $activeListing; ?></p>
                                    <?php } ?>
                                </div>
                                <div class="shp">
                                    <?php if(!empty($unactiveListing)){
?>
                                    <p>Un approved listing <?php echo $unactiveListing; ?></p>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>    
                    </div>  
                </div>
            </div>
        </div>
    </div><!-- dshbrdinadmin -->
</section>
@endsection