                
<?php
$counter = 1;
if (count($productsdata) == 0) {
    echo '<h3>No Record Found</h3>';
}
foreach ($productsdata as $data) {

    $achieved = getPercentageTarget($data->store_id);
    $image_url = getAttachmentUrlById((int) $data->feature_image, 'medium');
    if (!isImageExists($image_url)) {
        $image_url = url(asset('assets/images/placeholder.jpg', env('HTTPS_STATUS')));
    }
    $store_info = getStoreInfoById($data->store_id);
    $store_name = isset($store_info[0]->store_name) ? $store_info[0]->store_name : '';
    $store_slug = isset($store_info[0]->store_slug) ? $store_info[0]->store_slug : '';
    $store_home = '/brand/' . $store_slug;
         $store_icon=$store_info[0]->store_icon;
                        $storeIconData = getZAnimalById($store_icon);
                        $zanimalName = $storeIconData->name;            
                        $image_id = getImageIdByIconId((int) $store_icon);
                        $iconUrl = getAttachmentUrlById((int) $image_id, 'icons');

    if ($counter == 1) {
        ?>


        <div class = "col-sm-6">
            <p class = "featured-block">Featured</p>
            <div class = "section_potd-block pos-rel">                                
                <div class="dropdown show animal-dropdown cstm-hgt">
                    <a class=" dropdown-toggle" href="<?php echo $store_home;?>" role="button" id="dropdownMenuLink" aria-expanded="false"  >
                        <span class="anml anmls" data-toggle="tooltip" data-placement="bottom" title="<?php echo !empty($zanimalName)?$zanimalName:""; ?>"> 
                            <img class="img-fluid" src="<?php echo $iconUrl;?>" alt="{{$storeIconData->name}}" title="{{$storeIconData->name}}">
                        </span> 
                    </a>
                </div>                
                <a href = "<?php echo URL::to('/product/' . $data->slug); ?>">                   
                    <img class = "img-fluid" src = "<?php echo $image_url; ?>" alt="Placeholder icon" title="Placeholder icon">
                </a>
                <div class = "poted-box-descp">
                    <h4>
                        <a href = "<?php echo URL::to('/product/' . $data->slug); ?>"> 
                        <?php echo $data->title; ?>
                        </a></h4>
                    <div class = "list-bottom-spot  w100p">
                        <div class = "option-list-spot">
                            <span> Campaign: <?php echo $achieved; ?>%</span> |
                           
                        </div>
                        <div class = "option-list-spot">
                            <div class = "display-inline-block">By: <a href="<?php echo $store_home; ?>"><?php echo $store_name; ?></a></div>
                            <div class = "display-inline-block stars-rating-cnt text-blue">
                                <?php echo getProductReviewAverage($data->product_id); ?>
                            </div>
                            <span> <?php echo productTotalRatings($data->product_id); ?> Ratings</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }


    if ($counter == 1) {
        ?>

        <div class="col-sm-6">

            <!-- section_project-collection-tabbed-block -->
            <div class="results-ui-list">
                <div id="most-recents" class="items-start " style="display:block">
                    <ul>
                    <?php } if ($counter > 1 && $counter < 5) { ?>
                        <li >
                            <div class="d-flex justify-content-between w100p">
                                <a href="<?php echo URL::to('/product/' . $data->slug); ?>" class="mr-20 animal-icon " style="display:block;" >
                                    <span class="anml anmls " data-toggle="tooltip" data-placement="bottom" title="<?php echo !empty($zanimalName)?$zanimalName:""; ?>"> 
                                         <img class="img-fluid" src="<?php echo $iconUrl;?>" alt="{{$storeIconData->name}}" title="{{$storeIconData->name}}">
                                    </span>

                                    <img class="img-fluid" src="<?php echo $image_url; ?>" alt="Placeholder icon" title="Placeholder icon">

                                </a>
                                <div class="basis60p-md basis80p">
                                    <div class="heading-large">
                                        <div class="heading-large" style="display:block">
                                            <a class="" href="<?php echo URL::to('/product/' . $data->slug); ?>" ><?php echo $data->title; ?></a>
                                        </div>
                                    </div>
                                    <div class="supported-div">
                                        <p style="display:block">
                                            <?php
                                            $productId = getTotalSupports($data->store_id);
                                            ?>
                                            <span>Supporters: <?php echo $productId; ?></span>
                                            <span>Given Back: <span class="text-blue zit-price">USDC <?php echo getProductGaveBack($data->product_id); ?></span>
                                            </span>
                                           
                                        </p>
                                    </div>
                                    <div class="stars-rating-cnt text-blue" style="display:block"> 
                                        <?php echo getProductReviewAverage($data->product_id); ?>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php } if ($counter == count($productsdata)) { ?>
                    </ul>



                </div><!-- Items-start -->


            </div>
        </div>
        <?php
    } $counter++;
}
?>
