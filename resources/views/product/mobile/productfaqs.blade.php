@extends('layouts.mobile-main')
@section('content')
@section('title', 'All Faqs')

<main>
    <section class="admin-dashboard dshbrdin buyer mt-0 user-management manage-categories-section">
        <div class="dshbrdinadmin list-management">
            
            <div class="container">
                <div class="row">
                    <?php if(count($productDetails['faqs']) > 0) { ?>
                @if (\Session::has('success'))
                    <div class="alert alert-success">
                        <ul>
                            <li>{!! \Session::get('success') !!}</li>
                        </ul>
                    </div>
                    @endif
                    
                    <div class="col-sm-6 mt-40">
                        <h3>Manage Product FAQs</h3> 
                    </div>
                    <!-- <div class="col-sm-6 mt-40 text-right"><a class="addfaqs-link btn btn-primary" href="{{url('admin/addfaqs')}}" style="color:#fff">Add FAQs</a></div> -->
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
                                <div class="col-2 col-sm-2 col-md-2 user-col-third text-center">Faqs ID</div>
                                <div class="col-5 col-sm-5 col-md-5 user-col-fourth text-center">Questions</div>
                                <div class="col-2 col-sm-2 col-md-2 user-col-fourth text-center">Answer</div>
                                <div class="col-3 col-sm-3  col-md-3 user-col-eight">Actions</div>
                            </div>
                            <div class="data-body-content-ability">
                                <?php 
                                //  echo 'Data: <pre>' .print_r($productDetails['faqs'],true). '</pre>'; die();
                                $id = 0;  foreach ($productDetails['faqs'] as $faq) {  
                                    $id++; 
                                    ?>
                                <div class="row no-mrgn data-div-table repeater-list-ability-row" id="removeablefaqs">

                                    <div class="col-2 col-sm-2 col-md-2 user-col-third text-center"> {{$id}}
                                    </div>
                                    <div class="col-5 col-sm-5 col-md-5 user-col-third text-center">
                                      
                                        {{ $faq->question  }}
                                    </div>
                                   
                                    <?php
                                 
                                    ?>
                                    <div class="col-2 col-sm-2 col-md-2 user-col-fourth text-center">{{$faq->answer}}</div>

                                    <div class="col-3 col-sm-3 col-md-3 text-center">
                                        <a href="{{ route('vendor.editFaqs', [$faq->faq_id]) }}" class="editslide-link">Edit</a> | <a class="deletefaqs" data-id="{{$faq->faq_id}}">Delete</a>
                                    </div>

                                </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-center m-3">
                
                    </div>
                    <?php } else{ ?>
                        <p style= "padding:100px;">No Faqs Added to Clients</p>
                </div>
                <?php } ?>
            </div>
        </div>
    </section>
    <script>
    $(document).on('click', '.deletefaqs', function(e) {
        var id = $(this).data('id');

        swal({
                title: "Are you sure!",
                text: "You are Delete Faqs!",
                icon: "warning",
                buttons: true,
                showCancelButton: true,  
                dangerMode: true,
            },
             
            function() {
                $.ajax({
                    type: "POST",
                    url: '{{url('deleteproductfaq')}}',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        id: id 
                    },
                    success: function(data) {
                        // $("#removeablefaqs").remove(); //remove the tr without refreshing
                        setTimeout(function () {
                        location.reload();
                    }, 1000);
                        
                    }
                });
            });
    });
    </script>
    
    @endsection