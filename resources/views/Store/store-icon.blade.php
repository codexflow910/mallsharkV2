@extends('layouts.new-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'animal icon')
@include('Store.sidebar')
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<!--<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>-->
<style>

    .icnlst label > input:checked + img{
        border: 1px solid #009688;
        border-radius: 60px;
        opacity: 1;
        padding: 2px; 
    }            
    .smlicn img{
        opacity: .4;
        -webkit-transition: opacity 1s ease-in-out;
        -moz-transition: opacity 1s ease-in-out;
        -ms-transition: opacity 1s ease-in-out;
        -o-transition: opacity 1s ease-in-out;
        transition: opacity 1s ease-in-out;}
    #destination-image img{
        margin-bottom: 15px;
    }

</style>
<main>

    <div class="dshbrdin">
        <div class="container">
            <?php 
            
            
            if (!empty($storeIcons)) {
                ?>
                <div class="row"> 

                    <div class="col-md-4"> 
                        <form id="select_icon" action="<?php echo URL::to('select-store-icon'); ?>" method="POST">

                            <h5>Select Your Brand Icon</h5>
                            <div class="icnbx">                               
                                <div id='destination-image' class="text-center"> 
                                    @if(isset($storeIcons[0]->image_id))
                                    <img src="<?php echo $imageUrl= getAttachmentUrlById((int) $storeIcons[0]->image_id, $size = 'icons'); ?>" alt="<?php echo isset($storeIcons[0]->name) ? $storeIcons[0]->name : '' ?>" title="<?php echo isset($storeIcons[0]->name) ? $storeIcons[0]->name : '' ?>">
                                    @endif
                                    <h5 class="text-center icon-name"><?php echo isset($storeIcons[0]->name) ? $storeIcons[0]->name : '' ?></h5>
                                    <p class="text-center icondescription" ><?php echo isset($storeIcons[0]->description) ? $storeIcons[0]->description : ''; ?>.</p>                                    
                                </div>
                                {{ csrf_field() }}
                                <input type="hidden" name="icon_selected" id="icon_selected" value="<?php echo isset($storeIcons[0]->icon_id)?$storeIcons[0]->icon_id:''; ?>">
                                <div class="alert alert-warning text-center small" role="alert">Please select your brand icon carefuly, You cannot change this after confirmation.</div>
                                <button type="submit" id="icon-select" class="btn btn-primary btn-block">Select This Icon</button> 
                            </div>
                        </form>
                    </div> 

                    <div class="col-md-8"> 
                        <div class="row">
                            <div class="col-md-6"><h5>Available Icons</h5></div> 
                            <div class="input-group col-md-6">
                                <input class="form-control py-2 border-right-0 border" type="text" placeholder="search" id="iconsearch">
                                <span class="input-group-append">
                                    <button class="btn btn-outline-secondary border-left-0 border" id="btn_search" type="button">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </span>
                            </div>
                        </div> 
                        <div class="icnlst">
                            <?php
                            $count = 0;

                            foreach ($storeIcons as $si) {
                                $chk = "";
                                if ($count == 0) {
                                    $chk = "checked";
                                }
                                ?>
                                <label class="smlicn" data-toggle="tooltip"  data-placement="top" title="<?php echo $si->name; ?>">  
                                    <input type="radio" name="icon-pack" data-icon-name="<?php echo $si->name; ?>" data-icon-description="<?php echo $si->description; ?>" value="<?php echo $si->icon_id; ?>" <?php echo $chk; ?> />
                                    <img src="<?php echo getAttachmentUrlById((int) $si->image_id, $size = 'icons') ?>" alt="{{$si->name}}" title="{{$si->name}}">
                                </label>

                                <?php
                                $count++;
                            }
                            ?>

                        </div>
                        <div class=" ">
                            <?php
                            $q = [];
                            if (isset($_GET['status'])) {
                                $q['status'] = $_GET['status'];
                            }
                            if (isset($_GET['order'])) {
                                $q['order'] = $_GET['order'];
                            }
                            echo $storeIcons->appends($q)->links('pagination.defaultpagination');
                            ?>
                        </div>
                    </div>
                </div>
            <?php } else { ?>
                <h2>No record found</h2>
            <?php } ?>
        </div>
    </div>

</main>
<script>
$(document).ready(function () {
    $('[data-toggle="tooltip"]').tooltip();
});
$(document).on('click', 'input[name=icon-pack]', function () {
    //$('input[name=icon-pack]').on('click', function () {
    var src = $(this).next('img').attr('src');
    var icondescription = $(this).attr('data-icon-description');
    var name = $(this).attr('data-icon-name');
    $('#destination-image').children('img').attr('src', src);
    $(".icon-name").html(name);
    $(".icondescription").html(icondescription);
    selcectedIcon = $(this).val();
    $("#icon_selected").val(selcectedIcon);

});
$(document).on('click', '#btn_search', function () {
    var search = $("#iconsearch").val();
    var data = {};
    data['search'] = search;
    sendreq(data, 'icon', 'search', 'callBacksearch');
});
function callBacksearch(data) {
    $('.icnlst').html(data);
    $('[data-toggle="tooltip"]').tooltip();
}
$(function () {
    var url = "<?php echo URL::to('icon/search-complete'); ?>";
    $("#iconsearch").autocomplete({
        source: url,
        minLength: 3,
        select: function () {
            var search = $("#iconsearch").val();
            var data = {};
            data['search'] = search;
            sendreq(data, 'icon', 'search', 'callBacksearch');
        }
    });
});
$(document).on('click', '#icon-select', function (event) {
    event.preventDefault();
    if (confirm('Are you sure you want to select this store icon?')) {
        $("#select_icon").submit();
    } else {
        return false;
    }
});
$(document).on('click', ".page-link", function () {
    var link = $(this).attr("href");
    var search = $("#iconsearch").val();
    $.ajax({
        url: link,
        type: "post",
        data: {search: search},
        success: function (data) {
            $('.icnlst').html(data);
            $('[data-toggle="tooltip"]').tooltip();
        }
    });
    return false;
});

</script>
@endsection