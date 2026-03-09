@extends('layouts.dashboard-main')
@section('content')
@section('title', 'Users Management')

<main>
<section class="mt-0 d-flex">
    @include('admin.adminsidebar')
    <div class="list-management w-100">
        <div class="container">
           <div class="row px-4 mb-4">
                <div class="col-sm-12 mt-40 d-flex justify-content-between align-items-center mb-10">
                    <h3 class="mb-0">User Management</h3>
                    @if(canAccess('create-user'))
                    <!-- Trigger button -->
                    <button class="btn btn-primary addtransporterbtn btn-simple create-user-btn" data-toggle="modal" data-target="#createUserModal">
                        Create User
                    </button>
                    @endif
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

                <div class="form-group mb-0 col-sm-10">
                        <form action="{{URL::to('/admin/allusers')}}" method="get" id="search-filters">
                            <div class="row">  
                                <div class="col-sm-5 col-md-4 pr-7">
                                    <div class="">
                                        <input type="search" value="<?php echo isset($_GET["keyword"]) ? $_GET["keyword"]:'';  ?>" name="keyword" class="form-control autocomplete-field3 l-height1" placeholder="Search">
                                    </div>
                                </div>
                                <div class="col-sm-5 col-md-4 pl-7">
                                    @php
                                        $roles = getAllRoles();
                                        $selectedRole = request()->get('userrole');
                                    @endphp

                                    <select class="form-control" name="userrole">
                                        <option value="">Please select role</option>

                                        @foreach($roles as $role)
                                            <option value="{{ $role['role_id'] }}"
                                                {{ $selectedRole == $role['role_id'] ? 'selected' : '' }}>
                                                {{ $role['role_name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary btn-simple">Search</button>
                                <a href="{{URL::TO('/admin/allusers')}}" class="btn btn-primary btn-simple" style="margin-left:5px;">Reset</a>
                            </div>
                        </form>
                    </div>
                    <?php if (!empty($allUsers)) { ?>
                        <div class="col-sm-12 table-layout mt-40">
                            <div class="repeater-list-table table-body-container">
                                <div class="row ml-0 mr-0 data-div-table">
                                    <div class="col-1 col-sm-1 col-md-1">
                                        <span>User ID</span>
                                    </div>
                                    <div class="col-2 col-sm-2 col-md-2">
                                        <span>Name</span>
                                    </div>

                                    <div class="col-3 col-sm-3 col-md-3 text-center">
                                        <span>Email</span>
                                    </div>

                                    <div class="col-1 col-sm-1 col-md-1 text-center">
                                        <span>Status</span>
                                    </div>
                                    <div class="col-2 col-sm-2 col-md-2 text-center">
                                        <span>Role</span>
                                    </div>
                                    <div class="col-3 col-sm-3 col-md-3">
                                        <span>Actions</span>
                                    </div>
                                </div>
                                <div class="data-body-content-ability">
                                    <?php 
                                        if(isset($_GET['keyword'])){

                                        }else{
                                            // unset($allUsers['records'][0]);
                                        }
                                        // unset($allUsers['records'][0]);
                                        //unset($allUsers['records'][1]);
                                        // if($_GET['page']== 5){
                                        //     unset($allUsers['records'][4]);
                                        // }
                                        // dd($allUsers);
                                        foreach ($allUsers as $au) {
                                        ?>
                                        <div class="row ml-0 mr-0 data-div-table">
                                            <div class="col-1 col-sm-1 col-md-1">
                                                <span>#<?php echo $au->user_id; ?></span>
                                            </div>
                                            <div class="col-2 col-sm-2 col-md-2">
                                                <span><?php echo $au->name; ?></span>
                                            </div>

                                            <div class="col-3 col-sm-3 col-md-3 text-center">
                                                <span><?php echo $au->email; ?></span>
                                            </div>

                                            <div class="col-1 col-sm-1 col-md-1 text-center">
                                                <span>
                                                    <?php
                                                    if ($au->status == 1) {
                                                        echo "Active";
                                                    } else {
                                                        echo "Deactivate";
                                                    }
                                                    ?>
                                                </span>
                                            </div>
                                            <div class="col-2 col-sm-2 col-md-2 text-center">
                                                <span><?php
                                                    $roleId = "";
                                                    if (isset($_GET['userrole']) && $_GET['userrole'] != "") {
                                                        $roleId = $_GET['userrole'];
                                                    }
                                                    $userroles = getUsersRoles($au->user_id, $roleId);
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
                                            @if (canAccess('edit-user'))
                                            <div class="col-3 col-sm-3  col-md-3">
                                                <div class="action-box-ability">
                                                    <a href="<?php echo URL::to('admin/userdetail/' . $au->user_id) ?>">
                                                        View
                                                    </a>

                                                    <!-- <span></span>
                                                    <span class="dropdown no-border"> -->
                                                        <!-- <span></span> -->
                                                        <!-- <a href="#" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            Edit
                                                        </a> -->
                                                        <div class="dropdown-menu user-role-pop " aria-labelledby="dropdownMenuButton">
                                                            <div class="role-assignment" onclick=" event.stopPropagation();">
                                                                <div class="checkbox-list ">
                                                                    <label class="container-check">Normal User
                                                                        <?php
                                                                        $normalUser = '';
                                                                        //normal user role is 4
                                                                        $roleId = 4;
                                                                        $role = getUserRolesId($roleId, $au->user_id);
                                                                        
                                                                        if ($role && $role['role_status'] == "1") {
                                                                            $normalUser = 'checked="checked"';
                                                                        }
                                                                        ?>
                                                                        <input type="checkbox" name="normaluser" <?php echo $normalUser; ?> class="user-roles" value="4" data-id="<?php echo $au->user_id; ?>">
                                                                        <span class="checkmark"></span>
                                                                    </label>
                                                                </div>
                                                                <div class="checkbox-list ">
                                                                    <?php
                                                                    $support = "";
                                                                    $roleId = 5;
                                                                    $role = getUserRolesId($roleId, $au->user_id);
                                                                    if ($role && $role['role_status'] == "1") {
                                                                        $support = 'checked="checked"';
                                                                    }
                                                                    ?>
                                                                    <label class="container-check">Support

                                                                        <input type="checkbox" name="support" <?php echo $support; ?> class="user-roles" data-id="<?php echo $au->user_id; ?>" value="5">
                                                                        <span class="checkmark"></span>
                                                                    </label>
                                                                </div>
                                                                <?php
                                                                $userId = Session::get('userData')['user_id'];
                                                                if ($userId != $au->user_id) {
                                                                    ?>
                                                                    <div class="checkbox-list ">
                                                                        <?php
                                                                        $admin = "";
                                                                        $roleId = 1;
                                                                        $role = getUserRolesId($roleId, $au->user_id);
                                                                        if ($role && $role['role_status'] == "1") {
                                                                            $admin = 'checked="checked"';
                                                                        }
                                                                        ?>
                                                                        <label class="container-check">Admin
                                                                            <input type="checkbox" name="admin" <?php echo $admin; ?> class="user-roles" data-id="<?php echo $au->user_id; ?>" value="1">
                                                                            <span class="checkmark"></span>
                                                                        </label>
                                                                    </div>
                                                                <?php } ?>
                                                                <?php
                                                                $storeData = getStoreDataByUserId($au->user_id);
                                                                if (!empty($storeData)) {
                                                                    ?>
                                                                    <div class="checkbox-list ">
                                                                        <?php
                                                                        $storeOwner = "";
                                                                        $roleId = 2;
                                                                        $role = getUserRolesId($roleId, $au->user_id);
                                                                        if ($role && $role['role_status'] == "1") {
                                                                            $storeOwner = 'checked="checked"';
                                                                        }
                                                                        ?>
                                                                        <label class="container-check">Store Owner
                                                                            <input type="checkbox" name="storeownwer" <?php echo $storeOwner; ?> class="user-roles" data-id="<?php echo $au->user_id; ?>" value="2">
                                                                            <span class="checkmark"></span>
                                                                        </label>
                                                                    </div>
                                                                <?php } ?>
                                                            </div>

                                                            <!--<div class="edit-active buttons-feature-group clearfix dropdown mt-10 mb-10">
                                                                <a href="#" class="btn btn-primary btn-simple f-left-md">Cancel</a>
                                                                <button type="button" class="btn btn-primary text-white f-right-md updaterole">
                                                                    <span class="small-screen-btn">Save</span>
                                                                </button>
                                                            </div>-->
                                                        </div>                                                


                                                    <!-- </span> -->



                                                    <span></span>

                                                    <span class="dropdown kuchkuch no-border">
                                                        <a href="#" class="dropdown-toggle" id="status-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            Change Status
                                                        </a>                                                                        
                                                        <div class="dropdown-menu" aria-labelledby="status-dropdown">
                                                            <?php
                                                            $statusText = "Activate";
                                                            if ($au->status == 1) {
                                                                $statusText = "Deactivate";
                                                            }
                                                            ?>
                                                            <a  href="{{URL::to('admin/users-update',['id' => $au->user_id,'status'=>$statusText])}}" class="dropdown-item">{{$statusText}}</a>
                                                        </div>
                                                    </span>


                                                </div>
                                            </div>
                                            @else
                                                <p class="badge bg-danger m-2 d-inline-flex align-items-center text-light" style="height: 30px;">
                                                    Access denied
                                                </p>
                                            @endif
                                        </div> <!-- repeater-list-ability-row --> 
                                    <?php } ?>
                                </div>
                            </div>  
                                                            
                        </div><!-- table-layout -->
            </div>
            <div class="row px-4">
                <div class="custom-pagination col-sm-12 mt-15">
                    <?php
                    $q = [];
                    if (isset($_GET['userrole'])) {
                        $q['userrole'] = $_GET['userrole'];
                    }
                    ?>

                </div>
            </div>
            <?php } ?>

        </div>
    </div><!-- dshbrdinadmin -->

    <!-- Create User -->


<!-- Modal -->
<div class="modal fade" id="createUserModal" tabindex="-1" role="dialog" aria-labelledby="createUserModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="createUserModalLabel">Add New User</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span> <!-- Bootstrap 4 style close -->
        </button>
      </div>

      <div class="modal-body">
        <form id="createUserForm">
          <div class="form-group">
            <label for="userName">Name <span style="color:red;">*</span></label>
            <input type="text" class="form-control" id="userName" maxlength="25" name="name" required>
          </div>
          <div class="form-group">
            <label for="userEmail">Email <span style="color:red;">*</span></label>
            <input type="email" class="form-control" id="userEmail" maxlength="100" name="email" required>
          </div>
        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="submit" form="createUserForm" class="btn btn-primary">Save</button>
      </div>

    </div>
  </div>
</div>


</section>
</main>
<!-- jQuery (required by Bootstrap 4) -->
<script src="https://code.jquery.com/jquery-3.5.0.min.js"></script>

<!-- Popper.js (required by Bootstrap 4 for modals, tooltips, etc.) -->
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>

<!-- Bootstrap 4 JS -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

   $(document).ready(function () {
        // Attach CSRF token for all AJAX calls
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Delete User Account
        $(document).on('click', '.delete-user-account', function () {
            var userId = $(this).data('user_id');

            Swal.fire({
                title: "Are you sure?",
                text: "This will permanently delete the user's data and soft-delete their account. This action cannot be undone.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, Delete Account",
                cancelButtonText: "Cancel",
                customClass: {
                    confirmButton: "btn btn-danger",
                    cancelButton: "btn btn-secondary"
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "DELETE",
                        url: "/admin/delete-user-account/" + userId,
                        success: function (response) {
                            if (response.status) {
                                Swal.fire({
                                    title: 'Deleted!',
                                    text: response.message,
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error',
                                    text: response.message,
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        },
                        error: function (xhr) {
                            var message = 'Something went wrong. Please try again.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                title: 'Error',
                                text: message,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                }
            });
        });

        // Clear form when modal is hidden
        $('#createUserModal').on('hidden.bs.modal', function () {
            $('#createUserForm')[0].reset();
        });

        // Show modal on button click
        $(document).on('click', '.create-user-btn', function () {
            $('#createUserModal').modal('show');
        });

        // Submit form via AJAX
        $('#createUserForm').on('submit', function (e) {
            e.preventDefault();

            let formData = {
                name: $('#userName').val(),
                email: $('#userEmail').val(),
            };

            $.ajax({
                url: '/admin/create-user',
                method: 'POST',
                data: formData,
                success: function (response) {
                    console.log('User created:', response);
                    $('#createUserModal').modal('hide');
                    alert('User Created Successfully.');
                },
                error: function (xhr) {
                    if (xhr.status === 422) {
                        // Laravel validation error response
                        let errors = xhr.responseJSON.errors;
                        let messages = [];

                        for (let field in errors) {
                            if (errors.hasOwnProperty(field)) {
                                messages.push(errors[field][0]);
                            }
                        }

                        alert('Validation failed:\n' + messages.join('\n'));
                    } else {
                        console.error('Error creating user:', xhr.responseText);
                        alert('Failed to create user.');
                    }
                }
            });
        });

    });

</script>
@endsection