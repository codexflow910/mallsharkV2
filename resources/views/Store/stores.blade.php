@extends('layouts.frontend-main')
@section('content')
@section('title', 'All Brands | Mallshark - Crypto powered Shop')
@section('description', 'At Mallshark, you’ll find all of Clothing & Shoes, Home & Living and Art & Collection brands and can buy using cryptocurrencies like bitcoin, LTH and others.')
@section('keyword', 'Shop by Brands')

<script src="{{ URL::asset('assets/js/cart.js',env('HTTPS_STATUS')) }}"></script>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons"
    rel="stylesheet">
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<style>
    .search-icon-header {
        display: inline-block;
    }

    @media only screen and (min-width: 320px) and (max-width: 767px) {
        #menu-toggle {
            display: inline-block;
        }

        header .navbar-brand {
            margin-left: 30px;
        }

    }
</style>



<!-- /#sidebar-wrapper -->


<script src="{{ URL::asset('assets/js/cart.js',env('HTTPS_STATUS')) }}"></script>
<section>
    <div class="affiliate-home container mt-60">


        <h1 style="display: none;">Buy from featured brands using crypto currencies</h1>
        <h2 class="featured-brands mb-20 mt-80">Search Results For <?php echo isset($_GET['keyword']) ? $_GET['keyword'] : ''; ?></h2>
        <div class="row brands-cards ">
            <?php
            // echo '$stores: <pre>' .print_r($stores,true). '</pre>'; die();
            if (count($stores) > 0) {
                foreach ($stores as $st) {
                    $userId = $st['user_id'];
                    $userData = getUserById($userId);
                    $userImageId = $userData->user_avatar;
                    //  dump("user", $userData);
                    $logourl = '';
                    if (isset($st['store_logo']) && $st['store_logo'] != '') {
                        $store_logo = $st['store_logo'];
                        $logourl = getAttachmentUrlById((int) $store_logo, 'small');
                    } else {
                        $logourl = getAttachmentUrlById((int) $userImageId, 'small');
                    }
                    if ($st['stripe'] == '1') {
            ?>

                        <div class="col-sm-4">
                            <div class="card">
                                <div class="brand-logo-container">
                                    <div class="brands-header text-center  d-flex h-100">
                                        <div class="justify-content-center align-self-center w100p">
                                            <a href="{{Url::to('/brand/'.$st['store_slug'])}}" class="anchor-lnkmbt">
                                                <img src="{{!empty($logourl) ? $logourl : URL::asset('assets/images/placeholder.png') }}" alt="{{$st['store_name']}}" title="{{$st['store_name']}}" class="img-fluid">
                                            </a>
                                        </div>
                                    </div>
                                    <div class="brands-boday">

                                        <div class="justify-content-between list-brands">
                                            <div class="row mt-5 mb-5 ">
                                                <div class="col-sm-6 col-6">
                                                    <p>Store Name :</p>
                                                </div>
                                                <div class="col-sm-6 col-6">
                                                    <p class="text-blue text-right">
                                                        <a href="{{Url::to('/brand/'.$st['store_slug'])}}" class="">
                                                            <b>{{$st['store_name']}}</b>
                                                        </a>
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="row mt-5 mb-5 ">
                                                <div class="col-sm-6 col-6">
                                                    <p>Total Products:</p>
                                                </div>
                                                <div class="col-sm-6 col-6">
                                                    <p class="text-blue text-right"><b>{{getStoreTotalProducts($st['store_id'])}}</b></p>
                                                </div>
                                            </div>

                                        </div>
                                    </div><!-- brands-boday -->
                                    <div class="brands-footer mt-25 mb-15">
                                        <a href="{{Url::to('/brand/'.$st['store_slug'])}}" class="btn btn-primary btn-simple">
                                            Visit Store
                                        </a>
                                    </div>

                                </div>
                            </div>
                        </div>
                <?php
                    }
                }
            } else {
                ?>
                @include('search.no-record')
            <?php
            }
            ?>






        </div><!-- brands-cards -->



    </div><!-- affiliate-home -->

</section>

<style>
    a.nav-link.active {
        color: #34a08d !important;
    }

    .adjusted-margin {
        margin-top: -30px !important;
    }

    .mtTop {
        margin-top: 80px !important;
    }
</style>
<script>
    $(document).ready(function() {
        const h1Element = $('body > section > div > h1');
        const h2Element = $('body > section > div > h2');

        const h1Display = h1Element.css('display');

        if (h1Display === "none") {
            h2Element.addClass('adjusted-margin');
        } else {
            h2Element.addClass('mtTop');
        }
    });
</script>
@endsection