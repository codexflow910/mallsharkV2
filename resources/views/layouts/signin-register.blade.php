<?php
//if(!getCurrentUserId()){?>

<script src="https://cdn.jsdelivr.net/npm/web3@latest/dist/web3.min.js"></script>
<script>
        window.user_restriction = {
        passcodeCheck: "{{ env('User_restrict') }}"
        };
        window.user_passcode = {
        passcode: "{{ env('Passcode') }}"
        };
    </script>
<?php //} ?>
<!-- Sign Up Section Starts -->
<!-- Modal -->
<div id="login-registerpopup" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
                    <button type="button" style="z-index: 5;" class="close close-button-register" data-dismiss="modal">×</button>

            <div class="signbx" id="login-portion">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">

                            <p class="text-center">Don't have an account? <a href="javascript:;" id="signup-btn-link">Sign Up</a></p>
                            <!-- Sign Up Box -->
                            <div class="signbxin radius">
                                <h3>Sign In</h3>

                                @if(Session::has('SuccessMessage'))
                                <div class="alert alert-danger" role="alert">{{ Session::get('SuccessMessage') }}</div>
                                @endif
                                <form id="login_form_page" method="POST" action="{{ URL::to('/login') }}">
                                    {{ csrf_field() }}
                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" class="form-control" id="email-login" name="email" placeholder="Enter Your Email">
                                        @if ($errors->has('email'))
                                        <div class="error">{{ $errors->first('email') }}</div>
                                        @endif
                                    </div>
                                    <div class="form-group eye-icon-user">
                                        <label for="pwd">Password:</label>
                                        <input type="password" class="form-control" id="passwor-login" name="password" placeholder="Enter Your Password">
                                        
                                        @if ($errors->has('password'))
                                        <div class="error">{{ $errors->first('password') }}</div>
                                        @endif
                                    </div>
                                    <?php  if(env('User_restrict') == 1){ ?>
                                        <div class="form-group">
                                        <label for="pwd">Passcode:</label>
                                            <input type="text" class="form-control mallshark_passscode" maxlength="4" name="passcode" id="passcode" placeholder="Please Enter Passcode" value="{{ old('passcode') }}">
                                        </div>
                                    <?php  } ?>
                                    <div class="form-check" style="display: none;">
                                        <label class="form-check-label">
                                            <input class="form-check-input" type="checkbox" name="chkrememberme"> Remember me
                                        </label>   &nbsp; &nbsp;| &nbsp; &nbsp; <a href="{{URL::to('/forget-password')}}">Forgot Password</a>
                                    </div>
                            
                                    <p style="margin-bottom: 28px;"><button type="submit" class="btn btn-primary signin-btn" data-location="{{url('/checkout')}}">Sign in</button></p>
                                    <!-- <a class="btn btn-primary btn-simple facebook" style="padding-left:10px; padding-right:10px" href="{{URL::to('/facebook-login')}}">
                                    <i class="fa fa-facebook" aria-hidden="true"></i> Connect With Facebook </a>
                                <a href="{{URL::to('/google-login')}}" class="btn btn-primary btn-simple gmail "  style="padding-left:10px; padding-right:10px"> <i class="fa fa-google" aria-hidden="true"></i> Connect With Google</a> -->
                                   
                                <div class="fav-login-section-form">
                                    <h4>Sign in with:</h4>

                                    <div class="fav-login-form-icons">
                                        <a href="{{URL::to('/google-login')}}" class="  gmail ">                   
                                            <img src="{{ URL::asset('assets/images/google-icon.PNG') }}" alt="">
                                            </a> 
                                            <a class=" " href="{{URL::to('/facebook-login')}}">
                                            {{-- <i class="fa fa-facebook" aria-hidden="true"></i>   &nbsp; &nbsp; --}}
                                            <img src="{{ URL::asset('assets/images/facebook-icon.PNG') }}" alt="">
                                             </a>       
                                    </div>
                                </div>
                                     
                                    {{-- <p style="margin-bottom:0px;margin-top:28px;">By signing in you are agreed with the <a href="{{URL::to('/terms');}}" class="bold-class">terms and conditions</a></p> --}}
                                </form>
                            </div>
                            <!-- Sign UP Box Ends -->
                        </div>
                    </div>
                </div>
            </div>


            <div class="signbx" id="singup-form">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <p class="text-center">Have an account? <a href="javascript:;" id="sigin-btn-link"> <b> Sign in </b></a></p>
                            <!-- Sign Up Box -->
                            <div class="signbxin border radius">
                                <h3>Sign Up</h3>
                                @if(Session::has('RegistersuccessMessage'))
                                <div class="alert alert-success alert-success-mbt" role="alert">
                                    {{ Session::get('RegistersuccessMessage') }}
                                    <script>
                                        $(document).ready(function () {

                                            // setTimeout(
                                            //         function ()
                                            //         {
                                            //             window.location.href = '/login';
                                            //         }, 2000);
                                        });

                                    </script>
                                </div>
                                @endif
                                <form id="register_form_popup_form" name="register_form" method="POST" action="{{Url::to('/signup')}}">
                                    {{ csrf_field() }}
                                    <div class="form-group">
                                        <label for="email">Full Name*</label>
                                        <input type="text" class="form-control" id="txtname" name="txtname" >

                                        @if ($errors->has('txtname'))
                                        <div class="error">{{ $errors->first('txtname') }}</div>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label for="pwd">Email *</label>
                                        <input type="text" class="form-control" id="email" name="email" >
                                        @if ($errors->has('email'))
                                        <div class="error">{{ $errors->first('email') }}</div>
                                        @endif
                                    </div>
                                    <!-- <div class="form-group">
                                        <label for="email">Physical Address *</label>
                                        <textarea class="form-control" id="physical_address" name="physical_address" required></textarea>

                                        @if ($errors->has('physical_address'))
                                        <div class="error">{{ $errors->first('physical_address') }}</div>
                                        @endif
                                    </div> -->
                                    <div class="form-group">
                                        <label for="pwd">Contact Number</label>
                                        <input type="text" class="form-control" id="contact_no" name="contact_no"  >
                                        @if ($errors->has('contact_no'))
                                        <div class="error">{{ $errors->first('contact_no') }}</div>
                                        @endif
                                    </div>
                                    <!-- <div class="form-group">
                                        <label for="pwd">Wallet Address *</label>
                                        <input type="text" class="form-control" id="wallet_address" name="wallet_address" >
                                        @if ($errors->has('wallet_address'))
                                        <div class="error">{{ $errors->first('wallet_address') }}</div>
                                        @endif
                                    </div> -->
                                    <div class="form-group eye-icon-user">
                                        <label for="pwd">Password *</label>
                                        <input type="password" class="form-control" id="password" name="password">
                                      
                                        @if ($errors->has('password'))
                                        <div class="error">{{ $errors->first('password') }}</div>
                                        @endif
                                    </div>
                                    <div class="form-group eye-icon-user">
                                        <label for="pwd">Re-type Password *</label>
                                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                                        

                                        @if ($errors->has('password_confirmation'))
                                        <div class="error">{{ $errors->first('password_confirmation') }}</div>
                                        @endif
                                    </div>
                                    <?php
                                    if(env('User_restrict') == 1){ ?>
                                        <div class="form-group">
                                        <label for="pwd">Passcode:</label>
                                            <input type="text" class="form-control mallshark_passscode" maxlength="4" name="passcode" id="passcode" placeholder="Please Enter Passcode" value="{{ old('passcode') }}">
                                        </div>
                                    <?php  } ?>
                                    <div class="form-check" style="margin-bottom: 0px;">
                                        <label class="form-check-label container-check" style="display: none;">
                                            <input class="form-check-input" type="checkbox" id="chk_newsletter" value="1" name="chk_newsletter" checked="">
                                            <span class="checkmark"></span>
                                            <p><small>Receive our weekly newsletter and other occasional updates</small></p>

                                        </label>   
                                        <p style="margin-bottom: 28px;"><button type="submit" class="btn btn-primary" style="padding-left:10px;padding-right:10px" data-location="{{url('/checkout')}}"> Create Your MallShark account</button></p>
                                        <!-- <a class="btn btn-primary btn-simple facebook" style="padding-left:10px; padding-right:10px" href="{{URL::to('/facebook-login')}}">
                                    <i class="fa fa-facebook" aria-hidden="true"></i> Connect With Facebook </a>
                                <a href="{{URL::to('/google-login')}}" class="btn btn-primary btn-simple gmail "  style="padding-left:10px; padding-right:10px"> <i class="fa fa-google" aria-hidden="true"></i> Connect With Google</a>
                                        
                                        <p style="margin-top:10px">By signing up, you agree to our <a href="{{URL::to('/terms');}}" class="bold-class">terms and conditions</a> of use, <a href="{{URL::to('/comingsoon');}}"  class="bold-class"> privacy policy</a>, and <a href="{{URL::to('/terms');}}"  class="bold-class"> cookie policy</a>.</p>
                                <a href="{{URL::to('/google-login')}}" class="btn btn-primary btn-simple gmail "  style="padding-left:10px; padding-right:10px"> <i class="fa fa-google" aria-hidden="true"></i> Connect With Google</a> -->

                                <div class="fav-login-section-form">
                                <h4>Connect with:</h4>    

                                <div class="fav-login-form-icons">
                                    <a href="{{URL::to('/google-login')}}" class="  gmail ">                   
                                        <img src="{{ URL::asset('assets/images/google-icon.PNG') }}" alt="">
                                        </a> 
                                        <a class=" " href="{{URL::to('/facebook-login')}}">
                                        {{-- <i class="fa fa-facebook" aria-hidden="true"></i>   &nbsp; &nbsp; --}}
                                        <img src="{{ URL::asset('assets/images/facebook-icon.PNG') }}" alt="">
                                         </a>    
                                </div>
                                </div>  
                                    


                                        <p style="margin-top:28px;margin-bottom:0px;">By signing up, you agree to our <a href="{{URL::to('/terms');}}" class="bold-class" style="color:#073ba5;">terms and conditions</a> of use, <a style="color:#073ba5;font-weight: bold;" href="{{URL::to('/privacy-policy');}}"> privacy policy</a>, and cookie policy.</p>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
             
                $(document).ready(function () {
                    jQuery.validator.addMethod("lettersonly", function (e, r) {
                        return this.optional(r) || /^[a-z ]+$/i.test(e)
                    }, "Only letters are allowed."),
                    jQuery.validator.addMethod("customPasscode", function (value, element) {
                        // Check if user_restrict is true and passcode matches the environment variable
                        if (user_restriction.passcodeCheck == '1' && value === user_passcode.passcode) {
                            return true;
                        }
                        return false;
                    }, "Please enter a valid Passcode.");

                    $("#register_form_popup_form").validate({
                        errorElement: "span",
                        rules: {
                            txtname: {
                                required: true, minlength: 4, lettersonly: !0,
                                maxlength: 25
                            },
                            email: {
                                required: true, email: true,
                                remote: "/email-exist"
                            },
                            password: {
                                required: true, minlength: 6,
                                maxlength: 25
                            },
                            passcode : {customPasscode: true},
                            // physical_address: {
                            //     required: true,
                            //     maxlength: 500
                            // },
                            // wallet_address: {
                                
                            //     custom: true
                            // },
                            // 'categories[]': {
                            //     required: "#chk_newsletter:checked",
                            // },

                            // contact_no: { maxlength: 20, numbersonly: !0},
                            password_confirmation: {required: true, equalTo: "#password"},
                        },
                        messages: {
                            txtname: {required: "Name is required."},
                            password: {required: "Password  is required."},
                            password_confirmation: {required: "Confirm password  is required."},
                            //physical_address: {required: "Physical address  is required."},
                            // contact_no: {required: "Contact number is required."},
                            // 'categories[]': {required: "Please select at least on category."},
                            email: {
                                required: "Email  is required.",
                                remote: "<div class='clear'></div><div id='register_user_form'>This username is already registered.</div></div>"
                            },
                            passcode: {customPasscode: "Please enter a valid Passcode." },
                            // wallet_address: {
                        
                            //     custom: "Please enter a valid wallet address."
                            // },
                        },
                        errorPlacement: function (error, element) {
                            error.appendTo(element.closest('.form-group'));
                        },
                        submitHandler: function (form) {
                            form.submit();
                        }
                    }); 
                    // if (user_restriction.passcodeCheck == '') {
                    //     $("#passcode").rules("remove");
                    // }    

                });

            </script>




        </div>
    </div>
