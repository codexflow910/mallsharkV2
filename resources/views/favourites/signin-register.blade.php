
<script src="{{ URL::asset('assets/js/jquery.validate.js',env('HTTPS_STATUS')) }}"></script>
<!-- Sign Up Section Starts -->
<!-- Modal -->
<div id="loginpopup" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="signbx" id="login-portion-fav">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">

                            <p class="text-center">Don't have an account? <a href="javascript:;" id="signup-btn-link">Sign Up</a></p>
                            <!-- Sign Up Box -->
                            <div class="signbxin border radius">
                                <h3>Sign In</h3>

                                @if(Session::has('SuccessMessage'))
                                <div class="alert alert-danger" role="alert">{{ Session::get('SuccessMessage') }}</div>
                                @endif
                                <form id="fav_login_form" method="POST" action="{{ URL::to('/login') }}">
                                    {{ csrf_field() }}
                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" class="form-control" id="email-login" name="email">
                                        @if ($errors->has('email'))
                                        <div class="error">{{ $errors->first('email') }}</div>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label for="pwd">Password:</label>
                                        <input type="password" class="form-control" id="fav" name="password">
                                        @if ($errors->has('password'))
                                        <div class="error">{{ $errors->first('password') }}</div>
                                        @endif
                                    </div>
                                    <div class="form-check" style="display: none;">
                                        <label class="form-check-label">
                                            <input class="form-check-input" type="checkbox" name="chkrememberme"> Remember me
                                        </label>   &nbsp; &nbsp;| &nbsp; &nbsp; <a href="{{URL::to('/forget-password')}}">Forgot Password</a>
                                    </div>
                                    <p><button type="submit" class="btn btn-primary tn-primary-background">Sign in</button></p>

                                    <p>By signing in you are agreed with the <a href="{{URL::to('/terms');}}" class="bold-class">terms and conditions</a></p>
                                </form>
                            </div>
                            <!-- Sign UP Box Ends -->
                        </div>
                    </div>
                </div>
            </div>


            <div class="signbx" id="singup-form-fav">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <p class="text-center">Have and account? <a href="javascript:;" id="sigin-btn-link">Sign in</a></p>
                            <!-- Sign Up Box -->
                            <div class="signbxin border radius">
                                <h3>Sign Up</h3>
                                @if(Session::has('RegistersuccessMessage'))
                                <div class="alert alert-success alert-success-mbt" role="alert">
                                    {{ Session::get('RegistersuccessMessage') }}
                                    <script>
                                    // $(document).ready(function () {

                                    //     setTimeout(
                                    //             function ()
                                    //             {
                                    //                 window.location.href = '/login';
                                    //             }, 2000);
                                    // });

                                    </script>
                                </div>
                                @endif
                                <form id="register_form" name="register_form" method="POST" action="{{Url::to('/signup')}}">
                                    {{ csrf_field() }}
                                    <div class="form-group">
                                        <label for="email">Name</label>
                                        <input type="text" class="form-control" id="txtname" name="txtname">
                                        <input type="hidden" class="form-control" id="url" name="url" value="{{URL::to('search-results')}}">

                                        @if ($errors->has('txtname'))
                                        <div class="error">{{ $errors->first('txtname') }}</div>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label for="pwd">Email:</label>
                                        <input type="text" class="form-control" id="email" name="email">
                                        @if ($errors->has('email'))
                                        <div class="error">{{ $errors->first('email') }}</div>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label for="pwd">Password:</label>
                                        <input type="password" class="form-control" id="password" name="password">
                                        @if ($errors->has('password'))
                                        <div class="error">{{ $errors->first('password') }}</div>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label for="pwd">Re-type Password:</label>
                                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                                        @if ($errors->has('password_confirmation'))
                                        <div class="error">{{ $errors->first('password_confirmation') }}</div>
                                        @endif
                                    </div>
                                    <div class="form-check" >
                                        <label class="form-check-label container-check" style="display: none;">
                                            <input class="form-check-input" type="checkbox" id="chk_newsletter" value="1" name="chk_newsletter" checked="">
                                            <span class="checkmark"></span>
                                            <p><small>Receive our weekly newsletter and other occasional updates</small></p>

                                        </label>   
                                        <p ><button type="submit" class="btn btn-primary">Submit</button></p>
                                        <p style="display: none;">
                                            <a class="btn btn-primary btn-simple" href="">
                                                <i class="fa fa-facebook" aria-hidden="true"></i> Connect With Facebook </a> &nbsp; &nbsp;
                                            <button type="button" class="btn btn-primary btn-simple "> <i class="fa fa-linkedin" aria-hidden="true"></i> Connect With Linked In</button>
                                        </p>
                                        <p>By signing up, you agree to our <a href="{{URL::to('/terms');}}" class="bold-class" style="color:#073ba5!important;">terms and conditions</a> of use, <a href="{{URL::to('/privacy-policy');}}"  class="bold-class" style="color:#073ba5!important;"> privacy policy</a>, and <a href="{{URL::to('/terms');}}"  class="bold-class"> cookie policy</a>.</p>
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
                            $("#register_form").validate({
                        errorElement: "span",
                        rules: {
                            txtname: {required: true, minlength: 4, lettersonly: !0,
                                maxlength: 25},
                            email: {required: true, email: true,
                                remote: "/email-exist"
                            },
                            password: {required: true, minlength: 6,
                                maxlength: 25},
                            password_confirmation: {required: true, equalTo: "#password"},
                        },
                        messages: {
                            txtname: {required: "Name is required."},
                            password: {required: "Password  is required."},
                            password_confirmation: {required: "Confirm password  is required."},
                            email: {required: "Email  is required.", remote: "<div class='clear'></div><div id='register_user_form'>This username is already registered.</div></div>"},
                        },
                        errorPlacement: function (error, element) {
                            error.appendTo(element.closest('.form-group'));
                        },
                        submitHandler: function (form) {
                            jQuery('.info-message').remove();
                            login_email = jQuery('#email').val();
                            login_pass = jQuery('#password').val();
                            txtname = jQuery('#txtname').val();
                            password_confirmation = jQuery('#password_confirmation').val();

                            jQuery('.uploadImages').css('z-index', '9999');
                            jQuery('.uploadImages').show();
                            jQuery.ajax({
                                url: '/register-ajax',
                                data: 'email=' + login_email + '&password=' + login_pass + '&txtname=' + txtname + '&password_confirmation=' + password_confirmation + '&action=register_ajax',
                                type: 'POST',
                                success: function (respose) {

                                    if (respose == 'error') {
                                        jQuery('.uploadImages').hide();
                                        jQuery('.signbxin h3').append('<div class="alert alert-danger info-message">The signup was unsuccessful, please contact to site admin</div>');
                                    }
                                    if (respose == 'success') {
                                        jQuery('.signbxin h3').append('<div class="alert alert-success info-message">The signup was successful</div>');
                                        jQuery('.uploadImages').hide();
                                        redirect=$("#url").val();
                                        window.location.assign(redirect);
                                    }
                                    //jQuery('.uploadImages').hide();
                                }

                            });
                        }
                    });
                });

            </script>




        </div>
    </div>
</div>
<script>
    var redirect;
    $(document).ready(function () {
        $('#signup-btn-link').click(function () {
            $('#singup-form-fav').show();
            $('#login-portion-fav').hide();
        });

//        $('#sigin-btn-link').click(function () {
//            redirect = jQuery(this).attr('data-location');
//            $('#singup-form').hide();
//            $('#login-portion').show();
//        });
        $('.add-fav').click(function () {
            redirect = jQuery(this).attr('data-location');
            $("#url").val(redirect);
        });

        $("#fav_login_form").validate({
            errorElement: "span",
            rules: {
                email: {required: true},
                password: {required: true},
            },
            messages: {
                email: {required: "Email is required."},
                password: {required: "Password  is required."},
            },
            errorPlacement: function (error, element) {
                error.appendTo(element.closest('.form-group'));
            },
            submitHandler: function (form) {
                jQuery('.info-message').remove();
                login_email = jQuery('#email-login').val();
                login_pass = jQuery('#fav-passwor-login').val();

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
                              redirect=$("#url").val();
                            window.location.assign(redirect);
                        }
                        //jQuery('.uploadImages').hide();
                    }

                });
            }
        });
    });

</script>