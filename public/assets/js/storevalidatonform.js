/**
 * Created by Asif javaid on 08-Aug-18.
 */
$(document).ready(function () {

    // store create form validation start

    jQuery.validator.addMethod("lettersonly", function (e, r) {
        return this.optional(r) || /^[a-z ]+$/i.test(e)
    }, "Only letters are allowed."),
        jQuery.validator.addMethod("numbersonly", function (e, r) {
            return this.optional(r) || /^[0-9+]+$/i.test(e)
        }, "Only numbers are allowed."),
        jQuery.validator.addMethod("validaUrl", function (e, r) {
            return this.optional(r) || /^(?:(ftp|http|https):\/\/)?(?:[\w-]+\.)+[a-z]{2,6}$/.test(e)
        }, "Enter a valid Website url."),
        $("#store-user_account").validate({
            
            errorElement: "span",
            rules: {
                store_name: {
                    required: true, minlength: 4, lettersonly: !0,
                    maxlength: 25,
                    remote: "/store/available"
                },
                store_country: {
                    required: true
                },
                store_state: {required: true},
                contact_email: {email: true},
                store_city: {required: true, maxlength: 15},
                street_address: {required: true},
                zip_code: {required: true, maxlength: 5},
                store_fax_no: {maxlength: 15},
                store_phone: {maxlength: 15,numbersonly:!0},
               // store_image_id: {required:true},
                // about_store: {maxlength: 1000,required: true},
                // store_term_conditon: {required: true},
                // store_website: {validaUrl: !0},  
               

            },
            messages: {
              
                contact_email: {required: "Contact email is required."},
                store_country: {required: "Country is required."},
                store_state: {required: "Province is required."},
                store_city: {required: "City is required."},
                zip_code: {required: "Zip code is required."},
                street_address: {required: "Address is required."},
                // about_store: {required: "About store is required"},
                // store_term_conditon: {required: "Terms and conditions is required."},
               // store_image_id: {required: "please upload valid image."},
                store_name: {required: "Store name is required.",remote: "<div class='clear'></div><div id='register_user_form'>This store name is already registered.</div></div>"},
            },
            errorPlacement: function (error, element) {
                
                error.appendTo(element.closest('.form-group'));
            },
            submitHandler: function (form) {
                
                form.submit();
            }
        });
    // store create form validation end

});