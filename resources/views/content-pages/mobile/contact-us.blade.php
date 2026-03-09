@extends('layouts.mobile-main')
@section('content')
@section('title', 'Contact Us')
@section('description', 'Mallshark')
@section('google-analytics')
@include('layouts.google-analytics')
@stop
<main>
<script src="{{ URL::asset('assets/js/jquery.validate.js'),env('HTTPS_STATUS') }}"></script>
<script src="{{ URL::asset('assets/js/form-validation.js',env('HTTPS_STATUS')) }}"></script>

    <style>
        @media only screen and (min-width : 767px) and (max-width : 10000px) {}
    </style>

    <div class="col-sm-12">
        <div class="pos-rel mt-0 pt-30">
            <div class="container">
                @if(Session::has('success'))
                <div class="alert alert-success">
                  {{ Session::get('success')}}
                </div>
                @endif
                @if (count($errors) > 0)
                        <div class="alert alert-danger">
                        <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                        </ul>
                        </div>
                        @endif
                <h1>Contact Us</h1></br>
                <!-- <p class="contact-text">
                    Het is al geruime tijd een bekend gegeven dat een lezer, tijdens het bekijken van de layout van een pagina, afgeleid wordt door de tekstuele inhoud. Het belangrijke punt van het gebruik van Lorem Ipsum is dat het uit een min of meer normale verdeling van letters bestaat, in tegenstelling tot "Hier uw tekst, hier uw tekst" wat het tot min of meer leesbaar nederlands maakt. Veel desktop publishing pakketten en web pagina editors gebruiken tegenwoordig Lorem Ipsum als hun standaard model tekst, en een zoekopdracht naar "lorem ipsum" ontsluit veel websites die nog in aanbouw zijn.

                </p> -->


                <div class="row justify-content-center mt-30">


                    <div class="col-lg-4">
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

                        <form  id="contactUserinfoMobile" class="mbr-form" action="{{URL::to('/contact-us')}}" method="post" data-form-title="Mobirise Form">
                            @csrf
                            <div class="row row-sm-offset">
                                <div class="col-md-4 multi-horizontal" data-for="name">
                                    <div class="form-group">
                                        <label class="form-control-label mbr-fonts-style display-7" for="name-form1-2w">Name*</label>
                                        <input type="text" class="form-control" name="name" data-form-field="Name" required="" id="name-form1-2w">
                                    </div>
                                </div>
                                <div class="col-md-4 multi-horizontal" data-for="email">
                                    <div class="form-group">
                                        <label class="form-control-label mbr-fonts-style display-7" for="email-form1-2w">Email*</label>
                                        <input type="email" class="form-control" name="email" data-form-field="Email" required="" id="email-form1-2w">
                                    </div>
                                </div>
                                <div class="col-md-4 multi-horizontal" data-for="phone">
                                    <div class="form-group">
                                        <label class="form-control-label mbr-fonts-style display-7" for="phone-form1-2w">Phone</label>
                                        <input type="tel" class="form-control" name="phone" data-form-field="Phone" id="phone-form1-2w" maxlength="20">
                                    </div>
                                </div>
                            </div>
                            <div class="row row-sm-offset">
                                        <div class="col-md-6 multi-horizontal" data-for="reason">
                                            <div class="form-group">
                                                <label class="form-control-label mbr-fonts-style display-7" for="name-form1-2w">Reason</label>
                                                <input type="text" class="form-control" name="reason" data-form-field="reason"  id="name-form1-2w">
                                            </div>
                                        </div>
                                        <div class="col-md-6 multi-horizontal" data-for="name">
                                            <div class="form-group">
                                                <label class="form-control-label mbr-fonts-style display-7" for="name-form1-2w">Support</label>
                                                <select  class="form-control form-select" name="support">
                                                    <option value="technical-issue">Technical Issue</option>
                                                    <option value="order-issue">Order Issue</option>
                                                    <option value="customer-support">Customer Support</option>
                                                </select>
                                               
                                            </div>
                                        </div>
                                       
                                    </div>
                            <div class="form-group" data-for="message">
                                <label class="form-control-label mbr-fonts-style display-7" for="message-form1-2w">Message*</label>
                                <textarea type="text" class="form-control" name="message" rows="7" data-form-field="Message" id="message-form1-2w"></textarea>
                            </div>

                            <span class="input-group-btn">
                                <button href="" type="submit" class="btn btn-primary white-text btn-form display-4 contact-submit-btn">Submit</button>
                            </span>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>
@endsection
<!-- Content Ends-->