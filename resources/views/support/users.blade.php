@extends('layouts.new-main')
@section('content')
@section('title', 'Users Management')


<section class="admin-dashboard dshbrdin buyer mt-0 user-management">
    @include('support.supportsidebar')
    <div class="dshbrdinadmin list-management">
        <div class="container">
            <div class="row">
                <div class="col-sm-12 mt-40">
                    <h3>User Management</h3>
                </div>
                <div class="alert alert-success alert-success-mbt" id="success_message" role="alert" style="display: none;">
                    User role changed successfully
                </div>
                <div class="alert alert-danger alert-success-mbt" id="error_message" role="alert" style="display:none;">
                    At least one user in the system must be  admin role.
                </div>
                @if(Session::has('SuccessMessage'))
                <div class="alert alert-success alert-success-mbt" id="success_msg" role="alert">
                    {{ Session::get('SuccessMessage') }}
                    <script>
                        setTimeout(function () {
                            jQuery("#success_msg").fadeOut('slow');
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

                <div class="form-group clearfix mb-0 pull-right-large pull-right col-sm-10">

                    <form action="{{URL::to('/support/allusers')}}" method="get" id="search-filters">
                        <div class="row">  
                            <div class="col-sm-5 col-md-4">

                                <div class="search-field-ability">

                                    <input type="search" value="<?php echo isset($_GET["keyword"]) ? $_GET["keyword"] : ''; ?>" name="keyword" class="form-control autocomplete-field3" placeholder="Search">
                                </div>
                            </div>
                            <div class="col-sm-5 col-md-4">


                                <select class="form-control search-fieldd" id=" " name="userrole">
                                    <option value="">Please select role</option>
                                    <option value="1"<?php
                                    if (isset($_GET['userrole']) && $_GET['userrole'] == 1) {
                                        echo 'selected';
                                    }
                                    ?>>Super Admin</option>
                                    <option value="2" <?php
                                    if (isset($_GET['userrole']) && $_GET['userrole'] == 2) {
                                        echo 'selected';
                                    }
                                    ?>>Store owner</option>
                                    <option value="4" 
                                    <?php
                                    if (isset($_GET['userrole']) && $_GET['userrole'] == 4) {
                                        echo 'selected';
                                    }
                                    ?>
                                            >Normal User</option>
                                    <option value="5" <?php
                                    if (isset($_GET['userrole']) && $_GET['userrole'] == 5) {
                                        echo 'selected';
                                    }
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
                            <a href="{{URL::TO('/support/allusers')}}" class="btn btn-primary addtransporterbtn btn-simple" style="margin-left:5px;">Reset</a>
                        </div>
                    </form>
                </div>
                <?php if (!empty($allUsers)) { ?>
                    <div class="col-sm-12 table-layout mt-40">

                        <div class="repeater-list-table panel panel-default table-body-container">
                            <div class="row no-mrgn data-div-table panel-heading">
                                <div class="col-1 col-sm-1 col-md-1 user-col-first">
                                    <span>User ID</span>
                                </div>
                                <div class="col-2 col-sm-2 col-md-2 user-col-send">
                                    <span>Name</span>
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

                                <div class="col-3 col-sm-3  col-md-3 user-col-eight">
                                    <span>Actions</span>
                                </div>
                            </div>
                            <div class="data-body-content-ability">
                                <?php foreach ($allUsers['records'] as $au) {
                                    ?>
                                    <div class="row no-mrgn data-div-table panel-heading repeater-list-ability-row">
                                        <div class="col-1 col-sm-1 col-md-1 user-col-first">
                                            <span>#<?php echo $au['_source']['user_id']; ?></span>
                                        </div>
                                        <div class="col-2 col-sm-2 col-md-2 user-col-send">
                                            <span><?php echo $au['_source']['name']; ?></span>
                                        </div>

                                        <div class="col-3 col-sm-3 col-md-3 user-col-third text-center">
                                            <span><?php echo $au['_source']['email']; ?></span>
                                        </div>

                                        <div class="col-1 col-sm-1 col-md-1 user-col-fourth text-center">
                                            <span>
                                                <?php
                                                if ($au['_source']['status'] == 1) {
                                                    echo "Active";
                                                } else {
                                                    echo "Deactivate";
                                                }
                                                ?>
                                            </span>
                                        </div>
                                        <div class="col-2 col-sm-2 col-md-2 user-col-five text-center">
                                            <span><?php
                                                $roleId = "";
                                                if (isset($_GET['userrole']) && $_GET['userrole'] != "") {
                                                    $roleId = $_GET['userrole'];
                                                }
                                                $userroles = getUsersRoles($au['_source']['user_id'], $roleId);
                                                $numItems = count($userroles);
                                                $i = 0;
                                                foreach ($userroles as $ur) {
                                                    if ($ur['role_status'] == "1")
                                                        if (++$i != $numItems) {
                                                            echo $ur['role_name'] . ", ";
                                                        } else {
                                                            echo $ur['role_name'];
                                                        }
                                                }
                                                ?></span>
                                        </div>

                                        <div class="col-3 col-sm-3  col-md-3 user-col-eight">
                                            <div class="action-box-ability">
                                                <a href="<?php echo URL::to('support/userdetail/' . $au['_source']['user_id']) ?>">
                                                    View
                                                </a>

                                                <span></span>
                                                <span class="dropdown no-border">
                                                    <span></span>
                                                    <a href="javascript:;" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        Edit
                                                    </a>
                                                    <div class="dropdown-menu user-role-pop " aria-labelledby="dropdownMenuButton">

                                                        <div class="role-assignment" onclick=" event.stopPropagation();">
                                                            <div class="checkbox-list ">
                                                                <label class="container-check">Normal User
                                                                    <?php
                                                                    $normalUser = '';
//                                                                    normal user role is 4
                                                                    $roleId = 4;
                                                                    $role = getUserRolesId($roleId, $au['_source']['user_id']);

                                                                    if (!empty($role) && $role['role_status'] == "1") {
                                                                        $normalUser = 'checked="checked"';
                                                                    }
                                                                    ?>
                                                                    <input type="checkbox" name="normaluser" <?php echo $normalUser; ?> class="user-roles" value="4" data-id="<?php echo $au['_source']['user_id']; ?>">
                                                                    <span class="checkmark"></span>
                                                                </label>
                                                            </div>
                                                            <div class="checkbox-list ">
                                                                <?php
                                                                $support = "";
                                                                $roleId = 5;
                                                                $role = getUserRolesId($roleId, $au['_source']['user_id']);
                                                                if (!empty($role) && $role['role_status'] == "1") {
                                                                    $support = 'checked="checked"';
                                                                }
                                                                ?>
                                                                <label class="container-check">Support

                                                                    <input type="checkbox" name="support" <?php echo $support; ?> class="user-roles" data-id="<?php echo $au['_source']['user_id']; ?>" value="5">
                                                                    <span class="checkmark"></span>
                                                                </label>
                                                            </div>
                                                            <?php
                                                            $userId = Session::get('userData')['user_id'];
                                                            if ($userId != $au['_source']['user_id']) {
                                                                ?>
                                                                <div class="checkbox-list ">
                                                                    <?php
                                                                    $admin = "";
                                                                    $roleId = 1;
                                                                    $role = getUserRolesId($roleId, $au['_source']['user_id']);
                                                                    if (!empty($role) && $role['role_status'] == "1") {
                                                                        $admin = 'checked="checked"';
                                                                    }
                                                                    ?>
                                                                    <label class="container-check">Admin
                                                                        <input type="checkbox" name="admin" <?php echo $admin; ?> class="user-roles" data-id="<?php echo $au['_source']['user_id']; ?>" value="1">
                                                                        <span class="checkmark"></span>
                                                                    </label>
                                                                </div>
                                                            <?php } ?>
                                                            <?php
                                                            $storeData = getStoreDataByUserId($au['_source']['user_id']);
                                                            if (!empty($storeData)) {
                                                                ?>
                                                                <div class="checkbox-list ">
                                                                    <?php
                                                                    $storeOwner = "";
                                                                    $roleId = 2;
                                                                    $role = getUserRolesId($roleId, $au['_source']['user_id']);
                                                                    if (!empty($role) && $role['role_status'] == "1") {
                                                                        $storeOwner = 'checked="checked"';
                                                                    }
                                                                    ?>
                                                                    <label class="container-check">Store Owner
                                                                        <input type="checkbox" name="storeownwer" <?php echo $storeOwner; ?> class="user-roles" data-id="<?php echo $au['_source']['user_id']; ?>" value="2">
                                                                        <span class="checkmark"></span>
                                                                    </label>
                                                                </div>
                                                            <?php } ?>
                                                        </div>

                                                        <!--                                                        <div class="edit-active buttons-feature-group clearfix dropdown mt-10 mb-10">
                                                                                                                            <a href="javascript:;" class="btn btn-primary btn-simple f-left-md">Cancel</a>
                                                                                                                            <button type="button" class="btn btn-primary text-white f-right-md updaterole">
                                                                                                                                <span class="small-screen-btn">Save</span>
                                                                                                                            </button>
                                                                
                                                                                                                        </div>-->


                                                    </div>                                                


                                                </span>



                                                <span></span>

                                                <span class="dropdown no-border">
                                                    <a href="javascript:;" class="dropdown-toggle" id="status-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        Change Status
                                                    </a>                                                                        
                                                    <div class="dropdown-menu" aria-labelledby="status-dropdown">
                                                        <?php
                                                        $statusText = "Activate";
                                                        if ($au['_source']['status'] == 1) {
                                                            $statusText = "Deactivate";
                                                        }
                                                        ?>
                                                        <a  href="{{URL::to('support/users-update',['id' => $au['_source']['user_id'],'status'=>$statusText])}}" class="dropdown-item">{{$statusText}}</a>
                                                    </div>
                                                </span>


                                            </div>
                                        </div>
                                    </div> <!-- repeater-list-ability-row --> 
                                <?php } ?>
                            </div>
                        </div>  

                    </div><!-- table-layout -->

                </div>
                <div class="row">
                    <div class="custom-pagination col-sm-12 mt-15">
                        <?php
                        $q = [];
                        if (isset($_GET['userrole'])) {
                            $q['userrole'] = $_GET['userrole'];
                        }
                        ?>

                    </div>
                </div>
                <?php
                if (isset($allUsers['total']) && isset($allUsers['records'])) {

                    function remove_url_query($url, $key) {
                        $url = preg_replace('/(?:&|(\?))' . $key . '=[^&]*(?(1)&|)?/i', "$1", $url);
                        $url = rtrim($url, '?');
                        $url = rtrim($url, '&');
                        return $url;
                    }

                    $per_page = 10;
                    $total_records = $allUsers['total'];
                    $max = $total_records / $per_page;
                    $max = ceil($max);
                    $page_number = isset($_GET['page']) ? $_GET['page'] : 1;
                    unset($_GET['page']);
                    $pageurl = Request::fullUrl();

                    // delete edit parameter;
                    $pageurl = remove_url_query($pageurl, 'page');
                    if (!$_GET) {
                        $pageurl = $pageurl . '?';
                    }
                    echo paginate(1, $page_number, 5, $max, $pageurl);
                }
            }
            ?>

        </div>
    </div><!-- dshbrdinadmin -->

</section>
<script>
    $(document).ready(function () {
        $(".user-roles").on("change", function () {
            var result = confirm(" Are you sure want to update role.?");
            if (!result) {
                return false;
            }
            var data = {};
            var role = $(this).val();
            var userId = $(this).attr('data-id');
            data['role_id'] = role;
            data['user_id'] = userId;
            sendreq(data, 'admin', 'assignrole', 'callBackResponse');
        });
    });
    function callBackResponse(data) {
        console.log(data);
        if (data == 'success') {
            jQuery("#success_message").show();
            setTimeout(function () {
                window.location.reload();
            }, 3000);
        } else {
            jQuery("#error_message").show();
            setTimeout(function () {
                window.location.reload();
            }, 3000);
        }
//        setTimeout(function () {
//            window.location.reload();
//        }, 3000);
    }

</script>
<script>
    jQuery(document).ready(function () {
        jQuery(document).on('change', '#users-filters', function () {
            jQuery('#users-filters').trigger('submit');
        });
    });
    $(function ()
    {
        $("#search-filters").submit(function ()
        {
            $(this).find(":input[type=search],select,input[type=hidden]").filter(function () {
                return !this.value;
            }).attr("disabled", "disabled");


            return true; // ensure form still submits
        });
    });
</script>
@endsection