<?php
// id 8 for  flash sale products about to begin
$filterId = 8;
$filterData = getFilterData($filterId);
if ($filterData['filter_status'] == 1) {
     if (!$flashSaleAboutToBegain->isEmpty()) {
    ?>
<div class="brdr-tp">
    <div class="container flashsale-begin mt-4">
        <div class="row">
            <div class="col-md-12">
                <h3 class="cmn-hedn mt-4">Flash Sale About to Begin</h3>
                </div>
            <?php
           
                ?>
                <!-- Product big card -->
                <?php
                foreach ($flashSaleAboutToBegain as $fbsp) {
                    $image_url = getAttachmentUrlById((int) $fbsp->feature_image, 'medium');
                    if (!isImageExists($image_url)) {
                        $image_url = url(asset('assets/images/placeholder.jpg', env('HTTPS_STATUS')));
                    }
                    $store_icon = $fbsp->store_icon;
                    $storeIconData = getZAnimalById($store_icon);
                    $zanimalName = $storeIconData->name;
                    $image_id = getImageIdByIconId((int) $store_icon);
                    $iconUrl = getAttachmentUrlById((int) $image_id, 'icons');
                    $reviews = getProductRatingById($fbsp->product_id);
                    ?>
                    <div class="col-md-4">

                        <div class="card">
                            <a class="n-animal" href="<?php echo URL::to('/brand/' . $fbsp->store_slug); ?>" title="<?php echo!empty($zanimalName) ? $zanimalName : ""; ?>"><img src="{{ $iconUrl }}" alt="{{$fbsp->title}}" title="{{$fbsp->title}}"></a>     
                            <a href="<?php echo URL::to('/product/' . $fbsp->slug); ?>"><img src="<?php echo $image_url; ?>" class="card-img-top" title="{{$fbsp->store_name}}" alt="{{$fbsp->store_name}}"></a>
                            <div class="card-body no-padd-left no-padd-right">
                                <h5 class="card-title"><a href="<?php echo URL::to('/product/' . $fbsp->slug); ?>"><?php echo!empty($fbsp->title) ? $fbsp->title : ""; ?></a></h5>
                                <p class="card-text prc d-flex justify-content-between">
                                    <span class="n-price"><span class="z-symbl">
                                            USDC </span> <?php echo!empty($fbsp->price) ? $fbsp->price : ""; ?></span> 
                                            <span> <?php echo getProductReviewAverage($fbsp->product_id); ?></span>
                                </p>
                                <p class="card-text small  ">
                                    <span class="">By: <a href="<?php echo URL::to('/brand/' . $fbsp->store_slug); ?>"><?php echo!empty($fbsp->store_name) ? $fbsp->store_name : ""; ?></a></span>  
                                    <span class="push-right">Pledges Collected: <?php echo !empty($fbsp->pledges)?$fbsp->pledges:""; ?></span>
                                </p>

                            </div>
                        </div>
                    </div> 
                    <!-- Product big card ends -->

                    <?php
                }
            }
        }
        ?>
    </div>
</div>
</div>