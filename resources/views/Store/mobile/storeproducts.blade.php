@extends('layouts.mobile-main')
@section('content')
@section('title', 'Store Products')

<main id="main">
    @include('Store.mobile.sidebar')
    <!-- Dashboard Starts -->
    <!-- Dashboard Ends -->
    <div class="dshbrdin store-product-mobile">




        <div class="container">
            <div class="row">
                <div class="col-md-12"> 
                    <div class="dashprdlst">
                        <!-- Product Sorting Starts -->
                        <div class="row srtbx">
                            <div class="col-md-12"><h3 class="">Products</h3></div>    
                        </div>
                        @if(Session::has('SuccessMessage'))
                        <div class="alert alert-success alert-success-mbt" id="success_message" role="alert">
                            {{ Session::get('SuccessMessage') }}
                            <script>
                                setTimeout(function () {
                                    jQuery("#success_message").fadeOut('slow')
                                }, 3000);
                            </script>
                        </div>
                        @endif
                        @if(Session::has('errorMessage'))
                        <div class="alert alert-danger" role="alert">{{ Session::get('errorMessage') }}</div>
                        @endif
                        <!-- Product Sorting Ends--> 
                        <!-- Tab sections start -->
                        <!-- <ul class=" prd nav nav-pills nav-fill">
                            <li class="nav-item">
                                <a class="nav-link <?php
                                // if (!isset($_GET['campaign']) && !isset($_GET['onsale'])) {
                                //     echo 'active';
                                // }
                                ?>" href="{{URL::to('/store-products')}}">Standard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php //echo isset($_GET['campaign']) ? 'active' : ''; ?>" href="{{URL::to('/store-products')}}?campaign=1">On Campaign</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php //echo isset($_GET['onsale']) ? 'active' : ''; ?>" href="{{URL::to('/store-products')}}?onsale=1">On Flashsale</a>
                            </li>

                        </ul> -->

                        <br>   
                        <!-- Tab sections ends -->
                        <form action="{{url('store-products')}}" method="get" id="vendor-filters">
                            <div class="row srtbx">

                                <div class="col-6 text-right">
                                    <div class="form-inline small">
                                        <label for="text">Products By:</label>
                                        <select class="form-control search-field" id="category" name="category">
                                            <option>All</option>
                                            <?php
                                            $storeData = getStoreDataByUserId(getCurrentUserId());
                                            if (!empty($storeData)) {
                                                $storeId = $storeData->store_id;
                                                $parent_Categories = getCategoriesBystoreId($storeId);

                                                foreach ($parent_Categories as $key => $val) {
                                                    $selected = "";
                                                    if (isset($_GET['category']) && $_GET['category'] == $key) {
                                                        $selected = 'selected';
                                                    }
                                                    echo '<option value="' . $key . '" ' . $selected . '>' . $val . '</option>';
                                                }
                                            }
                                            ?>
                                        </select> 
                                    </div>   
                                </div>
                                <div class="col-6 text-right">
                                    <div class="  form-inline small ">
                                        <label for="text">Sort By:</label>
                                        <select class="form-control search-field" id="order" name="order">
                                            <option>Date</option>
                                            <option <?php
                                            if (isset($_GET['order']) && $_GET['order'] == 'ASC') {
                                                echo 'selected';
                                            }
                                            ?> value="ASC">Old to new</option>
                                            <option <?php
                                            if (isset($_GET['order']) && $_GET['order'] == 'DESC') {
                                                echo 'selected';
                                            }
                                            ?>  value="DESC">New to old</option>
                                        </select>   
                                    </div>   
                                </div>

                            </div>
                        </form>
                        <!-- Product List --> 
                        <div class="row">
                            <?php foreach ($results as $res) { ?>
                                <div class="dshprdbx col-sm-6">
                                    <div class="card">     
                                        <div class="col-md-12-full">  
                                            <div class="parent-box">
                                                <img class="img-fluid" src="<?php echo getAttachmentUrlById((int) $res->feature_Image, $size = 'medium') ?>" alt="{{$res->title}}" title="{{$res->title}}">
                                                <?php if ($res->is_feature == "1") { ?>
                                                    <div class="featured-pro-mbt ">
                                                        <span>Featured</span>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>     
                                        <div class="col-12">
                                            <div class="description-mobile-pro">
                                                <a href="<?php echo URL::to('product/' . $res->slug); ?>"><h4 class="card-title">{{$res->title}}</h4></a>
                                                <?php
                                                $timestamp = strtotime($res->created_at);
                                                $date = date('d-M-Y', $timestamp);
                                                ?>
                                                <p class="small" style="margin-bottom:5px">{{$date}}</p>
                                                <p class="card-text" style="margin-bottom:5px">{{strip_tags($res->short_description)}}</p>
                                                <?php
                                                $totalAdded = $res->current_stock + $res->sales;
                                                ?>
                                                <p style="margin-bottom:5px"><span class="zit-price">Price: USDC :{{number_format($res->price,2)}}</span>    &nbsp;&nbsp;|&nbsp;&nbsp;    In Stock: <?php echo isset($res->current_stock) ? $res->current_stock : 0; ?> &nbsp;&nbsp;|&nbsp;&nbsp; Sold: <?php echo isset($res->sale) ? $res->sale : 0; ?>    Total Added: {{$totalAdded}}  </p>

                                                <p class="mt-15 mb-15" style="margin-bottom:5px" text-right inline-block-ele> <a href="<?php echo URL::to('/edit-product') . "/" . $res->product_id; ?>">Edit Item</a>   
                                                    <?php if ($res->is_feature == "0") { ?>

                                                        <a href="#">Make feature</a>  <?php } ?> 
                                                        
                                                        <a href="#"  data-toggle="modal" data-target="#variable-popup">Start Campaign</a>  
                                                        <span class="sprtr-lne"> |<span> <a href="<?php echo URL::to('/editproductfaqs') . '/' . $res->product_id; ?>">All Faqs</a> <span class="sprtr-lne"> |

                                                    <a class="alert-message-mbt" href="<?php echo URL::to('/store-products-delete') . "/" . $res->product_id; ?>">Delete</a></p>
                                            </div> 
                                        </div>
                                    </div> 
                                </div> 
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="variable-popup" class="modal fade vaiable-popup-edit" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Product Variants</h4>  
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th scope="col">Color</th>
                                    <th scope="col">Diameter</th>
                                    <th scope="col">Finish</th>
                                    <th scope="col">Price</th>
                                    <th scope="col">SKU</th>
                                    <th scope="col">Visible</th>    

                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Blue</td>
                                    <td>12 inch</td>
                                    <td>Cotton</td>
                                    <td>&#36;5</td>
                                    <td>4644374</td>
                                    <td>
                                        <label class="form-check-label container-check">
                                            <input class="form-check-input" type="checkbox" name="chkrememberme">
                                            <span class="checkmark"></span>
                                        </label>
                                    </td>    
                                </tr>
                                <tr>
                                    <td>Blue</td>
                                    <td>12 inch</td>
                                    <td>Cotton</td>
                                    <td>&#36;5</td>
                                    <td>4644374</td>
                                    <td>
                                        <label class="form-check-label container-check">
                                            <input class="form-check-input" type="checkbox" name="chkrememberme">
                                            <span class="checkmark"></span>
                                        </label>
                                    </td>    
                                </tr>
                                <tr>
                                    <td>Blue</td>
                                    <td>12 inch</td>
                                    <td>Cotton</td>
                                    <td>&#36;5</td>
                                    <td>4644374</td>
                                    <td>
                                        <label class="form-check-label container-check">
                                            <input class="form-check-input" type="checkbox" name="chkrememberme">
                                            <span class="checkmark"></span>
                                        </label>
                                    </td>    
                                </tr> 
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>
    </div>        

</main>
<script>
    jQuery(document).ready(function () {
        jQuery(document).on('change', '#vendor-filters .search-field', function () {
            jQuery('#vendor-filters').trigger('submit');
        })
    });
</script>
<script>
    $(document).ready(function () {
        $(".alert-message-mbt").on("click", function () {
            var result = confirm(" Are you sure want to remove.?");
            if (!result) {
                return false;
            }
        });
        $(".alertduplicate").on("click", function () {
            var result = confirm(" Are you sure want to duplicate this product.?");
            if (!result) {
                return false;
            }
        });
    });

</script>
@endsection