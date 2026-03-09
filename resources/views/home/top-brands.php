<?php
// id 4 for top brand 
$filterId = 4;
$filterData = getFilterData($filterId);
if ($filterData['filter_status'] == 1) {
    ?>
    <div class="reward-campaigns-container pb-60 bb-1px award-campaigns-brands top-brands-compaign hidden">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="homePage-heading">Top Brands</h3>
                </div>
                <div class="col-sm-12">
                    <div class="reward-conainer-filter mobileVersio2">
                        <ul>
                            <li>
                                <a href="#" class="active" data-related="topbrands-main" >Art &amp; Crafts</a>
                            </li>
                            <li>
                                <a href="#" data-related="topbrands-main" class="">Apparel &amp; Accessories </a>
                            </li>
                            <li>
                                <a href="#" data-related="topbrands-main" class=""> Cosmetic &amp; Personal Care</a>
                            </li>
                            <li>
                                <a href="#" data-related="topbrands-main" class="">Foodstuffs &amp; Beverage</a>
                            </li>
                            <li>
                                <a href="#" data-related="topbrands-main" class=""> Health &amp; Wellness </a>
                            </li>
                            <li>
                                <a href="#" data-related="topbrands-main" > Hand Made &amp; Artisan Goods </a>
                            </li>
                            <div class="hide-list-container" style="display:none">
                                <li class="hide-listt">
                                    <a href="#" data-related="topbrands-main"> Drama </a>
                                </li>
                                <li class="hide-listt">
                                    <a href="#" data-related="topbrands-main"> Games </a>
                                </li>
                                <li class="hide-listt">
                                    <a href="#" data-related="topbrands-main"> Food </a>
                                </li>
                            </div>
                        </ul>
                        <a href="javascript:;" class="show-more-results">Show More</a>
                    </div>
                    <!-- reward-conainer-filter -->
                </div>
            </div>
            <div class="grid-container">
                <div class="row filtered-boxed active" id="topbrands-main" style="display: flex;">
                    <div class="col-sm-3 text-center">
                        <p class="featured-block">Featured</p>
                        <div class="section_potd-block">
                            <span class="anml"> <span class="pwr">100</span> <span class="prg"><span class="up">▲</span> 100%</span><a href="javascript:;">
                                    <img class="img-fluid" src="<?php echo asset('assets/images/icon1.png'); ?>" alt="Top Brand Animal Icon" title="Top Brand Animal Icon">
                                </a>
                            </span>

                        </div>
                    </div>
                    <div class="col-sm-9">

                        <!-- section_Top Brand Animal Icon-collection-tabbed-block -->
                        <div class="results-ui-list">
                            <div id="most-recents" class="items-start" style="display: block; margin-top:35px"> 
                                <span class="anml anmls"> <span class="pwr ">100</span><span class="prg"><span class="up">&#9650;</span> 100%</span><a href="javascript:;">
                                        <img class="img-fluid" src="<?php echo asset('assets/images/icon1.png'); ?>" alt="Top Brand Animal Icon" title="Top Brand Animal Icon">
                                    </a>
                                </span>
                                <span class="anml anmls"> <span class="pwr pwrsml">79</span> <span class="prg"><span class="up">&#9650;</span> 100%</span><a href="javascript:;">
                                        <img class="img-fluid" src="<?php echo asset('assets/images/icon2.png'); ?>" alt="Top Brand Animal Icon" title="Top Brand Animal Icon">
                                    </a>
                                </span>
                                <span class="anml anmls"> <span class="pwr pwrsml">87</span><span class="prg"><span class="up">&#9650;</span> 100%</span><a href="javascript:;">
                                        <img class="img-fluid" src="<?php echo asset('assets/images/icon3.png'); ?>" alt="Top Brand Animal Icon" title="Top Brand Animal Icon">
                                    </a>
                                </span>
                                <span class="anml anmls"> <span class="pwr pwrsml">78</span> <span class="prg"><span class="up">&#9650;</span> 100%</span><a href="javascript:;">
                                        <img class="img-fluid" src="<?php echo asset('assets/images/icon4.png'); ?>" alt="Top Brand Animal Icon" title="Top Brand Animal Icon">
                                    </a>
                                </span>
                                <span class="anml anmls"> <span class="pwr pwrsml">76</span><span class="prg"><span class="up">&#9650;</span> 100%</span><a href="javascript:;">
                                        <img class="img-fluid" src="<?php echo asset('assets/images/icon5.png'); ?>" alt="Top Brand Animal Icon" title="Top Brand Animal Icon">
                                    </a>
                                </span>
                                <span class="anml anmls"> <span class="pwr">100</span> <span class="prg"><span class="up">&#9650;</span> 80%</span><a href="javascript:;">
                                        <img class="img-fluid" src="<?php echo asset('assets/images/icon6.png'); ?>" alt="Top Brand Animal Icon" title="Top Brand Animal Icon">
                                    </a>
                                </span>
                                <span class="anml anmls"> <span class="pwr">100</span><span class="prg"><span class="dwn">&#9660;</span> 90%</span><a href="javascript:;">
                                        <img class="img-fluid" src="<?php echo asset('assets/images/icon7.png'); ?>" alt="Top Brand Animal Icon" title="Top Brand Animal Icon">
                                    </a>
                                </span>
                                <span class="anml anmls"> <span class="pwr">100</span> <span class="prg"><span class="dwn">&#9660;</span> 70%</span><a href="javascript:;">
                                        <img class="img-fluid" src="<?php echo asset('assets/images/icon8.png'); ?>" alt="Top Brand Animal Icon" title="Top Brand Animal Icon">
                                    </a>
                                </span>
                                <span class="anml anmls"> <span class="pwr">100</span><span class="prg"><span class="dwn">&#9660;</span> 60%</span><a href="javascript:;">
                                        <img class="img-fluid" src="<?php echo asset('assets/images/icon6.png'); ?>" alt="Top Brand Animal Icon" title="Top Brand Animal Icon">
                                    </a>
                                </span>
                                <span class="anml anmls"> <span class="pwr">100</span> <span class="prg"><span class="dwn">&#9660;</span> 75%</span><a href="javascript:;">
                                        <img class="img-fluid" src="<?php echo asset('assets/images/icon2.png'); ?>" alt="Top Brand Animal Icon" title="Top Brand Animal Icon">
                                    </a>
                                </span>
                                <span class="anml anmls"> <span class="pwr">100</span><span class="prg"><span class="up">&#9650;</span> 89%</span><a href="javascript:;">
                                        <img class="img-fluid" src="<?php echo asset('assets/images/icon8.png'); ?>" alt="Top Brand Animal Icon" title="Top Brand Animal Icon">
                                    </a>
                                </span>
                                <span class="anml anmls"> <span class="pwr">100</span> <span class="prg"><span class="up">&#9650;</span> 100%</span><a href="javascript:;">
                                        <img class="img-fluid" src="<?php echo asset('assets/images/icon5.png'); ?>" alt="Top Brand Animal Icon" title="Top Brand Animal Icon">
                                    </a>
                                </span>
                                <span class="anml anmls"> <span class="pwr">100</span><span class="prg"><span class="up">&#9650;</span> 100%</span><a href="javascript:;">
                                        <img class="img-fluid" src="<?php echo asset('assets/images/icon7.png'); ?>" alt="Top Brand Animal Icon" title="Top Brand Animal Icon">
                                    </a>
                                </span>
                                <span class="anml anmls"> <span class="pwr">100</span> <span class="prg"><span class="up">&#9650;</span> 100%</span><a href="javascript:;">
                                        <img class="img-fluid" src="<?php echo asset('assets/images/icon1.png'); ?>" alt="Top Brand Animal Icon" title="Top Brand Animal Icon">
                                    </a>
                                </span>
                            </div> 
                        </div>
                    </div>
                </div>
            </div>
            <!-- grid-container -->
        </div>
    </div>
    <script>
        $(".award-campaigns-brands div.filtered-boxed").each(function () {
            $(this).hide();
            if ($(this).attr('id') == 'topbrands-main') {
                $(this).show();
            }
        });
        $('.award-campaigns-brands .reward-conainer-filter ul li a').on("click", function (e) {
            e.preventDefault();
            var id = $(this).attr('data-related');
            $(".award-campaigns-brands div.filtered-boxed").each(function () {
                $(this).hide();
                if ($(this).attr('id') == id) {
                    $(this).show();
                }
            });
        });
        $('.award-campaigns-brands .reward-conainer-filter ul li a').on('click', function (e) {
            e.preventDefault();
            $('.reward-conainer-filter ul li a.active').removeClass('active');
            $(this).addClass('active');
        });
        $('.award-campaigns-brands .section_project-collection-tabbed-block ul li a').on('click', function (e) {
            e.preventDefault();
            $('.section_project-collection-tabbed-block ul li a.active').removeClass('active');
            $(this).addClass('active');
        });
        $(".award-campaigns-brands div.items-start").each(function () {
            $(this).hide();
            if ($(this).attr('id') == 'most-recents') {
                $(this).show();
            }
        });
        $('.award-campaigns-brands .section_project-collection-tabbed-block ul li a').on("click", function (e) {
            e.preventDefault();
            var id = $(this).attr('data-related');
            $(".award-campaigns-brands div.items-start").each(function () {
                $(this).hide();
                if ($(this).attr('id') == id) {
                    $(this).show();
                }
            });
        });
    </script>
<?php } ?>