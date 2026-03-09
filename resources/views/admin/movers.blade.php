@extends('layouts.new-main')
@section('content')
@section('title', 'List Management')
<?php
    $listingresults=array();
    ?>

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
            </div>   
            <div class="col-sm-6 col-md-3">
                <div class="search-field-ability">
                    <input type="search" name="" class="form-control" placeholder="Search">
                </div>
            </div>
           
            <?php
            if (!empty($listingresults)) {
                ?>
                <div class="col-sm-12 table-layout mt-40">

                    <div class="repeater-list-table panel panel-default table-body-container">
                        <div class="row no-mrgn data-div-table panel-heading">
                            <div class="col-xs-1 col-sm-1 col-md-1 col-first">
                                <span>Ad ID</span>
                            </div>
                            <div class="col-xs-2 col-sm-2 col-md-2 col-send">
                                <span>Title</span>
                            </div>

                            <div class="col-xs-1 col-sm-1 col-md-1 col-third text-center">
                                <span>Category</span>
                            </div>

                            <div class="col-xs-1 col-sm-1 col-md-1 col-fourth text-center">
                                <span>Posted Date</span>
                            </div>
                            <div class="col-xs-1 col-sm-1 col-md-1 col-five text-center">
                                <span>Posted By</span>
                            </div>
                            <div class="col-xs-1 col-sm-1 col-md-1 col-six text-center">
                                <span>Amount</span>
                            </div>
                            <div class="col-xs-1 col-sm-1 col-md-1 col-seven text-center">
                                <span>Status</span>
                            </div>
                            <div class="col-xs-3 col-sm-3  col-md-3 col-eight">
                                <span>Actions</span>
                            </div>
                        </div>
                        <div class="data-body-content-ability">
                            <?php foreach ($listingresults as $lr) { ?>
                                <div class="row no-mrgn data-div-table panel-heading repeater-list-ability-row">
                                    <div class="col-xs-1 col-sm-1  col-md-1 col-first">
                                        <span>#<?php echo $lr->product_id; ?></span>
                                    </div>
                                    <div class="col-xs-2 col-sm-2  col-md-2 col-send">
                                        <span><?php echo $lr->title; ?></span>
                                    </div>

                                    <div class="col-xs-1 col-sm-1 col-md-1 col-third text-center">
                                        <span><?php echo $lr->categoryTitle; ?></span>
                                    </div>

                                    <div class="col-xs-1 col-sm-1 col-md-1 col-fourth text-center">
                                        <?php
                                        $timestamp = strtotime($lr->created_at);
                                        $date = date('d-M-Y', $timestamp);
                                        ?>
                                        <span><?php echo $date; ?></span>
                                    </div>
                                    <div class="col-xs-1 col-sm-1 col-md-1 col-five text-center">
                                        <span><?php echo $lr->name; ?></span>
                                    </div>
                                    <div class="col-xs-1 col-sm-1 col-md-1 col-six text-center">
                                        <span>Z<?php echo $lr->price; ?></span>
                                    </div>
                                    <div class="col-xs-1 col-sm-1 col-md-1 col-seven text-center">
                                        <span>
                                            <?php
                                            $status = 'Active';
                                            if ($lr->status == 0) {
                                                $status = 'Deactive';
                                            }
                                            echo $status;
                                            ?>


                                        </span>
                                    </div>
                                    <div class="col-xs-3 col-sm-3  col-md-3 col-eight">
                                        <div class="action-box-ability">
                                            <?php $url = URL::to('admin/post-detail/' . $lr->slug); ?>
                                            <a href="#" onclick="return popitup('<?php echo $url; ?>')">
                                                View
                                            </a>
                                            <span></span>
                                            <a href="<?php echo URL::to('admin/edit-post/' . $lr->product_id); ?>">
                                                Edit
                                            </a>
                                            <span></span>    
                                            <?php
                                            $status_text = 'Deactivate';

                                            if ($lr->status == 0) {
                                                $status_text = 'Activate';
                                            }
                                            ?>
                                            <a href="#" class="dropdown-toggle" id="status-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                Change Status
                                            </a>                                                                        
                                            <div class="dropdown-menu" aria-labelledby="status-dropdown">

                                                <a  href="{{URL::to('admin/change-status',['id' => $lr->product_id,'status'=>$status_text])}}" class="dropdown-item">{{$status_text}}</a>
                                            </div>

                                        </div>
                                    </div>
                                </div> <!-- repeater-list-ability-row --> 
                            <?php } ?>
                        </div>
                    </div>
                </div><!-- table-layout -->
            <?php } ?>
        </div>
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
             //   echo $listingresults->appends($q)->links('pagination.defaultpagination');
                ?>
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
</script>

@endsection