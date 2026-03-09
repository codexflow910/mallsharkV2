@extends('layouts.new-main')
@section('content')
@section('title', 'List Management')
<?php
    $listingresults=array();
    ?>

<section class="admin-dashboard dshbrdin buyer mt-0">
    @include('admin.adminsidebar')
    <div class="dshbrdinadmin email-templates">
        <div class="container">
            <div class="row mt-40">
                <div class="col-sm-12">
                    <h3>Email Templates</h3>
                </div>
            </div>
            <div class="filter-table-cnt">
                <div class="table-head-mbt">
                    <div class="row mt-30">
                        <div class="col-sm-12">
                            <div class="row no-mrgn">
                                <div class="col-9 col-sm-9 gray-back br-0">
                                    <span class="filt-mbt">Filters</span>
                                </div>
                                <div class="col-3  col-sm-3 gray-back no-brder bl-0">
                                    <span class="filt-mbt">Action</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>    
                <div class="table-body-filter mt-15">
                    <div id="accordion" class="accordion">
                        <div class="card mb-0">
                            <div class="listing-filter-div">
                                <div class="row header-strip-ct">
                                    <div class="col-sm-7 justify-content-center align-self-center">                
                                        <div class="card-header collapsed align-middle" data-toggle="collapse" href="#collapse1">
                                            <a class="card-title">
                                                Ad Approved
                                            </a>
                                        </div>
                                    </div>    
                                    <div class="col-sm-4">
                                        
                                    </div>                              

                                </div>
                                <div id="collapse1" class="card-body " data-parent="#accordion">
                                    <div class="email-body-div">
                                        asdf asd
                                    </div><!-- email-body-div -->
                                </div>

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