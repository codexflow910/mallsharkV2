@extends('layouts.new-main')
@section('content')
@section('title', 'Feature Products')
@if (!empty($featureProducts))
    <section class="n-prsecin feature-section">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h3>Feature Products</h3>
                </div>
            </div>
        </div>
        <div class="n-prdctrow">

            <div class="container">
                <div class="row">
                    <div class="col-md-3 ipad-full-width">

                        <div class="pos-rel">
                            <div class="sidebar-filter pos-absolute mt-minu50 mb-20">
                               
                                <div class="sidebar-content mobile-mt-0  hide-mobile767">

                                    <h2 class="bros-heading gray-text fnt18" >
                                        <span class="broser-cate-cion"><i class="material-icons">list</i></span>
                                        Browse Categories
                                    </h2>
                                    <a href="{{URL::to('/')}}" style="display:none;"
                                        class="showallcats shw-all-cts">
                                        <!-- <i class="fa fa-chevron-left" aria-hidden="true"></i> -->

                                        <h2 class=" gray-text fnt18"> <span class="broser-cate-cion"><i
                                                    class="material-icons">list</i></span> all categories </h2>
                                    </a>
                                    @if(!empty($featureProducts))
                                        <ul class="nav flex-column flex-nowrap">
                                            @foreach ($featureProducts as $fps)
                                            
                                                @foreach ($fps->parentCategories as $pcategories)
                                                    <li class="nav-item  nav-item nav-list-item">
                                                    
                                                        <div class=" prent-nav-lnk">
                                                            <a style="display:inline-block"
                                                            class="nav-link ajax-link refreshcate no-childs"
                                                            href="{{URL::to('/feature-products?category='.$pcategories->category_slug)}}">{{$pcategories->categoryTitle}}</a>
                                                            {{-- <span class="product-items-counter">(3)</span> --}}
                                                        </div>
                                                        @if(!empty($fps->childCategories))
                                                        <ul>
                                                            @foreach ($fps->childCategories as $ccategories)
                                                                <li>
                                                                    <div class=" prent-nav-lnk">
                                                                        <a style="display:inline-block"
                                                                        class="nav-link ajax-link refreshcate no-childs"
                                                                        href="{{URL::to('/feature-products?category='.$ccategories->category_slug)}}">{{$ccategories->categoryTitle}}</a>
                                                                        {{-- <span class="product-items-counter">(3)</span> --}}
                                                                    </div>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            @endforeach
                                        
                                        </ul>
                                    @endif
                                </div>

                                <div class="sidebar-content mt-0 mobile-mt-0 mobile-style-filter bkclr-mbt brtop-0 sndbr-box">
                                    <div class="price_filter">
                                        <div itemscope="" itemtype="https://schema.org/WebSite">
                                            <meta itemprop="url" content="https://shopzit.mindblaze.net/">
                                            <form action="{{URL::to('/')}}" method="GET"
                                                id="search-filters" itemprop="potentialAction" itemscope=""
                                                itemtype="https://schema.org/SearchAction">
                                                <meta itemprop="target"
                                                    content="https://shopzit.mindblaze.net/search?min_price">
                                                <div class="apply-filter-btn mt-5 hide-mobile767">

                                                    <div class="mt-15 d-flex justify-content-between ">
                                                        <p class="mb-zero"> <a href="{{URL::to('/feature-products')}}" id="resetfilters">Reset Filters</a></p>
                                                      
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            </div><!-- sidebar-filter -->


                        </div>
                    </div>
                    <div class="col-md-9" id="fps_container">
                        <div class="row">
                            @foreach ($featureProducts as $fps)
                                @php
                                    $image_url = getAttachmentUrlById((int) $fps->feature_image, 'medium');
                                    if (!isImageExists($image_url)) {
                                        $image_url = url(asset('assets/images/placeholder.jpg', env('HTTPS_STATUS')));
                                    }
                                    $reviews = getReviewAverage($fps->reviews);
                                    //echo 'Data: <pre>' .print_r($fps->reviews,true). '</pre>';
                                @endphp
                                <div class="col-md-3">
                                    <!-- card starts -->
                                    <div class="card">
                                        <img class="feature-lable"
                                            src="{{ URL::asset('assets/images/featured-label.png') }}" />
                                        <a href="{{ URL::to('/product/' . $fps->slug) }}"><img
                                                src="{{ $image_url }}" class="card-img-top"
                                                alt="{{ $fps->title }}" title="{{ $fps->title }}"></a>
                                        <div class="card-body">
                                            <h4 class="card-title"><a
                                                    href="{{ URL::to('/product/' . $fps->slug) }} ">{{ !empty($fps->title) ? $fps->title : '' }}</a>
                                            </h4>
                                            <p class="card-text prc d-flex justify-content-between">
                                                <span class="n-price"><span class="z-symbl">USDC
                                                        {{ !empty($fps->price) ? $fps->price : 0.0 }}</span> </span>
                                                <span>
                                                    <?php echo $reviews; ?>
                                                </span>
                                            </p>
                                            <p class="card-text small  d-flex justify-content-between">
                                                <span>&nbsp;<span class="">By: <a
                                                            href="<?php echo URL::to('/brand/' . $fps->store->store_slug); ?>">{{ $fps->store->store_name }}</a></span></span>
                                            </p>
                                        </div>
                                    </div>
                                    <!-- card ends-->
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @if ($featureProducts->perPage() <= $featureProducts->total())
                    <div class="loadmore fpsmore button ldmre-txt">
                        <button type="" class="btn btn-primary btn-simple btn-block check-fields">Load
                            More</button>
                    </div>
                    <script>
                        var pageno = "<?php echo $featureProducts->currentPage() + 1; ?>";
                        var lastPage = "<?php echo $featureProducts->lastPage(); ?>";
                        var data = {};
                        $(document).on('click', '.fpsmore', function() {
                            if (lastPage >= pageno) {
                                var url = '/feature-products?page=' + pageno;
                                $.ajax({
                                    url: url,
                                    method: 'GET',
                                    success: function(response) {
                                        if (response != '') {
                                            $('#fps_container').append(response);
                                        }
                                    }
                                });
                                pageno++;
                            } else {
                                $('.fpsmore').remove();
                            }
                        });
                    </script>
                @endif
            </div>
        </div>
    </section>
@endif
@endsection
<!-- Top Selling Product Ends -->
