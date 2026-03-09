@extends('layouts.dashboard-main')

@section('content')
@section('title', 'Faqs')
<main>
    @include('admin.adminsidebar')

    <style>
    @media only screen and (min-width : 767px) and (max-width : 10000px) {
        .search-icon-header {
            display: none;
        }
    }
    </style>
    <style>
    .tmpbx {
        box-shadow: 0 0 5px 0 #64646459;
        display: inline-block
    }

    .clpgbx {
        background-color: #fff;
        box-shadow: 0 0 5px 0 #64646459;
        width: 100%;
        padding: 30px;
        border-radius: 3px;
        margin-bottom: 10px
    }

    .ovrly {
        background-color: #00a08e;
        position: absolute;
        width: 100%;
    }
    </style>
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
 CKEDITOR.config.versionCheck = false;
</script>
    <section>
        <div class="card-body faqs-body-add no-padd-left ">
            <div class="container">
                <div class="row">
                    @if (\Session::has('success'))
                    <div class="alert alert-success">
                        <ul>
                            <li>{!! \Session::get('success') !!}</li>
                        </ul>
                    </div>
                    @endif
                    
                    <div class="col-md-6 mt-40">
                        <h3>Setup FAQs</h3>
                        
                    </div>

                    <div class="col-md-6 mt-40 text-right">
                         <a class="btn btn-primary btn-simple" href="{{URL::to('/admin/faqs')}}">Back</a> 
                    </div>
                   
                    <div class="col-md-12  ">
                        <form action="{{URL::to('/admin/store-faqs')}}" method="POST" id="addFaqs_section">
                            @csrf
                            <div class="form-group mb-15">
                                <label>
                                    Question (Asnwer the possible questions)
                                </label>
                                
                                <input type="text"  class="form-control" name="question">
                            </div>
                            <div class="form-group mb-15">
                                <label>
                                    Answer
                                </label>
                                <textarea id='page_content' class="form-control"  name="answer"></textarea>
                                
                            </div>
                            <div class="form-group mb-40">
                                <label> Question Type:  </label>
                                <select class="form-select " name="type" style="padding:5px; width:200px">
                                    <option value="general-faqs">General Faqs</option>
                                    <option value="buyer-faqs">Buyer Faqs</option>
                                    <option value="seller-faqs">Seller Faqs</option>
                                </select>
                            </div>
                            <div class="add-button-option">
                                <input class="btn btn-primary btn-simple"  type="submit" value="Add FAQs">
                            </div>
                         
                            <!-- faqs-list-results -->
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </section>
    <script src="{{ URL::asset('assets/js/jquery.validate.js',env('HTTPS_STATUS')) }}"></script>
    <script>
    current_url = '<?php echo URL::to('/admin/content-pages'); ?>';

    CKEDITOR.replace('page_content');
 
    $("#addFaqs_section").validate({
        errorElement: "span",
        rules: {
            question: {required: true},
            answer: {required: true},

         
        },
        messages: {
            question: {required: "Please Enter Question."},
            answer: {required: "Please Enter Answer."},
           
        },
        errorPlacement: function (error, element) {
            error.appendTo(element.closest('.form-group'));
        },
        submitHandler: function (form) {
            form.submit();
        }
    });
    </script>

</main>
@endsection
<!-- Content Ends-->