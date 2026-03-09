
@extends('layouts.new-main')
@section('content')
@section('title', 'Transport Management')


<section class="transportmanagement-container">
    @include('admin.adminsidebar')
    <div class="dshbrdinadmin list-management">

        <div class="container">
            <div class="row">
                @if (Session::has('success'))
                <div class="alert alert-success mt-10">{{ Session::get('success') }}</div>
                @endif
                <?php if (Session::has('err')) { ?>
                    <div class="alert alert-danger">
                        <ul><?php
                            $errs = Session::get('err');
                            foreach ($errs as $er) {
                                echo '<li>' . $er . '</li>';
                            }
                            ?>
                        </ul>
                    </div>
                <?php } ?>
                <div class="col-sm-12 mt-40">
                    <h3>Manage Movers / Transporters </h3>
                </div>
                <div class="col-sm-6 col-md-3">
                    <form method="get" action="">
                    <div class="search-field-ability">
                        <input type="search" name="s" class="form-control" value="<?php echo isset($_GET['s']) ? $_GET['s']:''; ?>" placeholder="Search">
                    </div>
                        <!-- <button type="submit" class="btn button">Search</button> -->
                    </form>
                </div>

                <div class="col-sm-9 col-md-9">
                    <div class="edit-active buttons-feature-group clearfix add-more-btn">
                        <a href="#" class="btn btn-primary addtransporterbtn btn-simple f-left-md" data-toggle="modal" data-target="#myModal">Add Mover</a>
                    </div>
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
                </div><!-- table-head-mbt -->
                <div class="table-body-filter mt-15">

                    <div id="accordion" class="accordion">
                        <div class="card mb-0">
                            <?php
                            if (count($transporters) > 0) {
                                $counter = 1;
                                foreach ($transporters as $tr) {
                                   
                                    ?>
                                    <div class="listing-filter-div">
                                        <div class="row header-strip-ct">
                                            <div class="col-sm-3 justify-content-center align-self-center">                
                                                <div class="card-header collapsed align-middle" data-toggle="collapse" href="#collapse{{$counter}}">
                                                    <a class="card-title">
                                                        {{ $tr->name }}
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="col-sm-3 justify-content-center align-self-center">
                                                <div class="option-header ">
                                                    <span><span class="spanone">Contact No:</span>{{ $tr->contact_detail }} </span>
                                                </div>
                                            </div>
                                            <div class="col-sm-3 justify-content-center align-self-center">
                                                <div class="option-header">
                                                    <span><span class="spanone">Email:</span> {{ $tr->email }} </span>
                                                </div>
                                            </div>
                                            <div class="col-sm-1 justify-content-center align-self-center">
                                                <div class="option-header">
                                                    <span>State</span>
                                                </div>
                                            </div>

                                            <div class="strip-option col-sm-2 justify-content-center align-self-center">
                                                <a href="#" class="editcustomer" data-toggle="" date-transporter_id="{{$tr->transporter_id}}" data-target="#myModal">Edit</a>
                                                <span class="seprator-br"></span>
                                                <a href="#" data-toggle="modal" class="deletecustomer" data-target="#delete-confirmation" data-customerid="{{$tr->transporter_id}}">Delete</a>                
                                            </div>
                                        </div>


                                        <div id="collapse{{$counter}}" class="card-body collapse" data-parent="#accordion" >
                                            <?php //if (isset($tr['state']) && count($tr['state']) > 0) {
                                                    $statess=getStatesByMoverId($tr->transporter_id);
                                                    if(count($statess)>0){
                                                ?>
                                                <?php foreach ($statess as $key => $es) { ?>
                                                    <div class="panel-group" id="accordion21">
                                                        <div class="panel row no-mrgn">
                                                            <div class="left-list-estate col-sm-10">
                                                                <a data-toggle="collapse" data-parent="#accordion21" href="#collapseTwo{{$tr->transporter_id}}{{$key}}" data-state_id="{{$key}}" data-transporter_id="{{$tr->transporter_id}}" class="collapsed class-arrow loadcities" aria-expanded="false">{{$es}}
                                                                </a>
                                                            </div>
                                                            <div class="right-list-estate col-sm-2">
                                                                <a class="addcity" href="#"  data-target="#city-select" data-state_id="{{$key}}" data-transporter_id="{{$tr->transporter_id}}">Update Cities</a>
                                                                <a href="#" data-toggle="modal" class="deletestate" data-customerid="{{$tr->transporter_id}}" data-stateid="{{$key}}" data-target="#delete-confirmation">Delete</a>                                         
                                                            </div>

                                                            <div id="collapseTwo{{$tr->transporter_id}}{{$key}}" class="panel-collapse collapse w100" aria-expanded="false">
                                                                <div class="panel-body">

                                                                    <div class="states-list" id="state-{{$tr->transporter_id}}{{$key}}">

                                                                    </div>


                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                    <?php
                                                }
                                            }
                                            ?>


                                        </div>
                                    </div><!-- listing-filter-div -->
                                    <?php
                                    $counter++;
                                }
                            }
                            ?>
                        </div>
                    </div>


                </div><!-- table-body-filter -->




            </div>












            <div class="row">
                
                
                    <div class="custom-pagination col-sm-12 mt-15">
                        <?php
                        $q = [];
                        if (isset($_GET['s'])) {
                            $q['s'] = $_GET['s'];
                        }
                        ?>
                        {{ $transporters->appends($q)->links('pagination.defaultpagination') }}             
                    </div>
                
               

            </div>



        </div><!-- dshbrdinadmin -->

