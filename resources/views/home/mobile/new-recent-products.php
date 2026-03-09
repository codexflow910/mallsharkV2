<div class="reward-campaigns-container  bb-1px award-campaigns-rec recent-products-compaign">
    <div class="container" style="position:relative;">
        <div class="row">
            <div class="col-sm-12">
                <h3 class="homePage-heading">Recent Products</h3>
            </div>
            <div class="col-sm-12">
                <div class="reward-conainer-filter slickSliderForMobile">
                    <?php
                    $categoreis = getAllParentCategories(10);
                    $counter_all = 0;
                    if (count($categoreis) > 0) {
                        echo '<ul>';
                        foreach ($categoreis as $cat) {
                            $clss_first = '';
                            $activeClass = "";
                            if ($counter_all == 0) {
                                $clss_first = 'firstcat';
                                $activeClass = 'active';
                            }
                            if ($counter_all == 7) {
                                echo '<div class="hide-list-container" style="display:none">';
                            }
                            echo '<li class="' . $clss_first . '">
                            <a href="javascript:void(0);" class="' . $activeClass . ' getrelateddata ' . $clss_first . '" data-related="' . $cat->id . '" data-slugurl="'.URL::to('/category?name=').$cat->category_slug.'">' . $cat->categoryTitle . '</a>
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

            </div>
            <div class="view-al text-right">
                <a href="{{URL::to('/q')}}" class="viewalllink">View All</a>
            </div>
            <div class="uploadImages" style="display: block;">
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

        jQuery('.firstcat').click();
        jQuery('.uploadImages').hide();
    });
    jQuery(document).on('click', '.show-more-results', function () {

        jQuery('li.showmore').slideToggle();
    })
    jQuery(document).on('click', '.getrelateddata', function () {
        catid = jQuery(this).attr('data-related');
        slugurl = jQuery(this).attr('data-slugurl');
        jQuery('.viewalllink').attr('href',slugurl);
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
                }
            });
        }
    });

</script>