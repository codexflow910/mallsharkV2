@extends('layouts.mobile-main')
@section('content')

<main> 
    <!-- Dashboard Starts --> 
    <!-- Dashboard Ends -->
    <div class="dshbrdin">
        <div class="container">
            <?php if (!empty($storeIcons)) {
                ?>
                <div class="row"> 

                    <div class="col-md-12"> 
                        <h5>Select Your Brand Icon</h5>
                        <div class="input-group mb-20 mt-10" id="input-search-icon">
                            <input class="form-control py-2 border-right-0 border" type="text" placeholder="search" id="iconsearch">
                            <span class="input-group-append">
                                <button class="btn btn-outline-secondary border-left-0 border" id="btn_search" type="button">
                                    <i class="fa fa-search"></i>
                                </button>
                                <button class="btn btn-outline-secondary border-left-0 border" id="search-rest-icon" type="button" onclick="location.href = '/select-store-icon';">
                                    <i class="fa fa-close"></i>
                                </button>                                
                            </span>
                        </div>
                        <div class="icnbx">
                            <p class="text-center" id="destination-image"><img src="<?php echo getAttachmentUrlById((int) $storeIcons[0]->image_id, $size = 'icons') ?>" alt="<?php echo isset($storeIcons[0]->name) ? $storeIcons[0]->name : '' ?>" title="<?php echo isset($storeIcons[0]->name) ? $storeIcons[0]->name : '' ?>"></p>
                            <h5 class="text-center icon-name"><?php echo isset($storeIcons[0]->name) ? $storeIcons[0]->name : '' ?></h5>
                            <p class="text-center icondescription"><?php echo isset($storeIcons[0]->description) ? $storeIcons[0]->description : ''; ?>.</p>
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
                            <form action="<?php echo URL::to('select-store-icon'); ?>" method="POST">
                                {{ csrf_field() }}
                                <input type="hidden" name="icon_selected" id="icon_selected" value="<?php echo $storeIcons[0]->icon_id; ?>">
                                <div class="alert alert-warning text-center small" role="alert">Please select your brand icon carefuly, You cannot change this after confirmation.</div>
                                <button type="submit" class="btn btn-primary btn-block">Select This Icon</button> 
                            </form>
                        </div>
                    </div> 
                </div>
            <?php } else { ?>
                <h2>No record found</h2>
            <?php } ?>
        </div>
    </div>
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
            var search = $("#iconsearch").val();
            if (search != '') {
                $("#input-search-icon").addClass("icon-search-mbt");
            }
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
</main>
@endsection