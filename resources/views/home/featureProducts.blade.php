    @if(!empty($featureProducts) && count($featureProducts) > 0)
        <section class="n-prsecin">
            <div class="container">
                <div class="row">
                    <div class="col-md-12"><h3>Feature Products</h3></div>
                    <div class="view-al place-into-tap" style="top:0px;">
                    <a href="<?php echo URL::to('/feature-products'); ?>">View All</a>
                </div>
                </div> 
            </div> 
            <div class="n-prdctrow">
                <div class="container">
                    <div class="row">

                        
                        @foreach ($featureProducts as $fps) 
                            @php
                                $image_url = getAttachmentUrlById((int) $fps->feature_image, 'medium');
                                if (!isImageExists($image_url)) {
                                    $image_url = url(asset('assets/images/placeholder.jpg', env('HTTPS_STATUS')));
                                }
                                $reviews = getReviewAverage($fps->reviews);
                                //echo 'Data: <pre>' .print_r($fps->reviews,true). '</pre>';
                            @endphp
                            <div class="col-md-3">
                                <!-- card starts -->
                                <div class="card">
                                    <img  class="feature-lable" src="{{URL::asset('assets/images/featured-label.png')}}"/>
                                    <a href="{{URL::to('/product/' . $fps->slug);}}"><img src="{{$image_url}}" class="card-img-top" alt="{{$fps->title}}" title="{{$fps->title}}"></a>
                                    <div class="card-body">
                                        <h4 class="card-title"><a href="{{URL::to('/product/' . $fps->slug);}} ">{{!empty($fps->title) ? $fps->title : ""; }}</a></h4>
                                        <p class="card-text prc d-flex justify-content-between">
                                            <span class="n-price"><span class="z-symbl">USDC {{!empty($fps->price) ? $fps->price : 0.00;}}</span> </span> 
                                            <span>
                                            <?php echo $reviews; ?>
                                            </span>
                                        </p>
                                        <p class="card-text small  d-flex justify-content-between">
                                            <!-- <span class="">Given Back: USDC //{{number_format(getProductGaveBack($fps->product_id),2)}}</span> -->
                                            <span>&nbsp;<span class="">By: <a href="<?php echo URL::to('/brand/' . $fps->store->store_slug); ?>">{{ $fps->store->store_name}}</a></span></span>
                                        </p>
                                    </div>
                                </div>
                                <!-- card ends-->
                            </div>
                        @endforeach


                    </div>
                </div>  
            </div>     
        </section>
    @endif
<!-- Top Selling Product Ends -->
