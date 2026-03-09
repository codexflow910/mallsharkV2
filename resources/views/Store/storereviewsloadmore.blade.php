<?php
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
                                   
                                    if ($user_avatar != '') {
                                        $image_url = getAttachmentUrlById((int)$user_avatar, 'media');
                                    }
                                    ?>
                                    <img src="<?php  echo $image_url;?>" alt="{{$sr['name']}}" title="{{$sr['name']}}" class="img-fluid" >

                                </div>
                            </div>
                            <div class="col-sm-10-cell no-padd-left">
                                <div class="body-review-cntiner">
                                    <div class="row-review-head">
                                        <span class="name-show-rev"><?php echo $sr['name']; ?></span>
                                        <span class="seprator-line"> | </span>
                                        <span class="date-reivew-rev"><?php   $timestamp = strtotime($sr['created_at']);
                                         echo    $date = date('d-m-Y', $timestamp); ?></span>
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

        <?php }
}?>