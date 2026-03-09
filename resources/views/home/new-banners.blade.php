
<?php
// id 1 for slider

$filterId = 1;
$filterData = getFilterData($filterId);
if ($filterData['filter_status'] == 1) {
    ?>
    <section class="n-bannerbx">
        <div class="container">
            <div class="row">

            <!-- <div class="col-md-3 pr-0">
                    <div class=" cat-box">
                    <h6 class="grytxt">Browse Categories</h6>
                    <ul class="catlst">
                        @php
                          $categories = getSideCategories(11);   
                        @endphp
                        @if(count($categories)>0)
                        @foreach($categories as $category)
                        @php
                        
                       
                        $image_url = getAttachmentUrlById((int) $category->icon_id, 'medium');
                        if (!isImageExists($image_url)) {
                        $image_url = url(asset('assets/images/placeholder.jpg', env('HTTPS_STATUS')));
                                    }
                        
                        @endphp
                        <li>
                             <a href=" {{ URL::to('/category?name=') .$category->category_slug  }}">
                             <span class="icnctrl">
                                <img style="width:20px" src="{{ $image_url }}" />
                            </span>{{ $category->categoryTitle }}</a></li>
                        @endforeach
                        
                        @endif
                        {{-- <li> <a href="#"><span class="icnctrl"><img style="width:20px" src="{{ asset('assets/images/fashion-women.svg') }}" /></span> Women Fashion</a></li>
                        <li> <a href="#"><span class="icnctrl"><img style="width:20px" src="{{ asset('assets/images/fashion-women.svg') }}" /></span> Women Fashion</a></li>
                        <li><a href="#"><span class="icnctrl"><img style="width:20px" src="{{ asset('assets/images/fashoin-men.svg') }}" /></span> Men's Fashion</a></li>
                        <li><a href="#"><span class="icnctrl"><img style="width:20px" src="{{ asset('assets/images/mobile.svg') }}" /></span> Phone & Accessories</a></li>
                        <li><a href="#"><span class="icnctrl"><img style="width:20px" src="{{ asset('assets/images/computer.svg') }}" /></span> Computer</a></li>
                        <li><a href="#"><span class="icnctrl"><img style="width:20px" src="{{ asset('assets/images/electronics.svg') }}" /></span> Electronics</a></li>
                        <li><a href="#"><span class="icnctrl"><img style="width:20px" src="{{ asset('assets/images/jewelry.svg') }}" /></span> Jewelry</a></li>
                        <li><a href="#"><span class="icnctrl"><img style="width:20px" src="{{ asset('assets/images/home-appliance.svg') }}" /></span> Home, Pet & Appliances</a></li>
                        <li><a href="#"><span class="icnctrl"><img style="width:20px" src="{{ asset('assets/images/shoes.svg') }}" /></span> Bags & Shoes</a></li>
                        <li><a href="#"><span class="icnctrl"><img style="width:20px" src="{{ asset('assets/images/toy.svg') }}" /></span> Toys, Kids & Babies</a></li>
                        <li><a href="#"><span class="icnctrl"><img style="width:20px" src="{{ asset('assets/images/sports.svg') }}" /></span> Outdoor Fun & Sports</a></li>
                        <li><a href="#"><span class="icnctrl"><img style="width:20px" src="{{ asset('assets/images/beauty.svg') }}" /></span> Beauty, Health & Hair</a></li> --}}

                    </ul>
                   </div>
                </div> -->
                <div class="col-md-12">

                    <div id="demo" class="carousel carousel-fade" data-ride="carousel">

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
                                echo '<div class="carousel-item ' . $active_class . '" data-bgcolor="'. $main_slide->bg_color .'">
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


                 
            </div>
        </div>
    </section>
<?php } ?>
<script>
$('#demo').on('slide.bs.carousel', function (event) {
//     var currentSlide = $(event.relatedTarget);
//   let bgcolor = currentSlide.data('bgcolor');
//   console.log('Current slide ID:', bgcolor);
//   $('.n-bannerbx').css('background-color', bgcolor);
  // code to be executed after the carousel has finished sliding
});

$(document).ready(function(){
//   $("body").addClass("home");  
  
//   let bgcolor =  $('#demo .carousel-inner .active').data('bgcolor')
//   $('.n-bannerbx').css('background-color', bgcolor);
});
</script>