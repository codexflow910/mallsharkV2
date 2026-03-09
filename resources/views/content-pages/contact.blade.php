@extends('layouts.frontend-main')
@section('content')
@section('title', 'Contact Us')
@section('description', "Contact MallShark for easy and quick support. Simply write your query on our contact page or use our online chat for instant assistance. We're here to help!")
@section('google-analytics')
@include('layouts.google-analytics')
@stop
<main>
<!-- <script src="{{ URL::asset('assets/js/jquery.validate.js'),env('HTTPS_STATUS') }}"></script> -->
<script src="{{ URL::asset('assets/js/form-validation.js',env('HTTPS_STATUS')) }}"></script>
    <style>
        @media only screen and (min-width : 767px) and (max-width : 10000px) {

        }
    </style>
    <Style>

        /* Style for centering the content */
        .thank-you-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 60vh; 
            text-align: center;
        }

        /* Style for the message */
        .thank-you-message {
            font-size: 2rem; /* Increase the font size */
            color: #333;
        }

        .thank-you-message p {
            margin-top: 1rem;
            font-size: 1.2rem; /* Adjust size of the smaller text */
            color: #0510FA;
        }

    </Style>



<section class="cntct ftrset">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    @if(Session::has('success')) 
                    <!-- Thank You Section -->
                    <div class="thank-you-container">
                        <div class="thank-you-message">
                            <h1>Thank you! Your support request has been submitted successfully.</h1>
                            <p>Our support team will review it and respond shortly.</p>
                        </div>
                    </div>
                    
                    @else
                    <div class="terms-and-condition-heading">
                        <h1>Contact MallShark?</h1> 
                        <h4>How can we help you?</h4>
                    </div>
                    <div class="faqsec"> 
                            <div class=" ">
                                <div class="row">
                                <div class="col-sm-12">            
                                        <div class="box-text-with-icons">  
                                                    <div class="col-sm-12">
                                <div class="pos-rel mt-0">

                                <div class=" ">
                                    
                                    @if (count($errors) > 0)
                                    <div class="alert alert-danger" id="errorMessage">
                                    <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                    </ul>
                                    </div>
                                    @endif

                                    <script>
                                        setTimeout(function() {
                                        var successMessage = document.getElementById('successMessage');
                                        var errorMessage = document.getElementById('errorMessage');
                                        
                                        if (successMessage) {
                                            successMessage.remove();
                                        }
                                        
                                        if (errorMessage) {
                                            errorMessage.remove();
                                        }
                                        }, 2000); // Timeout in milliseconds (2 seconds)
                                        </script> 

                                    <div class="row justify-content-center mt-30">


                                                         

                            <div class="media-container-column  " data-form-type="formoid">
                                <div data-form-alert="" hidden="">
                                    Thanks for filling out the form!
                                </div>

                                <form id="contactUserinfo" class="contact-us-form mbr-form" action="{{URL::to('/contact-us')}}" method="post" data-form-title="Mobirise Form">
                                    @csrf
                                    
                                    <div class="row row-sm-offset">
                                        <!-- <div class="shark_image">
                                            <img src="{{ URL::asset('assets/images/Shark22.png') }}" alt="image" srcset="">
                                        </div> -->
                                        <div class="col-md-6 multi-horizontal" data-for="name">
                                            <div class="form-group">
                                                <label class="form-control-label mbr-fonts-style display-7" for="name-form1-2w">First Name*</label>
                                                <input type="text" class="form-control" placeholder="First Name" name="first_name" data-form-field="Name"  id="name-form1-2w">
                                            </div>
                                        </div>
                                        <div class="col-md-6 multi-horizontal" data-for="name">
                                            <div class="form-group">
                                                <label class="form-control-label mbr-fonts-style display-7" for="name-form1-2w">Last Name</label>
                                                <input type="text" class="form-control" placeholder="Last Name" name="last_name" data-form-field="Name"  id="name-form1-2w">
                                            </div>
                                        </div>
                                        <div class="col-md-12 multi-horizontal" data-for="email">
                                            <div class="form-group">
                                                <label class="form-control-label mbr-fonts-style display-7" for="email-form1-2w">Email*</label>
                                                <input type="email" class="form-control" placeholder="Enter your email" name="email" data-form-field="Email" value="<?php if (isset($_GET['email'])) { echo $_GET['email']; } ?>" id="email-form1-2w">
                                            </div>
                                        </div>
                                        <div class="col-md-6 multi-horizontal" data-for="name">
                                            <div class="form-group">
                                                <label class="form-control-label mbr-fonts-style display-7" for="name-form1-2w">Support Type*</label>
                                                <select name="support" class="form-control form-select">
                                                    <option value="feature-request">Feature Request</option>
                                                    <option value="technical-issue">Technical Issue</option>
                                                    <option value="order-issue">Order Issue</option>
                                                    <option value="customer-support">Customer Support</option>
                                                    <option value="customer-support">Account Deletion Request</option>
                                                </select>
                                               
                                            </div>
                                        </div>
                                        <div class="col-md-6 multi-horizontal" data-for="phone">
                                            <div class="form-group">
                                                <label class="form-control-label mbr-fonts-style display-7" for="phone-form1-2w">Order Number</label>
                                                <input type="tel" class="form-control" name="phone" placeholder="Order Number" data-form-field="Phone" id="phone-form1-2w" maxlength="20">
                                            </div>
                                        </div>
                                        <div class="form-group col-md-12" data-for="message">
                                            <label class="form-control-label mbr-fonts-style display-7" for="message-form1-2w">Message*</label>
                                            <textarea type="text" class="form-control" placeholder="Tell us more." name="message" rows="4" data-form-field="Message" id="message-form1-2w"></textarea>
                                        </div>
                                        <div class="col-md-12 text-center">

                                            {{ romanzipp\Turnstile\Captcha::getChallenge('light') }}
                                        </div>
                                        <div class="col-md-12 text-center mt-20">
                                            <span class="input-group-btn ">
                                                <button href="" type="submit" class="btn btn-primary white-text btn-form btn-block">Send</button>
                                            </span>
                                        </div>
                                        
                                        <!-- <div class="bottom-contact-image">
                                                <img src="{{ URL::asset('assets/images/Shark1.png') }}" alt="image" srcset="">
                                        </div> -->
                                    </div>
                                    {{-- <div class="row row-sm-offset">
                                        <div class="col-md-6 multi-horizontal" data-for="reason">
                                            <div class="form-group">
                                                <label class="form-control-label mbr-fonts-style display-7" for="name-form1-2w">Reason</label>
                                                <input type="text" class="form-control" name="reason" data-form-field="reason"  id="name-form1-2w">
                                            </div>
                                        </div> --}}
                                       
                                       
                                    </div>
                                    
                                </form>
                            </div>
                        </div>                        


                    </div>
                </div>              
                            </div>   
        </div>
         
    </div>
                      
 
                       
                    </div>
                    @endif
                </div>
            </div> 
        </div>
    </section> 





                
                </div>
</main>
<script>
    $(document).ready(function() {
        // Initially hide the order number field
        $('#phone-form1-2w').closest('.col-md-6').hide();

        // Show or hide based on the dropdown value
        $('select[name="support"]').change(function() {
            if ($(this).val() === 'order-issue') {
                $('#phone-form1-2w').closest('.col-md-6').show();
            } else {
                $('#phone-form1-2w').closest('.col-md-6').hide();
            }
        });
    });
</script>
@endsection
<!-- Content Ends-->




