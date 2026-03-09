@extends('layouts.new-main')
@section('content')
@section('title', 'Coupon admin')
   <!-- Font Awesome JS -->
   <script defer src="https://use.fontawesome.com/releases/v5.0.13/js/solid.js"
   integrity="sha384-tzzSw1/Vo+0N5UhStP3bvwWPq+uvzCMfrN1fEFe+xBmv1C/AtVX5K0uZtmcHitFZ" crossorigin="anonymous">
</script>
<script defer src="https://use.fontawesome.com/releases/v5.0.13/js/fontawesome.js"integrity="sha384-6OIrr52G08NpOFSZdxxz1xdNSndlD4vdcf/q2myIUVO0VsqaGHJsB0RaBE01VTOY" crossorigin="anonymous">
</script>
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
                    @if (\Session::has('updatesuccess'))
                    <div class="alert alert-success">
                        <ul>
                            <li>{!! \Session::get('updatesuccess') !!}</li>
                        </ul>
                    </div>
                    @endif
                    
                    <div class="col-sm-6 mt-40">
                        <h3>Manage Coupons</h3> 
                    </div>
                    <div class="col-sm-6 mt-40 text-right"><a class="addfaqs-link btn btn-primary" href="{{url('admin/coupon-create')}}" style="color:#fff">Create Coupon</a></div>
                    @if (Session::has('error'))
                    <div class="col-sm-12">
                        <div class="alert alert-danger">
                            <ul>
                                <li>{!! Session::get('error') !!}</li>
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
                                <div class="col-2 col-sm-2 col-md-1 user-col-third text-center">Coupon ID</div>
                                <div class="col-5 col-sm-2 col-md-2 user-col-fourth text-center">Code</div>
                                <div class="col-2 col-sm-1 col-md-1 user-col-fourth text-center">Code Type</div>
                                <div class="col-2 col-sm-4 col-md-3 user-col-fourth text-center">Description</div>
                                <div class="col-3 col-sm-1  col-md-1 user-col-eight">Coupon Amount</div>
                                <div class="col-3 col-sm-1  col-md-1 user-col-eight">Usage / Limit </div>
                                <div class="col-3 col-sm-2  col-md-2 user-col-eight text-center">Expiry Date</div>
                                <div class="col-3 col-sm-1  col-md-1 user-col-eight text-center">Action</div>
                            </div>
                            <div class="data-body-content-ability">
                                <?php  $id = 0;  foreach ($coupons as $coupon) {  
                                    $id++; 
                                   
                                    ?>
                             <div class="row no-mrgn data-div-table repeater-list-ability-row" id="removeablecoupon">
                                <div class="col-2 col-sm-1 col-md-1 user-col-third text-center"> {{$id}} </div>
                                <div class="col-5 col-sm-2 col-md-2 user-col-third text-center">{{ $coupon->code_name  }}</div>
                                <div class="col-2 col-sm-1 col-md-1 user-col-fourth text-center">{{$coupon->type}}</div>
                                <div class="col-2 col-sm-4 col-md-3 user-col-fourth text-center">{{$coupon->description}}</div>
                                <div class="col-3 col-sm-1 col-md-1 text-center">{{$coupon->value_discount}} </div>
                                <div class="col-3 col-sm-1 col-md-1 text-center">{{$coupon->limit_number}} </div>
                                <div class="col-3 col-sm-2 col-md-2 text-center">{{date('F j, Y', strtotime($coupon->end_date))}} </div>
                                <div class="col-3 col-sm-1 col-md-1 text-center">
                                    <a href="{{ route('coupon.editCoupon', [$coupon->id]) }}">
                                        <i class="fas fa-edit  fa-lg"></i></a>
                                    <a class="deletecoupon" data-id="{{$coupon->id}}">
                                        <i class="fas fa-trash fa-lg text-danger"></i></a>
                                </div>

                             </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-center m-3">
                    {{ $coupons->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>

       <!-- dshbrdinadmin -->

    </section>
    <script>
    $(document).on('click', '.deletecoupon', function(e) {
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
                    url: '{{url('admin/coupondelete')}}',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        id: id
                    },
                    success: function(data) {
                        $("#removeablecoupon").remove(); //remove the tr without refreshing
                    }
                });
            });
            
    });
    </script>

@endsection