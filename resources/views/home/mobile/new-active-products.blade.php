<div class="reward-campaigns-container  bb-1px award-campaigns-rec recent-products-compaign">
    <div class="container" style="position:relative;">
        <div class="row">
            <div class="col-sm-12">
                <h3 class="homePage-heading">Active Reward Campaigns</h3>
            </div>
            <div class="col-sm-12">
                <div class="reward-conainer-filter slickSliderForMobile">
                    <?php
                    $categoreis = getAllActiveCategories(10);
                    $counter_all = 0;
                    if (count($categoreis) > 0) {
                        echo '<ul>';
                        foreach ($categoreis as $cat) {
                            $clss_first = '';
                            $activeClass = "";
                            if ($counter_all == 0) {
                                $clss_first = 'firstcategory';
                                $activeClass = 'active';
                            }
                            if ($counter_all == 7) {
                                echo '<div class="hide-list-container" style="display:none">';
                            }
                            echo '<li class="' . $clss_first . '">
                            <a href="javascript:void(0);" class="' . $activeClass . ' getactivedata ' . $clss_first . '" data-related="' . $cat->id . '" data-slugurl="'.URL::to('/category?name=').$cat->category_slug.'">' . $cat->categoryTitle . '</a>
                            </li>';
                            if ($counter_all == count($categoreis) - 1) {
                                echo '</div>';
                            }
                            $counter_all++;
                        }

                        echo '</ul>';
                    }
                    ?>
                    <a href="javascript:;" class="show-more-results">Show More</a>
                </div>
                <!-- reward-conainer-filter -->
            </div>
        </div>
        <div class="grid-container">
            <div class="row filtered-boxed ajax-response">
                <!-- xxx -->
            </div>
            <!-- Product box ishi starts -->
            <div class="row filtered-boxed ">
            <div class="dealbx col">
    <div class="row">
        <div class="col-md-12 text-center">
            <h3 ><b>Super Deals Ends In</b></h3>
            <div id="countdown">
                <ul class="d-flex text-center counterin">
                <li><span id="days">198</span>Days</li>
                <li><span>:</span></li>
                <li><span id="hours">8</span>Hours</li>
                <li><span>:</span></li>
                <li><span id="minutes">32</span>Minutes</li>
                <li><span>:</span></li>
                <li><span id="seconds">38</span>Seconds</li>
                </ul>
                <hr>
            </div>
        </div>
    </div>
            </div>    
             
            <div class="col-6">
                
                <!-- card starts -->
                <div class="card mobcrd">
                            <!--<span class="n-featured" href=""><img src="assets/images/featured.png"></span>  -->                    
                            <a href="https://dev.mallshark.net/product/1978-rushing-leaders"><img src="https://dev.mallshark.net/uploads/medium/20230314_160244_2044.jpg" class="card-img-top" alt="1978 Rushing Leaders" title="1978 Rushing Leaders"></a>
                            <div class="card-body">
                                <h4 class="card-title"><a href="https://dev.mallshark.net/product/1978-rushing-leaders">Retro Art Paining Replica</a></h4>
                                <p class="card-text prc">
                                    <span class="n-price"><span class="z-symbl">USDC </span> 18</span> 

                                    <span class="n-stars  push-right"><i class="fa fa-star-o" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i></span>
                                </p>
                                <!-- <p class="card-text small  ">
                                    <span class="">Gave Back: USDC </span> 
                                
                                </p> -->
                                <p class="card-text small  ">
                                    <span class="">By: <a href="/brand/shark-tank">Shark Tank</a></span> 
                                </p>     
                            </div>
                        </div>
                        <!-- card ends-->

            </div>
            <div class="col-6">
                
                    <!-- card starts -->
                    <div class="card mobcrd">
                        <!--<span class="n-featured" href=""><img src="assets/images/featured.png"></span>  -->                    
                        <a href="https://dev.mallshark.net/product/1978-rushing-leaders"><img src="https://dev.mallshark.net/uploads/medium/20230314_160244_2044.jpg" class="card-img-top" alt="1978 Rushing Leaders" title="1978 Rushing Leaders"></a>
                        <div class="card-body">
                            <h4 class="card-title"><a href="https://dev.mallshark.net/product/1978-rushing-leaders"> 1978 Rushing Leaders</a></h4>
                            <p class="card-text prc">
                                <span class="n-price"><span class="z-symbl">USDC </span> 18</span> 

                                <span class="n-stars  push-right"><i class="fa fa-star-o" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i></span>
                            </p>
                            <!-- <p class="card-text small  ">
                                <span class="">Gave Back: USDC </span> 
                               
                            </p> -->
                            <p class="card-text small  ">
                                <span class="">By: <a href="/brand/shark-tank">Shark Tank</a></span> 
                            </p>     
                        </div>
                    </div>
                    <!-- card ends-->

            </div> 

            <div class="col-6">
                
                <!-- card starts -->
                <div class="card mobcrd">
                            <!--<span class="n-featured" href=""><img src="assets/images/featured.png"></span>  -->                    
                            <a href="https://dev.mallshark.net/product/1978-rushing-leaders"><img src="https://dev.mallshark.net/uploads/medium/20230314_160244_2044.jpg" class="card-img-top" alt="1978 Rushing Leaders" title="1978 Rushing Leaders"></a>
                            <div class="card-body">
                                <h4 class="card-title"><a href="https://dev.mallshark.net/product/1978-rushing-leaders">Retro Art Paining Replica</a></h4>
                                <p class="card-text prc">
                                    <span class="n-price"><span class="z-symbl">USDC </span> 18</span> 

                                    <span class="n-stars  push-right"><i class="fa fa-star-o" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i></span>
                                </p>
                                <!-- <p class="card-text small  ">
                                    <span class="">Gave Back: USDC </span> 
                                
                                </p> -->
                                <p class="card-text small  ">
                                    <span class="">By: <a href="/brand/shark-tank">Shark Tank</a></span> 
                                </p>     
                            </div>
                        </div>
                        <!-- card ends-->

            </div>

            <div class="col-6">
                
                <!-- card starts -->
                <div class="card mobcrd">
                            <!--<span class="n-featured" href=""><img src="assets/images/featured.png"></span>  -->                    
                            <a href="https://dev.mallshark.net/product/1978-rushing-leaders"><img src="https://dev.mallshark.net/uploads/medium/20230314_160244_2044.jpg" class="card-img-top" alt="1978 Rushing Leaders" title="1978 Rushing Leaders"></a>
                            <div class="card-body">
                                <h4 class="card-title"><a href="https://dev.mallshark.net/product/1978-rushing-leaders">Retro Art Paining Replica</a></h4>
                                <p class="card-text prc">
                                    <span class="n-price"><span class="z-symbl">USDC </span> 18</span> 

                                    <span class="n-stars  push-right"><i class="fa fa-star-o" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i></span>
                                </p>
                                <!-- <p class="card-text small  ">
                                    <span class="">Gave Back: USDC </span> 
                                
                                </p> -->
                                <p class="card-text small  ">
                                    <span class="">By: <a href="/brand/shark-tank">Shark Tank</a></span> 
                                </p>     
                            </div>
                        </div>
                        <!-- card ends-->

            </div>
                 
            </div>
            <!-- Product box ishi ends -->
            <div class="view-al text-right">
                <a href="{{route('home.search')}}" class="viewalllink">View All</a>
            </div>
            <div class="uploadActiveImages" style="display: block;">
                <span></span>
            </div>
        </div>


        <!-- grid-container -->
    </div>
</div>
<style>.showmore{
        display:none;
    }
</style>
<script>
    jQuery(document).ready(function () {

        jQuery('.firstcategory').click();
        jQuery('.uploadActiveImages').hide();
    });
    jQuery(document).on('click', '.show-more-results', function () {

        jQuery('li.showmore').slideToggle();
    })
    jQuery(document).on('click', '.getactivedata', function () {
        catid = jQuery(this).attr('data-related');
        slugurl = jQuery(this).attr('data-slugurl');
        jQuery('.viewalllink').attr('href',slugurl);
        if (catid != '') {
            jQuery('.getactivedata').removeClass('active');
            jQuery(this).addClass('active');
            jQuery('.uploadActiveImages').show();
            jQuery.ajax({
                url: '/getactive',
                method: 'POST',
                Type: 'POST',
                data: 'catid=' + catid,
                success: function (data) {
                    jQuery('.ajax-response').html(data);
                    jQuery('.uploadActiveImages').hide();
                }
            });
        }
    });




</script>