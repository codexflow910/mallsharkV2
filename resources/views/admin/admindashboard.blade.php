@extends('layouts.dashboard-main')
@section('content')
@section('title', 'Admin Dashboard')
<main>
    <section class="dshbrdin buyer mt-0 d-flex">
        @include('admin.adminsidebar')
        <div class="container px-4">
            <div class="">
                <div class="row mt-40">
                    @if (canAccess('view-sales-reports'))
                    <div class="col-md-6">
                        <div class="wdgtbx border">
                            <h6>Sales<a class="pull-right" href="<?php echo URL::to('admin/sale-report'); ?>"><i class="fa fa-pencil"
                                                                                  aria-hidden="true"></i></a></h6>
                            <div class="wdgcnt ">
                                <div class="dshord">
                                    <div class="usr">
                                    </div>
                                    <div class="shp">
                                        <p>This month</p>
                                        <p>This week </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    @if (canAccess('view-users'))
                    <div class="col-md-6">
                        <div class="wdgtbx border mb-15">
                            <h6>Users<a class="pull-right" href="<?php echo URL::to('/admin/allusers') ?>"><i
                                        class="fa fa-pencil" aria-hidden="true"></i></a></h6>
                            <div class="wdgcnt ">
                                <div class="dshord">
                                    <div class="usr">
                                    </div>
                                    <div class="shp">
                                        <p>No of inactive users: </p>
                                        <?php 
                                            if ($unactivatedUser > 0) { 
                                                    echo $unactivatedUser;
                                            }else{
                                                echo 0;
                                            }
                                        ?>
                                        <p>No of active users:</p><?php
                                            if ($activatedUser > 0) {
                                                echo $activatedUser;
                                            }else{
                                                echo 0;
                                            }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    @if (canAccess('view-products'))
                    <div class="col-md-6">
                        <div class="wdgtbx border mb-15">
                            <h6>Products<a class="pull-right" href="<?php echo URL::to('/admin/store-products') ?>">
                                    <i class="fa fa-pencil" aria-hidden="true"></i></a></h6>
                            <div class="wdgcnt ">
                                <div class="dshord">
                                    <div class="usr">
                                    </div>
                                    <div class="shp">                                    
                                        <p>Total Store Products</p><?php
                                        if ($allproducts > 0) {
                                            echo $allproducts;
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    @if (canAccess('view-vendors'))
                    <div class="col-md-6">
                        <div class="wdgtbx border mb-15">
                            <h6>Stores<a class="pull-right" href="<?php echo URL::to('/admin/stores') ?>">
                                    <i class="fa fa-pencil" aria-hidden="true"></i></a></h6>
                            <div class="wdgcnt ">
                                <div class="dshord">
                                    <div class="usr">
                                    </div>
                                    <div class="shp">                                    
                                        <p>Total Stores</p><?php
                                        if ($allstores > 0) {
                                            echo $allstores;
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    @if (canAccess('view-tiktok-videos'))
                    <div class="col-md-6">
                        <div class="wdgtbx border mb-15">
                            <h6>TikTok Videos<a class="pull-right" href="<?php echo URL::to('/admin/tiktok-videos') ?>">
                                    <i class="fa fa-pencil" aria-hidden="true"></i></a></h6>
                            <div class="wdgcnt ">
                                <div class="dshord">
                                    <div class="usr">
                                    </div>
                                    <div class="shp">                                    
                                        <p>Total TikTok Videos</p><?php
                                        if ($alltiktokvideos > 0) {
                                            echo $alltiktokvideos;
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
    </section>
</main>
@endsection