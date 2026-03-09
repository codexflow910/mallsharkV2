@extends('layouts.new-main')
@section('content')
@section('title', 'Brand Reviews');
<main>
    <div class="strhd">
        <div class="container-fluid">
            <div class="row">
                <div class="col hdlft">
                    <span>
                        <?php
                        if (count($totalRating) > 0) {
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $totalRating['product_rating']) {
                                    $class = "fa fa-star";
                                } else {
                                    $class = "fa fa-star-o";
                                }
                                ?>
                                <i class = "<?php echo $class; ?>" aria-hidden = "true"></i>
                                <?php
                            }
                        } else {
                            ?>

                            <i class="fa fa-star-o" aria-hidden="true"></i>
                            <i class="fa fa-star-o" aria-hidden="true"></i>
                            <i class="fa fa-star-o" aria-hidden="true"></i>
                            <i class="fa fa-star-o" aria-hidden="true"></i>
                            <i class="fa fa-star-o" aria-hidden="true"></i>
                        <?php } ?>
                        <b><?php echo $totalRating['review_count']; ?></b> Rating<?php
                        if ($totalRating['review_count'] > 0) {
                            echo 's';
                        } else {
                            echo '';
                        }
                        ?></span>
                    <br>
                    <span class="small"><?php
                        if ($storeSales > 1) {
                            echo $storeSales . " Sales";
                        } else {
                            echo $storeSales . " Sale";
                        }
                        ?>     &nbsp;| &nbsp;   Since <?php
                        $timestamp = strtotime($storeData['created_at']);
                        echo $date = date('Y', $timestamp);
                        ?>    &nbsp;| &nbsp;   <?php echo $totalProducts; ?> Items</span>

                </div>
                <div class="col strlgo hdcnt">
                    <?php
                    if (!empty($storeData['store_logo'])) {
                        $imageUrl = getAttachmentUrlById((int) $storeData['store_logo'], 'small');
                        ?>
                        <a href="<?php echo URL::to('/brand') . "/" . $storeId; ?>">
                            <img src="<?php echo $imageUrl; ?>" alt="<?php echo $storeData['shop_name']; ?>" title="<?php echo $storeData['shop_name']; ?>"></a>
                    <?php } else { ?>
                        <h3><?php echo $storeData['shop_name']; ?></h3>
                    <?php } ?>
                </div>      
                <div class="col hdrght">
                    <button class="btn btn-primary btn-simple">Follow Us</button>
                    &nbsp; &nbsp;
                    <button class="btn btn-primary">Become Ambassador</button> 
                </div>      
            </div>
        </div>

    </div>

    <!-- Store Header Ends-->   

    <!-- Store Features Starts -->

    <div class="strftr">
        <div class="container-fluide">
            <div class="row">
                <div class="col-md-2">
                    <div class="card">
                        <a class="fvrt" href="#"><span><i class="fa fa-heart-o" aria-hidden="true"></i></span></a> 
                        <a href="#"><img src="{{ asset('assets/images/prdct.png',env('HTTPS_STATUS')) }}" alt="product icon"  title="product icon" style="width:100%"></a>
                        <div class="card-body">
                            <h5 class="card-title"><a href="#">1.09 Ct Natural Loose Diamond Slice Shape Blue Color 8 Pcs L8972</a></h5> 
                            <div class="infbx small  justify-content-between">
                                <div>
                                    <span>Supporters: <b>4356</b></span>
                                    &nbsp; | &nbsp;
                                    <span>Price: <b>$235</b></span> 
                                </div> 

                            </div>  
                        </div>
                    </div>
                </div>


            </div>           
        </div>   
    </div>      

    <!-- Store Features Ends -->  

    <!-- Store Nav Starts -->
    @include('Store.storehomesidebar')
    <div class="main-container">
        <!-- Store Nav Ends -->
        <?php
        //print_rr($storeReviews);
        if (!empty($storeReviews)) {
            foreach ($storeReviews['data'] as $sr) {
                ?>
                <div class="container mt-40">
                    <div class="row">
                        <div class="col-sm-12">    

                            <div class="row-table">
                                <div class="col-sm-2-cell">
                                    <div class="image-review-cnt">

                                        <?php
                                        $user_avatar = $sr['user_avatar'];
                                        $name = "";
                                        if ($user_avatar != '') {

                                            $image_url = getAttachmentUrlById((int) $user_avatar, 'media');
                                            ?>
                                            <img src="<?php echo $image_url; ?>" alt="{{$sr['name']}}" class="img-fluid" >
                                        <?php
                                        } else {

                                           
                                            $username = $sr['name'];
                                            $words = preg_split("/\s+/", $username);
                                            foreach ($words as $w) {
                                                $name .= strtoupper($w[0]);
                                            }
                                        }
                                        ?>


                                        <span> <span class="name-show-header"><?php echo $name; ?></span></span>

                                    </div>
                                </div>
                                <div class="col-sm-10-cell no-padd-left">
                                    <div class="body-review-cntiner">
                                        <div class="row-review-head">
                                            <span class="name-show-rev"><?php echo $sr['name']; ?></span>
                                            <span class="seprator-line"> | </span>
                                            <span class="date-reivew-rev"><?php
                                $timestamp = strtotime($sr['created_at']);
                                echo $date = date('d-m-Y', $timestamp);
                                        ?></span>
                                            <span class="rating-start-right pull-right">
                                                <div class="rating-strip">
                                                    <?php
                                                    $average = $sr['rating'];
                                                    for ($i = 1; $i <= 5; $i++) {
                                                        if ($i <= $average) {
                                                            $class = "fa fa-star";
                                                        } else {
                                                            $class = "fa fa-star-o";
                                                        }
                                                        ?>
                                                        <i class = "<?php echo $class; ?>" aria-hidden = "true"></i>
                                                    <?php } ?>
                                                </div>
                                            </span>
                                        </div>
                                        <div class="body-review-text-bdy">
                                            <p><?php echo $sr['comment'] ?></p>
                                        </div>
                                    </div>
                                </div>

                            </div>


                        </div>
                    </div>
                </div>

            <?php } ?>
        </div>
        <div class="container "> 
            <div class="row">
                <div class="col-md-12 text-center" ><p>
                        <button class="btn  btn-primary btn-simple loadmore" data-store-id="<?php echo $storeId; ?>" data-page="<?php echo 2; ?>">Load More</button>
                    </p>
                </div> 
            </div>
            <!-- Loading Button Ends -->   
        </div>


        <!-- Loading Button Ends -->   


    <?php } else {
        ?>
        No record Found;
    <?php } ?>
</main>
<script>
    var counter = 2;
    var perPage = '<?php echo $storeReviews['last_page']; ?>';
    $(document).ready(function () {
        if (counter > perPage) {
            $(".loadmore").hide()
        }
        $(".loadmore").on("click", function () {
            if (counter > perPage) {
                $(".loadmore").hide();
            } else {
                storeId = "<?php echo $storeId ?>";
                href = $("#site_url").val() + '/brand-loadreviews/<?php echo $storeId ?>?page=' + counter;
                $.ajax({
                    url: href,
                    type: 'GET',
                    data: storeId,
                    success: function (data) {
                        $(".main-container").append(data);
                        counter = counter + 1;
                        if (counter > perPage) {
                            $(".loadmore").remove();

                        }
                    },
                    beforeSend: function () {
                    }
                });
            }

        });
    });
</script>
@endsection




