@extends('layouts.new-main')
@section('content')
@section('title', 'List Management')


<section class="admin-dashboard dshbrdin buyer mt-0">
    @include('admin.adminsidebar')
    <div class="dshbrdinadmin list-management">

        <div class="container">
            <div class="row">
                <div class="col-sm-12 mt-40">
                    <h3>List Management</h3>
                </div>
                @if(Session::has('SuccessMessage'))
                <div class="alert alert-success alert-success-mbt" id="success_message" role="alert">
                    {{ Session::get('SuccessMessage') }}
                    <script>
                        setTimeout(function () {
                            jQuery("#success_message").fadeOut('slow');
                        }, 3000);
                    </script>
                </div>
                @endif
                @if(Session::has('ErrorMessage'))
                <div class="alert alert-danger alert-success-mbt" id="error_message" role="alert">
                    {{ Session::get('ErrorMessage') }}
                    <script>
                        setTimeout(function () {
                            jQuery("#error_message").fadeOut('slow');
                        }, 3000);
                    </script>
                </div>
                @endif


                <div class="col-md-9 ">
                    <form action="{{URL::to('admin/list-management')}}" method="get" id="vendor-filters">


                        <div class="form-inline small">
                            <input type="text" name="keyword" class="form-control autocomplete-field2"
                                   placeholder="Search" value="<?php
                                   if (isset($_GET['keyword'])) {
                                       echo $_GET['keyword'];
                                   }
                                   ?>">
                            &nbsp;&nbsp;&nbsp;
                            <select class="form-control search-fieldd mmb-15" id=" " name="status">
                                <option value="">Please select status</option>
                                <option <?php
                                if (isset($_GET['status']) && $_GET['status'] == 'active') {
                                    echo 'selected';
                                }
                                ?> value="active">Active
                                </option>
                                <option <?php
                                if (isset($_GET['status']) && $_GET['status'] == 'inactive') {
                                    echo 'selected';
                                }
                                ?>  value="inactive">Deactive
                                </option>
                                <option <?php
                                if (isset($_GET['status']) && $_GET['status'] == 'deleted') {
                                    echo 'selected';
                                }
                                ?>  value="deleted">Deleted
                                </option>
                                <option <?php
                                if (isset($_GET['status']) && $_GET['status'] == 'pending') {
                                    echo 'selected';
                                }
                                ?>  value="pending">Pending
                                </option>
                            </select>
                            &nbsp;&nbsp;&nbsp;


                            <input type="hidden" name="sort_by" value="created_at">
                            <select class="form-control search-fieldd" id="sorting_dropdown" name="sort_order">
                                <option value="">Sort By</option>
                                <option <?php
                                if (isset($_GET["sort_order"]) && $_GET["sort_order"] == "DESC") {
                                    echo 'selected';
                                }
                                ?> value="DESC">Old to New
                                </option>
                                <option <?php
                                if (isset($_GET["sort_order"]) && $_GET["sort_order"] == "ASC") {
                                    echo 'selected';
                                }
                                ?> value="ASC">New to Old
                                </option>
                            </select>
                            &nbsp;&nbsp;&nbsp;
                            <button type="submit" class="btn btn-primary addtransporterbtn btn-simple">Search</button>
                            &nbsp; &nbsp; <a href="<?php echo URL::to('admin/list-management'); ?>"
                                             class="btn btn-primary addtransporterbtn btn-simple">Reset</a>
                        </div>
                    </form>
                </div>


                <?php
                $counter = 0;
                if (!empty($listingresults)) {
                    ?>
                    <div class="col-sm-12 table-layout mt-40">

                        <div class="repeater-list-table panel panel-default table-body-container">
                            <div class="row no-mrgn data-div-table panel-heading">
                                <div class="col-1 col-sm-1 col-md-1 col-first">
                                    <span>Ad ID</span>
                                </div>
                                <div class="col-2 col-sm-2 col-md-2 col-send">
                                    <span>Title</span>
                                </div>

                                <div class="col-1 col-sm-1 col-md-1 col-third text-center">
                                    <span>Category</span>
                                </div>

                                <div class="col-1 col-sm-1 col-md-1 col-fourth text-center">
                                    <span>Posted Date</span>
                                </div>
                                <div class="col-1 col-sm-1 col-md-1 col-five text-center">
                                    <span>Posted By</span>
                                </div>
                                <div class="col-1 col-sm-1 col-md-1 col-six text-center">
                                    <span>Amount</span>
                                </div>
                                <div class="col-1 col-sm-1 col-md-1 col-seven text-center">
                                    <span>Status</span>
                                </div>
                                <div class="col-3 col-sm-3  col-md-3 col-eight">
                                    <span>Actions</span>
                                </div>
                            </div>
                            <div class="data-body-content-ability">
                                <?php
                                foreach ($listingresults['records'] as $lr) {
                                    $counter++;
                                    ?>
                                    <div class="row no-mrgn data-div-table panel-heading repeater-list-ability-row">
                                        <div class="col-1 col-sm-1  col-md-1 col-first">
                                            <span>#<?php echo $lr['_source']['product_id']; ?></span>
                                        </div>
                                        <div class="col-2 col-sm-2  col-md-2 col-send">
                                            <span><?php echo $lr['_source']['title']; ?></span>
                                        </div>

                                        <div class="col-1 col-sm-1 col-md-1 col-third text-center">
                                            <span><?php echo $lr['_source']['categoryTitle']; ?></span>
                                        </div>

                                        <div class="col-1 col-sm-1 col-md-1 col-fourth text-center">
                                            <?php
                                            $timestamp = strtotime($lr['_source']['created_at']);
                                            $date = date('d-M-Y', $timestamp);
                                            ?>
                                            <span><?php echo $date; ?></span>
                                        </div>
                                        <div class="col-1 col-sm-1 col-md-1 col-five text-center">
                                            <span><?php echo $lr['_source']['name']; ?></span>
                                        </div>
                                        <div class="col-1 col-sm-1 col-md-1 col-six text-center">
                                            <span>Z<?php echo $lr['_source']['price']; ?></span>
                                        </div>
                                        <div class="col-1 col-sm-1 col-md-1 col-seven text-center">
                                            <span>
                                                <?php
                                                $status = 'Active';
                                                if ($lr['_source']['status'] == 0) {
                                                    $status = 'Deactive';
                                                } else if ($lr['_source']['status'] == 2) {
                                                    $status = 'Deleted';
                                                } else if ($lr['_source']['status'] == 3) {
                                                    $status = 'Pending';
                                                } else if ($lr['_source']['status'] == 4) {
                                                    $status = 'Rejected';
                                                }
                                                echo $status;
                                                ?>


                                            </span>
                                        </div>
                                        <div class="col-3 col-sm-3  col-md-3 col-eight">
                                            <div class="action-box-ability">
                                                <?php $url = URL::to('admin/post-detail/' . $lr['_source']['slug']); ?>
                                                <a href="#" onclick="return popitup('<?php echo $url; ?>')">
                                                    View
                                                </a>
                                                <span></span>
                                                <a href="<?php echo URL::to('admin/edit-post/' . $lr['_source']['product_id']); ?>">
                                                    Edit
                                                </a>
                                                <span></span>
                                                <?php
                                                $status_text = 'Deactivate';

                                                if ($lr['_source']['status'] == 0 || $lr['_source']['status'] == 4 || $lr['_source']['status'] == 3) {
                                                    $status_text = 'Activate';
                                                } else if ($lr['_source']['status'] == 2) {
                                                    $status_text = 'Restore';
                                                }
                                                ?>
                                                <a href="#" class="dropdown-toggle" id="status-dropdown"
                                                   data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    Change Status
                                                </a>
                                                <div class="dropdown-menu" aria-labelledby="status-dropdown">

                                                    <a href="{{URL::to('admin/change-status',['id' => $lr['_source']['product_id'],'status'=>$status_text])}}"
                                                       class="dropdown-item">{{$status_text}}</a>
                                                       <?php if ($lr['_source']['status'] == 3) { ?>
                                                        <a href="{{URL::to('admin/change-status',['id' => $lr['_source']['product_id'],'status'=>"Reject"])}}"
                                                           class="dropdown-item">Reject</a>
                                                       <?php } ?>

                                                </div>

                                            </div>
                                        </div>
                                    </div> <!-- repeater-list-ability-row -->
                                <?php } ?>
                            </div>
                        </div>
                    </div><!-- table-layout -->
                </div>
                <?php
            }
            if ($counter == 0) {
                echo '<div class="col-md-9"><br /><h2>No record found</h2></div>';
            }
            ?>
        </div>
        <div class="container">
            <div class="row">
                <div class="custom-pagination col-sm-12 mt-15">
                    <?php

                    function remove_url_query($url, $key) {
                        $url = preg_replace('/(?:&|(\?))' . $key . '=[^&]*(?(1)&|)?/i', "$1", $url);
                        $url = rtrim($url, '?');
                        $url = rtrim($url, '&');
                        return $url;
                    }

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
                    if (isset($listingresults['total']) && isset($listingresults['records'])) {
                        // echo 'yes';
                        $per_page = 10;
                        $total_records = $listingresults['total'];
                        $max = $total_records / $per_page;
                        $page_number = isset($_GET['page']) ? $_GET['page'] : 1;
                        unset($_GET['page']);
                        $pageurl = Request::fullUrl();

                        // delete edit parameter;
                        $pageurl = remove_url_query($pageurl, 'page');
                        if (!$_GET) {
                            $pageurl = $pageurl . '?';
                        }
                        if ($listingresults['total'] > $per_page) {
                            echo paginate(1, $page_number, 5, $max, $pageurl);
                        }
                    }
                    ?>

                </div>
            </div>
        </div>
    </div>
</div><!-- dshbrdinadmin -->
</section>
<script>
    function popitup(url) {
        newwindow = window.open(url, 'name', 'height=800,width=800');
        if (window.focus) {
            newwindow.focus()
        }
        return false;
    }


</script>
<script>
    jQuery(document).ready(function () {
        jQuery(document).on('change', '#vendor-filters .search-field', function () {
            jQuery('#vendor-filters').trigger('submit');
        });
    });
    $(function () {
        $("#vendor-filters").submit(function () {
            $(this).find(":input[type=text],select,input[type=hidden]").filter(function () {
                return !this.value;
            }).attr("disabled", "disabled");


            return true; // ensure form still submits
        });
    });

</script>

@endsection