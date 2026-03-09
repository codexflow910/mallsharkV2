@extends('layouts.frontend-main')
@section('content')
@section('title', 'Request Deletion')
@section('description', 'Het is al geruime tijd een bekend gegeven dat een lezer, tijdens het bekijken van de layout van een pagina')
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

                <div class="col-sm-12">
                    <div class="pos-rel mt-0 pt-30">

                    <div class="container">
                        
                        @if(Session::has('success'))
                        <?php $successMessage = Session::get('success') ?>
                            <script>
                            toastr.options = {
                                closeButton: true,
                                tapToDismiss: true,
                                positionClass: 'toast-top-right',
                                toastClass: 'toast-icon custom-toast-success',
                                progressBar: true,
                            };
                            toastr.success('<?php echo  $successMessage ?> ');
                            </script>
                        @endif
                        
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
                        
                    <div class="contact_heading">
                        <h1>Contact Us</h1>
                        <h4>How can we help you?</h4>
                    </div>
                        <!-- <p>
                            Het is al geruime tijd een bekend gegeven dat een lezer, tijdens het bekijken van de layout van een pagina, afgeleid wordt door de tekstuele inhoud. Het belangrijke punt van het gebruik van Lorem Ipsum is dat het uit een min of meer normale verdeling van letters bestaat, in tegenstelling tot "Hier uw tekst, hier uw tekst" wat het tot min of meer leesbaar nederlands maakt. Veel desktop publishing pakketten en web pagina editors gebruiken tegenwoordig Lorem Ipsum als hun standaard model tekst, en een zoekopdracht naar "lorem ipsum" ontsluit veel websites die nog in aanbouw zijn. 

                        </p> -->


                        <div class="row justify-content-center mt-30">


                            <div class="col-lg-4 d-none">
                                <div class="contact-contact">

                                    <h2 class="mb-30">GET IN TOUCH</h2>

                                    <!-- <ul class="contact-details">
                                        <li><span>25th Lake Avenue</span></li>
                                        <li><span> Advent Road,</span></li>
                                        <li><span>+1 4446 4460054</span></li>
                                        <li><span>{{env('SUPPORT_EMAIL')}}</span></li>
                                    </ul> -->

                                </div>

                            </div>                            

                            <div class="media-container-column col-lg-8" data-form-type="formoid">
                                <div data-form-alert="" hidden="">
                                    Thanks for filling out the form!
                                </div>

                                <form id="contactUserinfo" class="contact-us-form mbr-form" action="{{URL::to('/contact-us')}}" method="post" data-form-title="Mobirise Form">
                                    @csrf
                                    
                                    <div class="row row-sm-offset">
                                        <div class="shark_image">
                                            <img src="{{ URL::asset('assets/images/Shark22.png') }}" alt="image" srcset="">
                                        </div>
                                        <div class="col-md-6 multi-horizontal" data-for="name">
                                            <div class="form-group">
                                                <label class="form-control-label mbr-fonts-style display-7" for="name-form1-2w">Name*</label>
                                                <input type="text" class="form-control" placeholder="Enter your Name" name="name" data-form-field="name"  id="name-form1-2w">
                                            </div>
                                        </div>
                                        <div class="col-md-6 multi-horizontal" data-for="contact_no">
                                            <div class="form-group">
                                                <label class="form-control-label mbr-fonts-style display-7" for="contact-form1-2w">Contact No</label>
                                                <input type="tel" class="form-control" placeholder="Enter your contact number" name="contact_no" data-form-field="contact_no"  id="contact-form1-2w">
                                            </div>
                                        </div>
                                        <div class="col-md-12 multi-horizontal" data-for="email">
                                            <div class="form-group">
                                                <label class="form-control-label mbr-fonts-style display-7" for="email-form1-2w">Email*</label>
                                                <input type="email" class="form-control" placeholder="Enter your email" name="email" data-form-field="Email" value="<?php if (isset($_GET['email'])) { echo $_GET['email']; } ?>" id="email-form1-2w">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6 multi-horizontal" data-for="password">
                                            <div class="form-group">
                                                <label class="form-control-label mbr-fonts-style display-7" for="password-form1-2w">password*</label>
                                                <input type="password" class="form-control" placeholder="Enter your password" name="password" data-form-field="password"  id="password-form1-2w">
                                            </div>
                                        </div>
                                        <div class="col-md-6 multi-horizontal" data-for="confirm_password">
                                            <div class="form-group">
                                                <label class="form-control-label mbr-fonts-style display-7" for="confirm_password-form1-2w">Confirm Password*</label>
                                                <input type="password" class="form-control" placeholder="Confirm your password" name="confirm_password" data-form-field="confirm_password"  id="confirm_password-form1-2w">
                                            </div>
                                        </div>
                                        <div class="form-group col-md-12" data-for="message">
                                            <label class="form-control-label mbr-fonts-style display-7" for="message-form1-2w">Message*</label>
                                            <textarea type="text" class="form-control" placeholder="Tell us more." name="message" rows="4" data-form-field="Message" id="message-form1-2w" maxlength="500"></textarea>
                                        </div>
                                        <div class="form-check multi-horizontal" data-for="order_check">
                                            <input class="form-check-input" type="checkbox" name="order_check" data-form-field="order_check" id="order_check-form1-2w">
                                            <label class="form-check-label mbr-fonts-style" for="order_check-form1-2w">
                                                I agree to the terms and conditions
                                            </label>
                                        </div>
                                        <div class="form-check multi-horizontal">
                                            <input class="form-check-input" type="checkbox" name="order_check" id="order_check-form1-2w">
                                            <label class="form-check-label mbr-fonts-style display-7" for="order_check-form1-2w">
                                                I agree to the terms and conditions
                                            </label>
                                        </div>
                                        
                                        <div class="col-md-12 text-center">
                                            <span class="input-group-btn ">
                                                <button href="" type="submit" class="btn btn-primary white-text btn-form display-4">Send</button>
                                            </span>
                                        </div>

                                        <div class="bottom-contact-image d-none">
                                                <img src="{{ URL::asset('assets/images/Shark1.png') }}" alt="image" srcset="">
                                        </div>
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
</main>
@endsection
<!-- Content Ends-->




