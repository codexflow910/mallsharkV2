@extends('layouts.new-main')
<!-- Main Banner Starts -->
@section('title', 'Reviews')
@section('content')
<?php
//print_rr($orders);
?>
<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div id="review-tab" class="tab-pane  in mt-30">
                <div class="reviews-section-rel">
                    <h3 class="mt-20 mb-40">Your Reviews</h3>
                    <?php
                    if ($orders) {
                        foreach ($orders as $ored) {
                            foreach ($ored as $ore) {

                                $order_id = $ore->order_id;
                                $item_id = $ore->item_id;
                                $rating = $ore->rating;
                                $comment = $ore->comment;
                                $date_orderd = $ore->created_at;
                               
                                    $product_info=getProductById($item_id);
                                    print_rr($product_info);
                                    $product_title=isset($product_info[0]->title )? $product_info[0]->title:'';
                                    $product_thumb=isset($product_info[0]->title )? $product_info[0]->title:''
                                
                                ?>
                                <div class="row review-repeatr-list">
                                    <div class="col-sm-5 nt-pn">
                                        <div class="row">
                                            <div class="col-sm-3 pl-0 pr-0">
                                                <div class="review-thmb-img">
                                                    <img class="img-fluid" src="https://stage.ecommedge.com/uploads/product/U3O36j0BqTxMR5F_68.png" alt="Project">  
                                                </div>
                                            </div>
                                            <div class="col-sm-9 ">
                                                <h5 class="mb-0">{{$product_title}}</h5>
                                                <p class="fnt-12 mt-5 mb-0"> Order Id: {{$order_id}}  &nbsp;&nbsp; |  &nbsp;&nbsp; Purchased Date: {{  date('F d, Y', strtotime($date_orderd)) }} </p>
                                                <div class="stars-rating-cnt text-blue mt-5" >
                                                    <?php
                                                    $html = '';
                                                    for ($i = 1; $i <= 5; $i++) {
                                                        if ($i <= $rating) {
                                                            $class = "fa fa-star";
                                                        } else {
                                                            $class = "fa fa-star-o";
                                                        }

                                                        $html .= '<i class = "' . $class . '" aria-hidden = "true"></i>';
                                                    }
                                                    ?>
                                                    <?php echo $html ;?>
                                                    </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-7">
                                        <div class="rviw-body">
                                            <h6 class="mb-0">Your Review</h6>
                                            <p class="mb-0 mt-5">{{$comment}}</p>
                                        </div>
                                    </div>
                                </div>
            <?php
        }
    }
}
?>
                    <div class="row review-repeatr-list">
                        <div class="col-sm-5 nt-pn">
                            <div class="row">
                                <div class="col-sm-3 pl-0 pr-0">
                                    <div class="review-thmb-img">
                                        <img class="img-fluid" src="https://stage.ecommedge.com/uploads/product/U3O36j0BqTxMR5F_68.png" alt="Project">  
                                    </div>
                                </div>
                                <div class="col-sm-9 ">
                                    <h5 class="mb-0">Product Name</h5>
                                    <p class="fnt-12 mt-5 mb-0"> Order Id: 34343  &nbsp;&nbsp; |  &nbsp;&nbsp; Purchased Date: 12 Dec 2017 </p>
                                    <div class="stars-rating-cnt text-blue mt-5" ><span class="fa fa-star"></span><span class="fa fa-star"></span><span class="fa fa-star"></span><span class="fa fa-star"></span><span class="fa fa-star"></span></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-7">
                            <div class="rviw-body"> 
                                <div class="pt-stn mt-15">
                                    <div class="inline-block post-btn-link">
                                        <a href="javascript:;">
                                            Post Review
                                        </a>
                                    </div>
                                    <div class="inline-block rtg-str">
                                        <span class="post-ratinghead">
                                            Post Your Rating:
                                        </span>
                                        <div class="inline-block str-rating-buttons">
                                            <div class="stars-rating-cnt text-blue mt-5"><span class="fa fa-star-o"></span><span class="fa fa-star-o"></span><span class="fa fa-star-o"></span><span class="fa fa-star-o"></span><span class="fa fa-star-o"></span></div> 
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div> 

                    <div class="row review-repeatr-list">
                        <div class="col-sm-5 nt-pn">
                            <div class="row">
                                <div class="col-sm-3 pl-0 pr-0">
                                    <div class="review-thmb-img">
                                        <img class="img-fluid" src="https://stage.ecommedge.com/uploads/product/U3O36j0BqTxMR5F_68.png" alt="Project">  
                                    </div>
                                </div>
                                <div class="col-sm-9 ">
                                    <h5 class="mb-0">Product Name</h5>
                                    <p class="fnt-12 mt-5 mb-0"> Order Id: 34343  &nbsp;&nbsp; |  &nbsp;&nbsp; Purchased Date: 12 Dec 2017 </p>
                                    <div class="stars-rating-cnt text-blue mt-5" ><span class="fa fa-star"></span><span class="fa fa-star"></span><span class="fa fa-star"></span><span class="fa fa-star"></span><span class="fa fa-star"></span></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-7">
                            <div class="rviw-body">
                                <h6 class="mb-0">Your Review</h6>
                                <p class="mb-0 mt-5">Everything arrived quickly, packaged well and exactly as it was described. Prices are fantastic too. Thank you for hooking it up!We are happy glass blowers</p>
                            </div>
                        </div>
                    </div>                    

                </div>


            </div><!-- menu2 -->
        </div>
    </div>
</div>

@endsection