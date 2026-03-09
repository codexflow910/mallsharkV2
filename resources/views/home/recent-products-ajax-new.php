<div class="n-prdctrow n-feature-on">
    <div class="container">
        <div class="row">           
            <?php
            $counter = 1;
            if (count($productsdata) == 0) {
                echo '<h3>No Record Found</h3>';
            }
            foreach ($productsdata as $data) {

                $achieved = getPercentageTarget($data->store_id);
                $image_url = getAttachmentUrlById((int) $data->feature_image, 'medium');
                $product_price = $data->price;
                if (!isImageExists($image_url)) {
                    $image_url = url(asset('assets/images/placeholder.jpg', env('HTTPS_STATUS')));
                }
                $store_info = getStoreInfoById($data->store_id);
                $store_name = isset($store_info[0]->store_name) ? $store_info[0]->store_name : '';
                $store_slug = isset($store_info[0]->store_slug) ? $store_info[0]->store_slug : '';
                $store_home = '/brand/' . $store_slug;
                $store_icon = $store_info[0]->store_icon;
                $storeIconData = getZAnimalById($store_icon);
                // $zanimalName = $storeIconData->name;
                // $zanimalDescription = $storeIconData->description;
                $image_id = getImageIdByIconId((int) $store_icon);
                // $iconUrl = getAttachmentUrlById((int) $image_id, 'icons');
                ?>

                <div class="col-md-3">
                    <!-- card starts -->
                    <div class="card">
                        <?php if ($counter == 1) { ?>
<!--                            <span class="n-featured" href="<?php //echo URL::to('/product/' . $data->slug); ?>"><img src="assets/images/featured.png"></span>  -->
                        <?php } ?>
                
                    
                        <a href="<?php echo URL::to('/product/' . $data->slug); ?>"><img src="<?php echo $image_url; ?>" class="card-img-top" alt="<?php echo !empty($data->title) ? $data->title : ""; ?>" title="<?php echo !empty($data->title) ? $data->title : ""; ?>"></a>
                        <div class="card-body">
                            <h4 class="card-title"><a href="<?php echo URL::to('/product/' . $data->slug); ?>"> <?php echo $data->title; ?></a></h4>
                            <p class="card-text prc">
                                <span class="n-price"><span class="z-symbl">USDC </span> <?php echo $product_price;?></span> 

                                <span  class="n-stars  push-right"><?php echo getProductReviewAverage($data->product_id); ?></span>
                            </p>
                            <!-- <p class="card-text small  ">
                                <span class="">Gave Back: USDC <?php //echo getProductGaveBack($data->product_id); ?></span> 
                               
                            </p> -->
                            <p class="card-text small  ">
                                <span class="">By: <a href="<?php echo $store_home; ?>"><?php echo $store_name; ?></a></span> 
                            </p>     
                        </div>
                    </div>
                    <!-- card ends-->

                </div>


                <?php
                $counter++;
                if ($counter == 5) {
                    break;
                }
            }
            ?>

        </div>
    </div>  
</div>
