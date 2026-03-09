                
<?php
$counter = 1;
if(count($productsdata)==0){
    echo '<h3>No Record Found</h3>';
}
foreach ($productsdata as $data) {
    
    $image_url = getAttachmentUrlById((int) $data->feature_image, 'medium');
    if (!isImageExists($image_url)) {
        $image_url = url(asset('assets/images/placeholder.jpg', env('HTTPS_STATUS')));
    }
    if ($counter == 1) {
        ?>


        <div class = "col-sm-6">
            <p class = "featured-block">Featured</p>
            <div class = "section_potd-block">
                <a href = "<?php echo URL::to('/product/' . $data->slug); ?>">
                    <img class = "img-fluid" src = "<?php echo $image_url; ?>" alt ="Placeholder icon" title ="Placeholder icon">
                </a>
                <div class = "poted-box-descp">
                    <h4><?php echo $data->title; ?></h4>
                    <div class = "list-bottom-spot  w100p">
                        <div class = "option-list-spot">
                            <span> Campaign: 27%</span> |
                            <span>Pledges Needed: 222</span>
                        </div>
                        <div class = "option-list-spot">
                            <div class = "display-inline-block">By: jagoillustrations</div>
                            <div class = "display-inline-block stars-rating-cnt text-blue">
                                <span class = "fa fa-star"></span>
                                <span class = "fa fa-star"></span>
                                <span class = "fa fa-star"></span>
                                <span class = "fa fa-star"></span>
                                <span class = "fa fa-star"></span>
                            </div>
                            <span>445 Ratings</span>
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
                <div id="most-recents" class="items-start" style="display:block">
                    <ul>
                    <?php } if($counter>1 && $counter<5) {?>
                        <li >
                        <div class="d-flex justify-content-between w100p">
                            <a href="<?php echo URL::to('/product/' . $data->slug); ?>" class="mr-20" style="display:block;">

                                <img class="img-fluid" src="<?php echo $image_url; ?>" alt = "Placeholder icon" title="Placeholder icon">

                            </a>
                            <div class="basis60p-md basis80p">
                                <div class="heading-large">
                                  <div class="heading-large" style="display:block">
                                    <a class="" href="<?php echo URL::to('/product/' . $data->slug); ?>" ><?php echo $data->title; ?></a>
                                  </div>
                                </div>
                                <div class="supported-div">
                                    <p style="display:block">
                                        <span>Supporters: 4367</span>
                                        <span>Given Back: 
                                            <span class="text-blue zit-price">USDC 4666</span>
                                        </span>
                                        <span>Pledges Needed: 255</span>
                                    </p>
                                </div>
                                <div class="stars-rating-cnt text-blue" style="display:block"> 
                                    <span class="fa fa-star"></span>
                                    <span class="fa fa-star"></span>
                                    <span class="fa fa-star"></span>
                                    <span class="fa fa-star"></span>
                                    <span class="fa fa-star"></span>
                                </div>
                            </div>
                        </div>
                    </li>
                    <?php } if ($counter == count($productsdata)) { ?>
                    </ul>



                </div><!-- Items-start -->


            </div>
        </div>
    <?php } $counter++;
}
?>