</section>




<!-- Modal -->
<div id="delete-confirmation" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">

            <div class="modal-body pt-25">
                <h4 class="text-center text-capitalize red-text">
                    <span   class="circle-red"><i class="fa fa-exclamation" aria-hidden="true"></i></span> Are you sure you want to delete this?
                </h4>
            </div>
            <div class="modal-footer">

                <a href="#" class="btn btn-primary btn-simple f-left-md" data-dismiss="modal">No</a>
                <a href="javascript:void(0);" class="btn btn-primary btn-primary-background text-white delete-button">Yes</a>


            </div>
        </div>

    </div>
</div><!-- delete-confirmation -->




<!-- Modal -->
<div id="city-select" class="modal fade custom-popup" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Add Cities</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>

            </div>
            <div class="modal-body">
                <form action="{{route('addtransportercities')}}" method="post">
                    <input type="hidden" name="stateid" value="" id="stateid">
                    <input type="hidden" name="transpordertid" value="" id="transpordertid">
                    {{ csrf_field() }}
                    <div class="row">
                        <div class="col-sm-3 mb-15 text-right">
                            <label for="name">City:</label>
                        </div>
                        <div class="col-sm-7 mb-15">
                            <select  name="cities[]" id="add_cities" data-placeholder="Select Cities..." class="cform-control search-field chzn-select" multiple tabindex="4">

                            </select>
                        </div>


                    </div>


                    <div class="modal-footer">

                        <a href="#" class="btn btn-primary btn-simple f-left-md" data-dismiss="modal">Close</a>
                        <button type="submit" class="btn btn-primary btn-primary-background text-white">Update</button>
                    </div>
                </form>
            </div>

        </div>


    </div>
</div>
<!-- city-select -->






<!-- Modal -->
<div id="myModal" class="modal fade custom-popup" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Add Mover</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>

            </div>
            <div class="modal-body">
                <form action="{{route('addTransporter')}}" method="post" id="add-update-transporter">
                    <div class="row">
                        {{ csrf_field() }}
                        <div class="col-sm-3 mb-15 text-right">
                            <label for="name">Name:</label>
                        </div>
                        <input type="hidden" value="" id="transporter_update" name="transporter_update">
                        <div class="col-sm-7 mb-15">
                            <input type="text" id="transporter_name" name="transporter_name" class="form-control" required>
                        </div>

                        <div class="col-sm-3 mb-15 text-right">
                            <label for="name">Contact No:</label>
                        </div>
                        <div class="col-sm-7 mb-15">
                            <input type="tel" id="transporter_contact" name="transporter_contact" class="form-control" required>
                        </div>

                        <div class="col-sm-3 mb-15 text-right">
                            <label for="name">Email:</label>
                        </div>
                        <div class="col-sm-7 mb-15">
                            <input type="email" id="transporter_email" name="transporter_email" class="form-control" required>
                        </div>

                        <div class="col-sm-3 mb-15 text-right">
                            <label for="name">Website:</label>
                        </div>
                        <div class="col-sm-7 mb-15">
                            <input type="text" id="transporter_website" name="transporter_website" class="form-control">
                        </div>



                        <div class="col-sm-3 mb-15 text-right">
                            <label for="name">Office Address:</label>
                        </div>
                        <div class="col-sm-7 mb-15">
                            <input type="text" id="transporter_address" name="transporter_address" class="form-control" required>
                        </div>


                        <div class="col-sm-3 mb-15 text-right">
                            <label for="name">State:</label>
                        </div>
                        <div class="col-sm-7 mb-15">
                            <select  name="states[]" id="transporter_states" data-placeholder="Choose a State..." class="cform-control search-field chzn-select" multiple tabindex="4" required>
                                <option value=""></option>
                                <?php
                                if (count($states) > 0) {
                                    foreach ($states as $state) {
                                        echo '<option value="' . $state->state_code . '">' . $state->state_name . '</option>';
                                    }
                                }
                                ?>

                            </select>
                        </div>

                    </div>


                    <div class="modal-footer">

                        <a href="#" class="btn btn-primary btn-simple f-left-md" data-dismiss="modal">Decline</a>
                        <button type="submit" class="btn btn-primary btn-primary-background text-white">Save</button>
                    </div>
                </form>
            </div>

        </div>


    </div>
</div>


