
<script type="text/javascript" src="{{ URL::asset('assets/js/jquery.plugin.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/js/jquery.countdown.new.js') }}"></script>
<?php
// id 6 for feature flash sale
$filterId = 6;
$filterData = getFilterData($filterId);

if (!$flashsalesProduct->isEmpty() || !empty($featureFlashsale)) {
    ?>
    <section class="n-flashbx">
        <div class="n-flashstrp purple-bx">
            <div class="container">
                <div class="curve-sale"></div>
            Flashsale Offers <span>75%</span> Gave Back
            </div>
        </div>
    <?php } ?>
    <div class="container">
        <div class="row">
            <?php
            if ($filterData['filter_status'] == 1) {
                if (!empty($featureFlashsale)) {
                    $time = getFlashSaleDate($featureFlashsale->product_id);
                    $image_url = getAttachmentUrlById((int) $featureFlashsale->feature_image, 'product');
                    if (!isImageExists($image_url)) {
                        $image_url = url(asset('assets/images/placeholder.jpg', env('HTTPS_STATUS')));
                    }
                    $store_icon = $featureFlashsale->store_icon;
                    $storeIconData = getZAnimalById($store_icon);
                    $zanimalName = $storeIconData->name;
                    $image_id = getImageIdByIconId((int) $store_icon);
                    $iconUrl = getAttachmentUrlById((int) $image_id, 'icons');
                    ?>
                    <!-- Flash Sale Feature -->
                    <div class="col-md-12 ">
                        <div class="nflash-card">
                            <div class="row">
                                <div class="col-md-5">
                                    <a href="<?php echo URL::to('/product/' . $featureFlashsale->slug); ?>"><span class="n-featured"><img src="{{ asset('assets/images/featured.png',env('HTTPS_STATUS')) }}" alt="feature icon" title="feature icon"></span>
                                        <span class=""><img class="img-fluid" src="<?php echo $image_url; ?>" alt="placeholder icon" title="placeholder icon"></span>
                                    </a>    
                                </div>
                                <div class="col-md-7">
                                    <h4><a href="<?php echo URL::to('/product/' . $featureFlashsale->slug); ?>"><?php echo!empty($featureFlashsale->title) ? $featureFlashsale->title : ""; ?></a></h4>
                                    <div class="n-timebox">
                                        <div id="countdown-campaign">

                                        </div>
                                    </div>
                                    <p class="n-price"><span class="z-symbl">
                                            <span class="z-symbl"><img src="<?php  echo URL::asset('assets/images/z-symbal.png',env('HTTPS_STATUS')); ?>" alt="zit icon" title="zit icon" class="img-fluid"></span></span>
                                        <?php echo!empty($featureFlashsale->price) ? $featureFlashsale->price : ""; ?></p>
                                    <p class="mb10">
                                        <?php echo getProductReviewAverage($featureFlashsale->product_id); ?>&nbsp;  <?php echo productTotalRatings($featureFlashsale->product_id); ?> Ratings   
                                    </p> 
                                    <p class="small">
                                        <span>Gave Back: {{number_format(getProductGaveBack($featureFlashsale->product_id),2)}}</span>&nbsp;<span class="">By: <a href="<?php echo URL::to('/brand/' . $featureFlashsale->store_slug); ?>"><?php echo!empty($featureFlashsale->store_name) ? $featureFlashsale->store_name : ""; ?></a></span> 
                                    </p>    
                                </div>    
                            </div>
                        </div>
                    </div>    
                <?php }
            }
            ?>
            <!-- Flash Sale Feature Ends -->
            <?php
            // id 7 for  flash sale products
            $filterId = 7;
            $filterData = getFilterData($filterId);
            if ($filterData['filter_status'] == 1) {
                if (!$flashsalesProduct->isEmpty()) {
                    ?>
                    <!-- Product big card -->
                    <?php
                    foreach ($flashsalesProduct as $fsp) {
                        $image_url = getAttachmentUrlById((int) $fsp->feature_image, 'medium');
                        if (!isImageExists($image_url)) {
                            $image_url = url(asset('assets/images/placeholder.jpg', env('HTTPS_STATUS')));
                        }
                        $store_icon = $fsp->store_icon;
                        $storeIconData = getZAnimalById($store_icon);
                        $zanimalName = $storeIconData->name;
                        $image_id = getImageIdByIconId((int) $store_icon);
                        $iconUrl = getAttachmentUrlById((int) $image_id, 'icons');
                        $reviews = getProductRatingById($fsp->product_id);
                        ?>
                        <div class="col-md-4">

                            <div class="card">
                                <a class="n-animal" href="<?php echo URL::to('/brand/' . $fsp->store_slug); ?>" title="<?php echo!empty($zanimalName) ? $zanimalName : ""; ?>"><img src="{{ $iconUrl }}" title="{{ $storeIconData->name}}" alt="{{ $storeIconData->name}}"></a>     
                                <a href="<?php echo URL::to('/product/' . $fsp->slug); ?>"><img src="<?php echo $image_url; ?>" class="card-img-top" title="placeholder icon" alt="placeholder icon"></a>
                                <div class="card-body">
                                    <h5 class="card-title"><a href="<?php echo URL::to('/product/' . $fsp->slug); ?>"><?php echo!empty($fsp->title) ? $fsp->title : ""; ?></a></h5>
                                    <p class="card-text prc d-flex justify-content-between ">
                                        <span class="n-price">
                                            <span class="z-symbl">
                                                <img src="{{ asset('assets/images/z-symbal.png',env('HTTPS_STATUS')) }}" alt="zit icon" title="zit icon" class="img-fluid"></span> 
                                                    <?php echo!empty($fsp->price) ? $fsp->price : ""; ?>
                                        </span> 
                                        <span class="str-btx">  <?php echo getProductReviewAverage($fsp->product_id); ?> </span>
                                    </p>
                                    <p class="card-text small  ">
                                        <span class="">By: <a href="<?php echo URL::to('/brand/' . $fsp->store_slug); ?>"><?php echo!empty($fsp->store_name) ? $fsp->store_name : ""; ?></a></span>  
                                        <span class="push-right">Gave Back: {{number_format(getProductGaveBack($fsp->product_id),2)}}</span>
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
<?php if (!empty($time)) { ?>
        <script>
    $(function () {
        var austDay = new Date("<?php echo $time; ?>");
        // austDay = new Date(austDay.getFullYear() + 1, 1 - 1, 1);
        $('#countdown-campaign').countdown({onExpiry: liftOff, until: austDay, format: 'HMS'});
        $('#year').text(austDay.getFullYear());
    });
    function liftOff() {
        window.location.reload();
    }
        </script>
<?php } ?>
</section>