@include('layouts.frontend-header-v2')
@section('title', 'Users Notifications')
<style>
   @media screen and (max-width: 767px) { 
       main > section div.col-sm-12.mt-40.mb-20 > h2{
         margin-top: -5px!important;
       }
    }
</style>
<main>
<section class="admin-dashboard dshbrdin buyer mt-0 user-management">
    <div class="dshbrdinadmin list-management">
        <div class="container">
            <div class="row">
                <div class="col-sm-12 mt-40 mb-20">
                    <h2 class="mt-3">Notifications</h2>
                </div>
               <div class="form-group clearfix mb-0 pull-right-large pull-right col-sm-12">

<!--                    <form action="{{URL::to('/admin/allusers')}}" method="get" id="search-filters">
                        <div class="row">  
                        <div class="col-sm-5 col-md-4 pr-7">
                           
                            <div class="search-field-ability">

                                <input type="search" value="<?php echo isset($_GET["keyword"]) ? $_GET["keyword"]:'';  ?>" name="keyword" class="form-control autocomplete-field3 l-height1" placeholder="Search">
                            </div>
                        </div>-->
<!--                        <div class="col-sm-5 col-md-4 pl-7">

                             <select class="form-control search-fieldd" id=" " name="userrole">
                                <option value="">Please select role</option>
                                <option value="1"<?php
//                                if (isset($_GET['userrole']) && $_GET['userrole'] == 1) {
//                                    echo 'selected';
//                                }
                                ?>>Super Admin</option>
                                <option value="2" <?php
//                                if (isset($_GET['userrole']) && $_GET['userrole'] == 2) {
//                                    echo 'selected';
//                                }
                                ?>>Store owner</option>
                                <option value="4" 
                                <?php
//                                if (isset($_GET['userrole']) && $_GET['userrole'] == 4) {
//                                    echo 'selected';
//                                }
                                ?>
                                        >Normal User</option>
                                <option value="5" <?php
//                                if (isset($_GET['userrole']) && $_GET['userrole'] == 5) {
//                                    echo 'selected';
//                                }
                                ?>>Supporter</option>

                                <?php
// foreach ($parent_Categories as $key => $val) {
//     $selected = "";
//     if (isset($_GET['category']) && $_GET['category'] == $key) {
//         $selected = 'selected';
//     }
//     echo '<option value="' . $key . '" ' . $selected . '>' . $val . '</option>';
// }
                                ?>
                            </select>

                        </div>
                        <button type="submit" class="btn btn-primary addtransporterbtn btn-simple">Search</button>
                        <a href="{{URL::TO('/admin/allusers')}}" class="btn btn-primary addtransporterbtn btn-simple" style="margin-left:5px;">Reset</a>
                        </div>
                    </form>
                </div>-->
                <?php if (!empty($notification)) { ?>
                    <div class="table-layout">

                        <div class="repeater-list-table panel panel-default table-body-container">
                            <!-- <div class="row no-mrgn data-div-table panel-heading">
                                            <div class="col-2 col-sm-2 col-md-1 user-col-first text-center">
                                                <span>No</span>
                                            </div>
                                            <div class="col-10 col-sm-10 col-md-11 user-col-send">
                                                <span>Subject</span>
                                            </div>
                              <div class="col-3 col-sm-3 col-md-3 user-col-third text-center">
                                    <span>Email</span>
                                </div>

                                <div class="col-1 col-sm-1 col-md-1 user-col-fourth text-center">
                                    <span>Status</span>
                                </div>
                                <div class="col-2 col-sm-2 col-md-2 user-col-five text-center">
                                    <span>Role</span>
                                </div>
                            </div> -->
                            <div class="data-body-content-ability">
                                <?php
                                // $counter=1;
                                foreach ($notification as $nf) {
                                    ?>
                                    <div class="row no-mrgn data-div-table panel-heading repeater-list-ability-row notify">
                                        <div class="col-2 col-sm-2 col-md-1 user-col-first text-center notifyin">
                                           #<span><?php echo $nf->notification_id; ?></span>
                                        </div>
                                        <div class="col-10 col-sm-10 col-md-11 user-col-send cnt-hd mark-read"  data-id="{{ $nf->notification_id }}">
                                            <b><a href="<?php echo $nf->url_link; ?>"><?php  echo $nf->subject; ?></a></b>
                                            <span class="pull-right" style="font-size: 12px;"><?php echo time_ago_in_php($nf->created_at);?></span>
                                        </div>
                                    </div> <!-- repeater-list-ability-row --> 
                                <?php 
                                
                                // $counter++;
                                } ?>
                            </div>
                        </div>  
                    </div><!-- table-layout -->

                </div>
                <div class="row w-100">
                    <div class="custom-pagination col-sm-12 mt-15 pull-left mb-20">
                       <?php  
                       $q = [];
                       echo $notification->appends($q)->links('pagination.defaultpagination'); ?>
                    </div>
                </div>
                <?php
                
            }else{
            ?>

            <?php } ?>
        </div>
    </div><!-- dshbrdinadmin -->

</section>
</main>
@include('layouts.frontend-footer-v2')