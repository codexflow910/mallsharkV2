<?php
// id 4 for brand of week
$filterId = 4;
$filterData = getFilterData($filterId);
if ($filterData['filter_status'] == 1) {
    if (!$topBrands->isEmpty()) {
        ?>

        <section class="n-topbrands">
            <div class="container">
                <div class="row">
                    <div class="col-md-12"><h3>Top brands of the week</h3></div>
                </div>

                <!--Brand Bar Starts -->
                <div class="caontainer n-brandbar">
                    <div class="row">
        <?php
        $counter = 0;
                    foreach ($topBrands as $tb) {
                            if(!empty($tb->wallet_address)){
                            $image_url = getAttachmentUrlById((int) $tb->store_logo, 'medium');
                            if (!isImageExists($image_url)) {
                                $image_url = url(asset('assets/images/placeholder.jpg', env('HTTPS_STATUS')));
                            }
                            if ($counter == 0) {
                                ?>
                                <div class="col-md-3 n-1st-top">
                                    <a class="n-iconbx-big" href="<?php echo URL::to('/brand/' . $tb->store_slug); ?>">
                                        <img src="<?php echo $image_url; ?>" alt="{{ $tb->store_slug}}" title="{{ $tb->store_slug}}">
                                    </a>
                                </div>
                                <div class="col-md-9 n-other-top d-flex justify-content-between"> 
                                    <?php
                                }
                                if ($counter != 0) {
                                    ?>
                                    <a class="n-iconbx d-flex" href="<?php echo URL::to('/brand/' . $tb->store_slug); ?>">
                                        <span class="align-self-center display-inline-block">
                                        <img src="<?php echo $image_url; ?>" class="img-fluid" alt="{{ $tb->store_slug}} " title="{{ $tb->store_slug}} ">
                                        </span>
                                    </a>
                                    <?php
                                }
                                $counter++;
                            } 
                        }
                            ?>
                                    
                        </div>    
                    </div>
                </div>
                <!-- Brand Bar Ends -->    
            </div>
        </section>
    <?php }
}
?>