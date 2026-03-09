@extends('layouts.new-main')
@section('content')
@section('title', 'Flash Sales Management')
<section class="admin-dashboard dshbrdin buyer mt-0 user-management manage-categories-section">
    @include('admin.adminsidebar')
    <div class="dshbrdinadmin list-management">

        <div class="container">
            <div class="row">
                <div class="col-sm-12 mt-40">
                    <h3> Featured Flash Sales Management</h3>
                    <form action="{{URL::to('/admin/fsale-products')}}" method="get" id="product_search_form"> 
                        <div class="bd-highlight">
                            <div class="form-inline-large form-control-optn">
                                <div class="inline-block-ele pos-rel">
                                    <input type="search" class="form-control" id="search_products" name="search_products" value="<?php echo isset($_GET['search_products']) ? $_GET['search_products'] : ''; ?>" placeholder="Search Products" />
                                    <!--                                                    <button type="submit" class="btn">
                                                                                            <i class="fa fa-search" aria-hidden="true"> </i>
                                                                                        </button>     -->
                                </div>

                                <div class="btn-submit-optn inline-block-ele pos-rel">
                                    <button type="submit" class=" btn-primary"> Search
                                    </button>                                                    
                                </div>

                            </div>
                        </div>
                    </form>
                </div>
                @if (Session::has('message_success'))
                <div class="col-sm-12">

                    <div class="alert alert-success">
                        <ul>
                            <li>{!! Session::get('message_success') !!}</li>
                        </ul>
                    </div>
                </div>
                @endif
                <?php
                // print_rr($flashSaleProduct);
                if (count($flashSaleProduct) > 0) {
                    foreach ($flashSaleProduct as $rp) {
                        $timestamp = strtotime($rp['created_at']);
                        $pid = $rp['product_id'];
                        $date = date('d-M-Y', $timestamp);
                        $user_revenue = $rp['user_revenue'];
                        if ($rp['product_type'] == 'variant') {
                            $variableData = getvariableProduct($rp['product_id']);
                        }
                        $proudct_type = $rp['product_type'];
                        $sales = $rp['sales'];
                        $home_flash_sale = $rp['home_flash_sale'];
                        ?>
                        <div class="dshprdbx border">
                            <div class="row">     
                                <div class="col-sm-6 lp-bar">
                                    <div class="row">
                                        <div class="col-md-3"> 
                                            <div class="img-div-mbt">
                                                <img class="img-responsive" src="<?php echo getAttachmentUrlById($rp['feature_image']); ?>" alt="{{ $rp['title'] }}" title="{{ $rp['title'] }}">
                                            </div>
                                        </div>   

                                        <div class=" col-md-9">
                                            <div class="body-content-mbt">
                                                <a href="<?php echo URL::to('/product'); ?>/<?php echo $rp['slug']; ?>"><h4 class="card-title"><?php echo $rp['title']; ?></h4></a>
                                                <p class="small lgt-gry" style="margin-bottom:5px"><?php echo $date; ?></p>
                                                <p class="card-text" style="margin-bottom:5px;display:none;"></p>

                                                <p class="inline-block-ele" style="margin-bottom:5px">
                                                    <span class="bold-element zit-price">Price: USDC <?php echo$rp['price']; ?></span> 
                                                </p></div> 
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 lrt-bar  d-flex justify-content-between ">
                                    <div class="rw-tp wt-left-tab align-self-center">
                                        <div class="co-rvnu">
                                            <div class="lft-prf d-flex justify-content-between">
                                                <span>Collectable Revenue:</span>
                                                <span><?php echo $user_revenue; ?></span>
                                            </div>
                                            <div class="lft-prf d-flex justify-content-between">
                                                <span>Product Type:</span>
                                                <span>
                                                  <?php echo $proudct_type; ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div> 
                                    <div class="rw-tp wt-rgt-tab align-self-center">
                                        <div class="co-rvnu ">
                                            <div class="rght-prf d-flex justify-content-between">
                                                <span>Current Stock:</span>
                                                <?php if ($rp['product_type'] == 'variant') {
                                                    ?>
                                                    <span><?php echo isset($variableData['current_stock']) ? $variableData['current_stock'] : 0; ?></span>
                                                <?php } else { ?>
                                                    <span><?php echo isset($rp['current_stock']) ? $rp['current_stock'] : 0; ?></span>
                                                <?php } ?>
                                            </div> 
                                            <div class="rght-prf d-flex justify-content-between">
                                                <span>All-time Sales:</span>
                                                <span><?php echo $sales; ?></span>

                                            </div>                                                     
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row pr-edit-strip">
                                <div class="col-sm-12">
                                    <div class="d-flex justify-content-between w100p">
                                        <div class="edit-delete-box align-self-center">
                                            <p> 
                                                <?php if ($home_flash_sale == 1) { ?>
                                                    <a class="product-deactivev" href="javascript:void(0);">Featured</a>  
                                                    <?php
                                                } else {
                                                    ?>
                                                    <a class="product-deactivev" href="/admin/flash-product/<?php echo $pid; ?>">Set As Featured</a>  
                                                <?php } ?>
                                            </p>                                                    
                                        </div>
                                    </div>   
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <div class="row">
                    <div class="custom-pagination col-sm-12 mt-15">
                        <?php
                        $q = [];
                        if (isset($_GET['search_products'])) {
                            $q['search_products'] = $_GET['search_products'];
                        }
                        if (isset($_GET['status'])) {
                            $q['status'] = $_GET['status'];
                        }
                        //print_rr($q);
                        echo $flashSaleProduct->appends($q)->links('pagination.defaultpagination');
                        ?>
                    </div>
                </div>
            </div>
        <?php } else { ?>
            <div class="container">
                <div class="row">
                    <div class="alert alert-danger mt-50" role="alert">Sorry there is no flash sales running at the moment.</div>
                <?php } ?>

            </div>
            </section>
            <script>
                $(".product-deactivev").on("click", function () {
                    var result = confirm(" Are you sure want to change flash sale?");
                    if (!result) {
                        return false;
                    }
                });

            </script>
            @endsection


