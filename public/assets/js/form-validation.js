/**
 * Created by Asif javaid on 01-Aug-18.
 */
 
// registration form validation start
$(document).ready(function () {
    
    // Update passcode validation rule dynamically
    // if (user_restriction.passcodeCheck == '') {
    //     $("#passcode").rules("remove");
    // }

    $(document).ready(function () {
        $("#chk_newsletter").on("change", function () {
            $(".cat-contan").toggle("slow");
        });
    });
    $(document).on("click", ".cat-select", function (e) {
        $(".catchek").prop('checked', 'checked');
    });
    $(document).on("click", ".cat-deselect", function () {
        $(".catchek").prop('checked', '');
    });
//forget password validation start

    $("#forget_form").validate({
        errorElement: "span",
        rules: {
            forgetemail: {required: true},
        },
        messages: {
            forgetemail: {required: "Email is required."},
        },
        errorPlacement: function (error, element) {
            error.appendTo(element.closest('.form-group'));
        },
        submitHandler: function (form) {
            form.submit();
        }
    });
//forget password validation end

// login validaton start
    jQuery.validator.addMethod("customPasscode", function (value, element) {
        // Check if user_restrict is true and passcode matches the environment variable
        if (user_restriction.passcodeCheck == '1' && value === user_passcode.passcode) {
            return true;
        }
        return false;
    }, "Please enter a valid Passcode.");

    $("#login_form").validate({
        errorElement: "span",
        rules: {
            email: {required: true},

            password: {required: true},

            passcode : {
                customPasscode: true
            },
        },
        messages: {
            email: {required: "Email is required."},
            password: {required: "Password  is required."},
            passcode: {
                customPasscode: "Please enter a valid Passcode."
            },
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

    $("#login_form_page_mobile").validate({
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
            form.submit();
        }
    });

    //order-refund
    $("#order_refund_cancel").validate({
        errorElement: "span",
        rules: {
            // "reason": {required: true},
            //  "reason_type": {required: true},
             "select_price[]": {
                required: true,
             },
            // password: {required: true},
        },
        messages: {
            // refund_reason: {required: "reason is required."},
            //  refund_reason_type: {required: "please select reson type."},
             "select_price[]": {
                required: "please select.",
             }
        },
        errorPlacement: function (error, element) {
            error.appendTo(element.closest('.form-group'));
        },
        submitHandler: function (form) {
            form.submit();
            swal("Success!", "Done.", "success");
        }
    });

// login validaton end

//user updater validation start
    jQuery.validator.addMethod("alphanumeric", function (value, element) {
        return this.optional(element) || /^[a-zA-Z0-9]+$/.test(value);
    }, "Only alphanumeric are allowed.");
    jQuery.validator.addMethod("lettersonly", function (e, r) {
        return this.optional(r) || /^[a-z ]+$/i.test(e)
    }, "Only letters are allowed."),
            jQuery.validator.addMethod("numbersonly", function (e, r) {
                return this.optional(r) || /^[0-9 +]+$/i.test(e)
            }, "Only numbers are allowed."),
            $("#user_account_buyer").validate({
        errorElement: "span",
        rules: {
            user_name: {
                required: true, minlength: 4, lettersonly: !0,
                maxlength: 25
            },
            user_country: {
                required: true
            },
            user_state: {required: true},
            physical_address: {required: true, maxlength: 500},
            user_city_id: {required: true},
            user_zip_code: {required: true, alphanumeric: !0, maxlength: 15},
            email: {required: true, email: true},
            about: {maxlength: 1000},
            user_avatar: {accept: "jpg,png,jpeg"},
            // email: {required: true},
        },
        messages: {
            user_name: {required: "Name is required."},
            user_country: {required: "Country is required."},
            user_state: {required: "State is required."},
            user_city_id: {required: "City is required."},
            physical_address: {required: "Street Address is required."},
            user_zip_code: {required: "Zip Code is required."},
            user_avatar: {accept: "Please upload valid image."},
            email: {
                required: "Email  is required.",
                remote: "<div class='clear'></div><div id='register_user_form'>This Email is already registered.</div></div>"
            },
        },
        errorPlacement: function (error, element) {
            error.appendTo(element.closest('.form-group'));
        },
        submitHandler: function (form) {
            form.submit();
        }
    });

    //user updater validation end

//password chnage validation start

    jQuery.validator.addMethod("lettersonly", function (e, r) {
        return this.optional(r) || /^[a-z ]+$/i.test(e)
    }, "Only letters are allowed."),

    $("#userInfo").validate({
        errorElement: "span",
        rules: {
            txtname: {required: true, minlength: 4, lettersonly: !0,
                maxlength: 25},
            email: {required: true, email: true,
                remote: "/buyer-email-exist"
            },
            txtpassword: {
                minlength: 6,
                maxlength: 25,
                required: true,
            },

            password: {minlength: 6,
                maxlength: 25,
                required: true,
            },
            password_confirmation: {equalTo: "#password",
            },
        },
        messages: {
            txtname: {required: "Name is required."},
            password: {required: "Password  is required."},
            txtpassword: {required: "Current password  is required."},
            password_confirmation: {required: "Confirm password  is required."},
            email: {required: "Email  is required.", remote: "<div class='clear'></div><div id='register_user_form'>This username is already registered.</div></div>"},
        },
        errorPlacement: function (error, element) {
            error.appendTo(element.closest('.form-group'));
        },
        submitHandler: function (form) {
            form.submit();
        }
    });

    // Contact-us Form Validation
    $.validator.addMethod("pattern", function(value, element, pattern) {
        if (this.optional(element)) {
          return true;
        }
        var regex = new RegExp(pattern);
        return regex.test(value);
      }, "Please enter a valid value.");

    $("#contactUserinfo").validate({
        errorElement: "span",
        rules: {
            first_name: {required: true, minlength: 4,maxlength: 25},
            email: {required: true, email: true},
            phone: {pattern: "^[0-9\\-\\+\\(\\)\\s]+$",},
            message: {required: true},
        },
        messages: {
            first_name: {required: "Name is required."},
            email: {required: "Email  is required."},
            phone: {pattern: "Please enter only numbers and special characters."},
            message: {required: "Message is equired."},
        },
        errorPlacement: function (error, element) {
            error.appendTo(element.closest('.form-group'));
        },
        submitHandler: function (form) {
            form.submit();
        }
    });

    // Contact-us Form Validation on Mobile

    $("#contactUserinfoMobile").validate({
        errorElement: "span",
        rules: {
            name: {required: true, minlength: 4,maxlength: 25},
            email: {required: true, email: true},
            phone: { pattern: "^[0-9\\-\\+\\(\\)\\s]+$",},
            message: {required: true},
        },
        messages: {
            name: {required: "Name is required."},
            email: {required: "Email  is required."},
            phone: {pattern: "Please enter only numbers and special characters."},
            message: {required: "Message is equired."},
        },
        errorPlacement: function (error, element) {
            error.appendTo(element.closest('.form-group'));
        },
        submitHandler: function (form) {
            form.submit();
        }
    });

});
//password chnage validation end








