<!-- Top Selling Proudcts -->
<?php
// id 5 for Top Selling of week
$filterId = 8;
$filterData = getFilterData($filterId);

if ($filterData['filter_status'] == 1) {
    if (!empty($featuredProducts) && count($featuredProducts)>=3) {

        $cl=3;
        if(count($featuredProducts)==3){
            $cl=4;
        }
        
        ?>
        <section class="n-prsecin">
            <div class="container">
                <div class="row">
                    <div class="col-md-12"><h3>Featured Products</h3></div>
                </div> 
            </div> 
            <div class="n-prdctrow">
                <div class="container">
                    <div class="row">
                        <?php
                        
                        foreach ($featuredProducts as $tsp) {
                            $image_url = getAttachmentUrlById((int) $tsp->feature_image, 'medium');
                            if (!isImageExists($image_url)) {
                                $image_url = url(asset('assets/images/placeholder.jpg', env('HTTPS_STATUS')));
                            }
                            $store_icon = $tsp->store_icon;
                            $storeIconData = getZAnimalById($store_icon);
                            $zanimalName = $storeIconData->name;
                            $image_id = getImageIdByIconId((int) $store_icon);
                            $iconUrl = getAttachmentUrlById((int) $image_id, 'icons');
                            $reviews = getProductRatingById($tsp->product_id);
                            ?>
                            <div class="col-md-<?php echo $cl; ?>">
                                <!-- card starts -->
                                <div class="card">
                                    <a class="n-animal" href="<?php echo URL::to('/brand/' . $tsp->store_slug); ?>" title="<?php echo!empty($zanimalName) ? $zanimalName : ""; ?>"><img src="{{ $iconUrl }} " alt="{{$storeIconData->description}}" title="{{$storeIconData->description}}"></a>     
                                    <a href="<?php echo URL::to('/product/' . $tsp->slug); ?>"><img src="<?php echo $image_url; ?>" class="card-img-top" alt="{{$tsp->title}}" title="{{$tsp->title}}"></a>
                                    <div class="card-body">
                                        <h4 class="card-title"><a href="<?php echo URL::to('/product/' . $tsp->slug); ?>"><?php echo!empty($tsp->title) ? $tsp->title : ""; ?></a></h4>
                                        <p class="card-text prc d-flex justify-content-between">
                                            <span class="n-price"><span class="z-symbl">USDC <?php echo!empty($tsp->price) ? $tsp->price : ""; ?></span> </span> 
                                            <span>
                                            <?php echo getProductReviewAverage($tsp->product_id); ?>
                                            </span>
                                        </p>
                                        <p class="card-text small  d-flex justify-content-between">
                                            <!-- <span class="">Given Back: USDC //{{number_format(getProductGaveBack($tsp->product_id),2)}}</span> -->
                                            <span>&nbsp;<span class="">By: <a href="<?php echo URL::to('/brand/' . $tsp->store_slug); ?>"><?php echo!empty($tsp->store_name) ? $tsp->store_name : ""; ?></a></span></span>
                                        </p>
                                    </div>
                                </div>
                                <!-- card ends-->
                            </div>
                        <?php } ?>


                    </div>
                </div>  
            </div>     
        </section>
    <?php }
} ?>
<!-- Top Selling Product Ends -->