<div class="large-loader">
    <span>
        <img src=" http://127.0.0.1:8000/assets/images/loader-large.svg" class="img-fluid">
    </span>
</div>


<script src="{{ URL::asset('assets/js/chosen.jquery.js' ,env('HTTPS_STATUS')) }}" type="text/javascript"></script>
<script src="{{ URL::asset('assets/js/prism.js' ,env('HTTPS_STATUS')) }}" type="text/javascript" charset="utf-8"></script>
<script src="{{ URL::asset('assets/js/init.js' ,env('HTTPS_STATUS')) }}" type="text/javascript" charset="utf-8"></script>

<script>

$('#myModal,#city-select').on('shown.bs.modal', function () {
    $('.chzn-select', this).chosen();
});
jQuery(document).on('click', '.addcity', function () {
    state = jQuery(this).attr('data-state_id');
    transporter_id = jQuery(this).attr('data-transporter_id');
    jQuery("#stateid").val(state);
    jQuery("#transpordertid").val(transporter_id);
    if (state != '') {
        jQuery('.large-loader').show();
        jQuery.ajax({
            data: 'state_id=' + state + '&transporter_id=' + transporter_id,
            url: '{{route("getstatecities")}}',
            method: 'post',
            success: function (res) {

                jQuery("#city-select").modal();
                jQuery('#add_cities').html(res);
                jQuery('.large-loader').hide();
                jQuery('#add_cities').chosen();
                jQuery("#add_cities").trigger("chosen:updated");

            }
        });
    }
});
jQuery(document).on("click", ".addtransporterbtn", function () {
    jQuery("#transporter_update").val("");
});
jQuery(document).on('click', '.loadcities', function () {
    state = jQuery(this).attr('data-state_id');
    transporter_id = jQuery(this).attr('data-transporter_id');
    //jQuery("#stateid").val(state);
    //jQuery("#transpordertid").val(transporter_id);
    if (state != '') {
        jQuery('.large-loader').show();
        jQuery.ajax({
            data: 'state_id=' + state + '&transporter_id=' + transporter_id,
            url: '{{route("gettransportercities")}}',
            method: 'post',
            success: function (res) {
                id = "#state-" + transporter_id + state;
                jQuery(id).html(res);
                jQuery('.large-loader').hide();


            }
        });
    }
});
jQuery(document).on("click", ".deletecustomer", function () {
    customer_id = jQuery(this).attr("data-customerid");
    if (customer_id != '') {
        jQuery('.text-white.delete-button').attr("href", "{{Request::url()}}?delete_customer=" + customer_id);
    }
});
jQuery(document).on("click", ".deletestate", function () {
    customer_id = jQuery(this).attr("data-customerid");
    state_id = jQuery(this).attr("data-stateid");
    if (customer_id != '' && state_id != '') {
        jQuery('.text-white.delete-button').attr("href", "{{Request::url()}}?delete_customer_state=" + customer_id + '&delete_state=' + state_id);
    }
});

jQuery(document).on("click", ".editcustomer", function () {
    jQuery('#add-update-transporter')[0].reset();
    customer_id = jQuery(this).attr("date-transporter_id");
    jQuery("#transporter_update").val(customer_id);
    if (customer_id != '') {

        jQuery("#transporter_states option").each(function ($) {


            jQuery(this).removeAttr("selected");

        });



        jQuery('.large-loader').show();
        jQuery('#transporter_states option:selected').removeAttr('selected');
        jQuery.ajax({
            data: 'customer_id=' + customer_id,
            url: "{{route('editcustomer')}}",
            method: 'post',
            dataType: 'json',
            success: function (response) {
                jQuery('.large-loader').hide();
                jQuery("#transporter_name").val(response.transporter_name);
                jQuery("#transporter_contact").val(response.contact);
                jQuery("#transporter_email").val(response.email);
                jQuery("#transporter_website").val(response.website);
                jQuery("#transporter_address").val(response.address);
                states = response.state;
                jQuery.each(states, function (key, data) {
                    jQuery("#transporter_states option").each(function ($) {

                        vl = jQuery(this).val();
                        if (vl == key) {

                            jQuery(this).attr("selected", "selected");
                        }
                    });

                });
                jQuery("#myModal").modal();
                //jQuery('#transporter_states').chosen('destroy');
                //jQuery('#transporter_states').chosen();

                jQuery("#transporter_states").trigger("chosen:updated");

                //jQuery("#transporter_states").trigger("chosen:updated");
            }
        });
    }
    jQuery('#myModal')
            .on('hidden.bs.modal', function () {
               
               if(jQuery("#transporter_update").val()!=''){
                   location.reload();
               }
            })



});

  // Chosen touch support.
    if (jQuery('.chzn-select').length > 0) {
      jQuery('.chzn-select').on('touchstart', function(e){
        e.stopPropagation(); e.preventDefault();
        // Trigger the mousedown event.
        jQuery(this).trigger('mousedown');
      });
    }


</script>

@endsection