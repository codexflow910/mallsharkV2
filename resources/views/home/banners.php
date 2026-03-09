<?php
// id 1 for slider
$filterId = 1;
$filterData = getFilterData($filterId);
if ($filterData['filter_status'] == 1) {
    ?>

    <section class="n-bannerbx">
    @if(Session::has('createOrder'))
<p class="alert alert-info">{{ Session::get('createOrder') }}</p>
@endif
        <div class="container">
            <div class="row">
                <div class="col-md-8">

                    <div id="demo" class="carousel slide" data-ride="carousel">

                        <!-- Indicators -->
                        <ul class="carousel-indicators">
                            <?php
                            for ($i = 1; $i <= count($slides_main); $i++) {

                                if ($i == 1) {
                                    $active_class = "active";
                                } else {
                                    $active_class = "";
                                }

                                echo ' <li data-target="#demo" data-slide-to="0" class="' . $active_class . '"></li>';
                            }
                            ?>


                        </ul>

                        <!-- The slideshow -->


                        <?php
                        if (count($slides_main) > 0) {
                            echo '<div class="carousel-inner">';
                            $i = 1;
                            foreach ($slides_main as $main_slide) {
                                $slide_id = $main_slide->attachment_id;
                                if ($i == 1) {
                                    $active_class = "active";
                                } else {
                                    $active_class = "";
                                }
                                $slide_url = $image_url = getAttachmentUrlById($slide_id, 'media');
                                $slide_link = $main_slide->link;
                                if ($slide_link == '') {
                                    $slide_link = 'javascript:void(0)';
                                }
                                echo '<div class="carousel-item ' . $active_class . '">
                        <a href="' . $slide_link . '"><img src="' . $slide_url . '" alt="sales discounted products" title="sales discounted products"></a>
                    </div>';
                                $i++;
                            }
                            echo '</div>';
                        }
                        ?>


                    </div>

                    <!-- Left and right controls -->
                    <a class="carousel-control-prev" href="#demo" data-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                    </a>
                    <a class="carousel-control-next" href="#demo" data-slide="next">
                        <span class="carousel-control-next-icon"></span>
                    </a>

                </div> 


                <div class="col-md-4">
                    <?php
                    if (count($slide_small) > 0) {


                        foreach ($slide_small as $main_slide) {
                            $slide_id = $main_slide->attachment_id;
                            if ($i == 1) {
                                $active_class = "active";
                            } else {
                                $active_class = "";
                            }
                            $slide_url = $image_url = getAttachmentUrlById($slide_id, 'media');
                            $slide_link = $main_slide->link;
                            if ($slide_link == '') {
                                $slide_link = 'javascript:void(0)';
                            }
                            echo '<a href="' . $slide_link . '"><img src="' . $slide_url . '" alt="Branded Shose and Cloths" title="Branded Shose and Cloths"></a>';
                            break;
                        }
                    }
                    ?>

                </div>
            </div>
        </div>
    </section>
<?php } ?>
<script>
$(document).ready(function(){
  $("body").addClass("home");  
});
</script>