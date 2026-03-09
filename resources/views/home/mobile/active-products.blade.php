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

            </div>
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