</div>
@if(isset($_GET['active_tabe']) && $_GET['active_tabe'])=='faq')
<script>

    jQuery(document).ready(function () {

        jQuery('.faqtab').click();
        jQuery('.aska-question').click();

       
        setTimeout(
  function() 
  {
     jQuery('html, body').animate({
            scrollTop: jQuery(".product-tabs").offset().top
        }, 500);
        
        
  }, 500);
        


    });
</script>

@endif
<script>
    var redirect;
    $(document).ready(function () {
        $('#signup-btn-link').click(function () {
            $('#singup-form').show();
            $('#login-portion').hide();
        });

        $('.ask-a-question  .get-p-url').click(function () {
            redirect = jQuery(this).attr('data-location') + '?active_tabe=faq';

        });
        $('#sigin-btn-link').click(function () {
            redirect = jQuery(this).attr('data-location');
            $('#singup-form').hide();
            $('#login-portion').show();
        });
        $('.checkout-item').click(function () {
            redirect = jQuery(this).attr('data-location');


        });
        $(".fav-login").on("click", function () {
            redirect = jQuery(this).attr('data-location');
        });
        $(".checkout-item").on("click", function () {
            redirect = jQuery(this).attr('data-location');
           
        });
        $(".favlogin").on("click", function () {
            redirect = jQuery(this).attr('data-location');
        });
        $(".cartlogin").on("click", function () {
            redirect = jQuery(this).attr('data-location');
            
        });
     
        jQuery.validator.addMethod("customPasscode", function (value, element) {
        // Check if user_restrict is true and passcode matches the environment variable
        if (user_restriction.passcodeCheck == '1' && value === user_passcode.passcode) {
            return true;
        }
        return false;
         }, "Please enter a valid Passcode.");

        $("#login_form_page").validate({
            errorElement: "span",
            rules: {
                email: {required: true},
                password: {required: true},
                passcode : {customPasscode: true},
            },
            messages: {
                email: {required: "Email is required."},
                password: {required: "Password  is required."},
                passcode: {customPasscode: "Please enter a valid Passcode." },
            },
            errorPlacement: function (error, element) {
                error.appendTo(element.closest('.form-group'));
            },
            submitHandler: function (form) {
                jQuery('.info-message').remove();
                login_email = jQuery('#email-login').val();
                login_pass = jQuery('#passwor-login').val();

                jQuery('.uploadImages').css('z-index', '9999');
                jQuery('.uploadImages').show();
                jQuery.ajax({
                    url: '/login-ajax',
                    data: 'email=' + login_email + '&password=' + login_pass + '&action=ajax_login',
                    type: 'POST',
                    success: function (respose) {

                        var obj = JSON.parse(respose);
                        if (obj.error != '' && obj.error != undefined && obj.error != 'undefined') {
                            jQuery('.uploadImages').hide();
                            jQuery('.signbxin h3').append('<div class="alert alert-danger info-message">' + obj.error + '</div>');
                        }
                        if (obj.success != '' && obj.success != 'undefined' && obj.success != undefined) {
                            jQuery('.signbxin h3').append('<div class="alert alert-success info-message">' + obj.success + '</div>');
                            jQuery('.uploadImages').hide();
                            
                            window.location.assign(redirect);
                        }
                        //jQuery('.uploadImages').hide();
                    }

                });
            }
        });
    //     if (user_restriction.passcodeCheck == '') {
    //     $("#passcode").rules("remove");
    // }
    });

</script>