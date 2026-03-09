@extends('layouts.new-main')
@section('content')
@section('title', 'List Management')

<main>
<section class="admin-dashboard dshbrdin buyer mt-0 lister-filter-mbt">
<?php 

if (collect(request()->segments())->last() == 'support-filters') {
?>
    @include('support.supportsidebar')
 
<?php }else{ ?>
     @include('admin.adminsidebar')
    <?php }?>
    <div class="dshbrdinadmin list-management">
        <div class="container">
            <!--            <div class="row row-full-width">
                            <div class="col-sm-12 mt-40">
                                <h3>Listing Filters</h3>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div class="search-field-ability">
                                    <input type="search" name="" class="form-control" placeholder="Search">
                                </div>
                            </div>
            
                            <div class="col-sm-9 col-md-9">
                                <div class="edit-active buttons-feature-group clearfix add-more-btn">
                                    <a href="#" class="btn btn-primary btn-simple f-left-md">Add Mover</a>
                                </div>
                            </div>
                        </div>-->

            <br>
            <div class="alert alert-success alert-success-mbt" id="success_message_ajax" role="alert" style="display:none;">
                Filter value has been updated successfully.
            </div>
            @if(Session::has('SuccessMessage'))
            <div class="alert alert-success alert-success-mbt" id="success_message" role="alert">
                {{ Session::get('SuccessMessage') }}
                <script>
                    setTimeout(function () {
                        jQuery("#success_message").fadeOut('slow');
                    }, 3000);
                </script>
            </div>
            @endif
            @if(Session::has('ErrorMessage'))
            <div class="alert alert-danger alert-success-mbt" id="error_message" role="alert">
                {{ Session::get('ErrorMessage') }}
                <script>
                    setTimeout(function () {
                        jQuery("#error_message").fadeOut('slow');
                    }, 3000);
                </script>
            </div>
            @endif
            <div class="filter-table-cnt">
                <div class="table-head-mbt">
                    <div class="row mt-30">
                        <div class="col-sm-12">
                            <div class="row no-mrgn">
                                <div class="col-9 col-sm-9 gray-back br-0">
                                    <span class="filt-mbt">Section</span>
                                </div>
                                <div class="col-3 col-sm-3 gray-back no-brder bl-0">
                                    <span class="filt-mbt">Action</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- table-head-mbt -->
                <div class="table-body-filter mt-15">

                    <div id="accordion" class="accordion">
                        <div class="card mb-0">
                            <?php
                            $counter = 1;
                            foreach ($filterData as $key => $fd) {
                                $filterarr = getFilterData($key);
                                if ($filterarr['filter_status'] == '2') {
                                    continue;
                                }
                                ?>
                                <div class="listing-filter-div">
                                    <div class="row no-mrgn">
                                        
                                        <?php 
                                        $styleClass="";
                                        if ($filterarr['filter_id'] != '3') {
                                            $styleClass="nodata";
                                            
                                        } ?>
                                        <div class="card-header collapsed col-sm-9">
                                            <a class="card-title">
                                                <?php echo ucfirst($filterarr['filter_name']); ?>
                                            </a>
                                        </div>
                                        <div class="strip-option col-sm-3">
                                            <?php
                                            $activateText = "Activate";
                                            if ($filterarr['filter_status'] == '1') {
                                                $activateText = "Deactivate";
                                            }
                                            ?>
                                            <a class="statuschange" href="<?php echo URL::to('/filters/' . $filterarr['filter_id'] . "/" . $activateText); ?>"><?php echo $activateText; ?></a>
<!--                                            <span class="seprator-br"></span>-->

<!--                                            <a class="deletefilter" href="<?php //echo URL::to('/del-filters/' . $filterarr['filter_id']); ?>">Delete</a>                -->
                                        </div>
                                    </div>


                                    <div id="collapse<?php echo $counter; ?>" class="card-body collapse" data-parent="#accordion" >

                                        <ul class="items-list-menu ">
                                            <?php foreach ($fd as $rv) { ?>
                                            <?php if ($filterarr['filter_id'] == '3') { ?>
                                                <li>
                                                    <div class="row">
                                                        <div class="text-div col-sm-9">
                                                            <div class="row dataupdate">
                                                                <div class="col-sm-4 col-md-3 col-lg-2  no-padd-right">
                                                                    <span  class="inline-block-ele mt-5"><?php echo ucfirst($rv['value_name']); ?></span>
                                                                </div>
                                                                <?php //if ($filterarr['filter_id'] == '3') { ?>
                                                                    <div class="col-sm-2 col-md-2 col-lg-1 no-padd-right">
                                                                        <input type="number" id="<?php echo $string = str_replace(' ', '', $rv['value_name']); ?>" name="filters_update" class="form-control" value="<?php echo $rv['value']; ?>" />
                                                                    </div>
                                                                    <div class="col-sm-3 col-md-2">
                                                                        <a href="#" class="update-btn btn btn-primary btn-simple no-padd-left no-padd-right min-width90" data-id="<?php echo $rv['f_value_id']; ?>"> Update</a>
                                                                    </div>
                                                                </div>
                                                            <?php //} ?>
                                                        </div>
                                                        <!--                                                        <div class="strip-option col-sm-3 no-border dropdown">
                                                  </div>-->
                                                    </div>
                                                </li>
                                            <?php } } ?>

                                        </ul>
                                    </div>
                                </div>
                                <?php
                                $counter++;
                            }
                            ?>

                        </div>
                    </div>
                </div><!-- table-body-filter -->
            </div>
            <!--            <div class="row">
                            <div class="custom-pagination col-sm-12 mt-15">
                                <nav aria-label="Page navigation example ">
                                    <ul class="pagination pull-right">
                                        <li class="page-item"><a class="page-link" href="#">Previous</a></li>
                                        <li class="page-item"><a class="page-link" href="#">1</a></li>
                                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                                        <li class="page-item"><a class="page-link" href="#">Next</a></li>
                                    </ul>
                                </nav>                        
                            </div>
            
                        </div>-->
        </div><!-- dshbrdinadmin -->

</section>
</main>
<script>
    $(document).ready(function () {
        $(".update-btn").on("click", function () {
            aa = $(this).closest('.dataupdate');
            updatedValue = aa.find('input').val();
            updatedId = $(this).attr('data-id');

            var data = {};
            data['value_id'] = updatedId;
            data['value'] = updatedValue;

            sendreq(data, 'filters', 'update', 'callback');
        });
    });
    function callback(data) {
        if (data == 'success') {
            $("#success_message_ajax").show();
            setTimeout(function () {
                jQuery("#success_message_ajax").fadeOut('slow');
            }, 3000);

        }
    }
    $(".statuschange").on("click", function () {
        var result = confirm(" Are you sure want to change status.?");
        if (!result) {
            return false;
        }
    });
    $(".deletefilter").on("click", function () {
        var result = confirm(" Are you sure want to delete Filter.?");
        if (!result) {
            return false;
        }
    });
</script>

@endsection