@extends('layouts.new-main')
@section('content')
@section('title', 'All Vendor Accounts')

<main>
    <section class="admin-dashboard dshbrdin buyer mt-0 user-management manage-categories-section">
        @include('admin.adminsidebar')
        <div class="dshbrdinadmin list-management">

            <div class="container">
                <div class="row">
                @if (\Session::has('success'))
                    <div class="alert alert-success">
                        <ul>
                            <li>{!! \Session::get('success') !!}</li>
                        </ul>
                    </div>
                    @endif
                    
                    <div class="col-sm-6 mt-40">
                        <h3>Manage Stripe Vandor Accounts</h3> 
                    </div>
                   
                    @if (Session::has('message_error'))
                    <div class="col-sm-12">
                        <div class="alert alert-danger">
                            <ul>
                                <li>{!! Session::get('message_error') !!}</li>
                            </ul>
                        </div>
                    </div>
                    @endif
                    @if (Session::has('message_success'))
                    <div class="col-sm-12">

                        <div class="alert alert-success">
                            <ul>
                                <li>{!! Session::get('message_success') !!}</li>
                            </ul>
                        </div>
                    </div>
                    @endif
                    <div class="col-sm-12 table-layout mt-40">
                        <div class="repeater-list-table panel panel-default table-body-container">
                            <div class="row no-mrgn data-div-table panel-heading">
                                <div class="col-2 col-sm-4 col-md-4 user-col-third text-center">Account ID</div>
                                <div class="col-5 col-sm-3 col-md-3 user-col-fourth text-center">User Email</div>
                                <div class="col-2 col-sm-2 col-md-2 user-col-fourth text-center">Status</div>
                                <div class="col-3 col-sm-3  col-md-3 user-col-eight">Actions</div>
                            </div>
                            <div class="data-body-content-ability">
                                <?php  $id = 0;  foreach ($accounts as $acc) {  
                                    $id++; 
                                    ?>
                                <div class="row no-mrgn data-div-table repeater-list-ability-row" id="removeablefaqs">

                                    <div class="col-2 col-sm-4 col-md-4 user-col-third text-center"> {{$acc->id}}
                                    </div>
                                    <div class="col-5 col-sm-3 col-md-3 user-col-third text-center">
                                      
                                        {{ $acc->email  }}
                                    </div>
                                   
                            
                                    <div class="col-2 col-sm-2 col-md-2 user-col-fourth text-center"></div>

                                    <div class="col-3 col-sm-3 col-md-3 text-center">
                                        <a class="deletefaqs" data-id="{{$acc->id}}">Delete</a>
                                    </div>

                                </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-center m-3">
                   
                    </div>
                </div>
            </div>
        </div>

       <!-- dshbrdinadmin -->

    </section>
    <script>
    $(document).on('click', '.deletefaqs', function(e) {
        var id = $(this).data('id');

        swal({
                title: "Are you sure!",
                text: "You are Delete Faqs!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            },
             
            function() {
                $.ajax({
                    type: "POST",
                    url: '{{url('admin/deletefaq')}}',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        id: id
                    },
                    success: function(data) {
                        $("#removeablefaqs").remove(); //remove the tr without refreshing
                    }
                });
            });
    });
    </script>

    @endsection