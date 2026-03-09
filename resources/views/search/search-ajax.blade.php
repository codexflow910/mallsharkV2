
<?php 
$url = url()->current();
if (strpos($url, 'brand') !== false) {
    $slug = request()->segment(1);
    if($slug && $slug != 'brand'){
        $slug = $slug;
     }else{
         $slug = isset($_GET['category']) ? $_GET['category'] : '';
     }
}
if (!empty($productResults) && count($productResults['records']) > 0) { ?>
    @include('search.top-filters')
    <div class="col-md-99">

        <div class="results-box-mbt row">

           @include('search.search-ajax-loadmore')
        </div><!-- results-box-mbt -->

        <div class="row">
            <div class="custom-pagination col-sm-12 mt-15">
                <?php

                $q = [];
                if (isset($_GET['status'])) {
                    $q['status'] = $_GET['status'];
                }
                if (isset($_GET['order'])) {
                    $q['order'] = $_GET['order'];
                }
                // echo 'no';
                // print_rr($listingresults);
                //echo $listingresults->appends($q)->links('pagination.defaultpagination');
                //echo paginate($item_per_page, $page_number, $get_total_rows[0], $total_pages, $page_url);

                // if (isset($productResults['total']) && isset($productResults['records'])) {
                //     // echo 'yes';
                //     $per_page = 9;
                //     $total_records = (int)$productResults['total'];
                //     $max = $total_records / $per_page;
                    
                //     $page_number = isset($_GET['page']) ? $_GET['page'] : 1;
                //     unset($_GET['page']);
                //     $pageurl = Request::fullUrl();
                //     if (!$_GET) {
                //         $pageurl = $pageurl . '?';
                //     }
                //     if ($productResults['total'] > $per_page) {
                //       //  echo paginate(1, $page_number, 10, $max, $pageurl);
                //     }
                // }
                ?>

            </div>
        </div>
        
    </div>
<?php } else { ?>
    <div class="col-sm-99">
        @include('search.no-record')
        <!-- No record found -->
    </div>
<?php } ?>
<script>
   
        $(document).ready(function () {
         
            var loading = false; 
            let ajax_pagination_counter = 1;
          
            // console.log(ajax);
            $(window).off('scroll').on('scroll', function () {
            var ajax = @json($ajax);
               
            if(ajax){
                const scrollPosition = $(window).scrollTop() + $(window).height();
                const triggerPoint = $(document).height() * 0.7; // 70% of total page height

                // Check if we've reached 70% of the page and aren't currently loading
                if (!loading && scrollPosition >= triggerPoint) {
                    
                    loading = true; // Prevent multiple requests

                    if (ajax_pagination_counter < maxdata) {
                        ajax_pagination_counter++;

                        let url = window.location.href.includes('?')
                            ? `${window.location.href}&page=${ajax_pagination_counter}`
                            : `${window.location.href}?page=${ajax_pagination_counter}`;
                        url += '&loadmore=true';

                        $.ajax({
                            url: url,
                            method: 'GET',
                            success: function (res) {
                                if (res !== '') {
                                    $('.ajax-res .col-md-99 .results-box-mbt').append(res);
                                    loading = false; // Allow further requests
                                } else {
                                    loading = false;
                                }
                            },
                            error: function () {
                                loading = false; // Reset on error
                            }
                        });
                    }
              
                }
            }
            });
       
        });
</script>