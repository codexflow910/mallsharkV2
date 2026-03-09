<?php
// id 2 for recent products
$filterId = 2;
$filterData = getFilterData($filterId);
if ($filterData['filter_status'] == 1) {
    ?>

    <section class="reward-campaigns-container pb-30 award-campaigns-rec recent-products-compaign">
        <div class="container" style="position:relative;">
            <div class="row">
                <div class="col-md-12">
                    <h3>Recent Products</h3>
                </div>
                 <div class="view-al place-into-tap" style="top:0px;">
                    <a href="{{URL::to('/q')}}" class="viewalllink">View All</a>
                </div>
            </div>
            <div class="n-txtslider">
                <div class="col-left reward-conainer-filter">
                    <?php
                    $categoreis = getAllParentCategories(50);
                    $counter_all = 0;
                    if (count($categoreis) > 0) {
                        echo '<ul class="multiple-items-mbt DesktopVersion">';
                        foreach ($categoreis as $cat) {
                            $clss_first = '';
                            $activeClass = "";
                            if ($counter_all == 6) {
                                $clss_first = 'firstcat';
                                $activeClass = 'active';
                            }
                            if ($counter_all == 8) {
                               // echo '<div class="hide-list-container">';
                            }
                            echo '<li class="' . $clss_first . '">
                            <a href="javascript:void(0);" class="' . $activeClass . ' getrelateddata ' . $clss_first . '" data-related="' . $cat->id . '" data-slugurl="' . URL::to('/category?name=') . $cat->category_slug . '">' . $cat->categoryTitle . '</a>
                        </li>';
                            if ($counter_all == count($categoreis)) {
                                //echo '</div>';
                            }
                            $counter_all++;
                        }
                        echo '</ul>';
                    }
                    ?>
                    <!--
                    <a href="javascript:;" class="show-more-results">
                        <span class="shw-mrtxt">Show More</span>
                        <span class="shw-lstxt">Show Less</span>                        
                    </a>
                    -->
                </div>
                <!-- reward-conainer-filter -->
            </div>

            <div class="grid-container">
                <div class="row filtered-boxed ajax-response"></div>
               
                <div class="uploadImages" style="display: block;">
                    <span></span>
                </div>
            </div>
            <!-- grid-container -->
        </div>
    </section>
    <style>.showmore{
            display:none;
        }
    </style>
    <script>


        jQuery(document).ready(function () {
            jQuery('.multiple-items-mbt').slick({
                infinite: true,
                slidesToShow: 4,
                variableWidth: true,
                slidesToScroll: 1
            });

            jQuery('.firstcat').click();
            jQuery('.uploadImages').hide();
        });
        jQuery(document).on('click', '.show-more-results', function () {

            jQuery('li.showmore').slideToggle();
        })
        jQuery(document).on('click', '.getrelateddata', function () {
            catid = jQuery(this).attr('data-related');
            slugurl = jQuery(this).attr('data-slugurl');
            jQuery('.viewalllink').attr('href', slugurl);
            if (catid != '') {
                jQuery('.getrelateddata').removeClass('active');
                jQuery(this).addClass('active');
                jQuery('.uploadImages').show();
                jQuery.ajax({
                    url: '/getrelated',
                    method: 'POST',
                    Type: 'POST',
                    data: 'catid=' + catid,
                    success: function (data) {
                        jQuery('.ajax-response').html(data);
                        jQuery('.uploadImages').hide();
                        makeNumberFormate();
                    }
                });
            }
        });
    </script>

<?php } ?>