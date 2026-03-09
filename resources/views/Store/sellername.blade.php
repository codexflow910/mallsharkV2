@extends('layouts.dashboard-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Start Selling')
@section('description', 'Explore creative projects and help bring bold new ideas into the world with the Mallshark app')

<style>
    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        font-weight: bold;
    }



    .terms {
        display: flex;
        align-items: center;
       
    }
    .terms-text-center{
        border: 1px solid #0510fa;
        padding: 5px;
        border-radius: 25px;
        margin-top: 10px;
        margin-bottom: 10px;
    }
    .terms-text {
        max-height: 350px;
    overflow-y: auto;
    padding: 10px;
    
    
    background-color: #ffffff;
    margin-bottom: 20px;
    }

    .terms-text p {
        margin: 0;
    }

    a {
        color: #007BFF;
        text-decoration: none;
    }

    a:hover {
        text-decoration: underline;
    }

    /* Custom checkbox styles */
    .checkbox-container {
        position: relative;
        padding-left: 30px;
        cursor: pointer;
        user-select: none;
        color: #555;
    }

    .checkbox-container input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
    }

    .checkmark {
        position: absolute;
        top: 0;
        left: 0;
        height: 20px;
        width: 20px;
        border: 2px solid #555;
        border-radius: 4px;
    }

    .checkbox-container:hover .checkmark {
        border: 2px solid #007BFF;
    }

    .checkbox-container input:checked~.checkmark {
        border: 2px solid #007BFF;
    }

    .checkmark:after {
        content: "";
        position: absolute;
        display: none;
    }

    .checkbox-container input:checked~.checkmark:after {
        display: block;
    }

    .checkbox-container .checkmark:after {
        left: 7px;
        top: 3px;
        width: 5px;
        height: 10px;
        border: solid #007BFF;
        border-width: 0 2px 2px 0;
        transform: rotate(45deg);
    }
     #store_term_conditon-error {
        margin-left: 20px;
        margin-bottom: 10px;
    }
    h1 {
            text-align: center;
        }

        p {
            margin-bottom: 15px;
        }

        /* Add a light gray background to the sections */
        .section {
            /* background-color: #f5f5f5; */
            padding: 10px;
            border-radius: 5px;
        }

        /* Increase the font size for section headings */
        .section h3 {
            font-size: 1rem;
        }

        /* Bold and underline the section numbers */
        .section-number {
            font-weight: bold;
            /* text-decoration: underline; */
        }

        /* Add indentation to the content of each section */
        .section-content {
            padding-left: 20px;
            font-size: 14px;
        }

        /* Style the "I Agree" button */
        .agree-button {
            display: block;
            width: 100%;
            max-width: 200px;
            margin: 0 auto;
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            text-align: center;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
</style>

<div class="seller-name-page">
    <div class="container">
        <?php
        if ($storeData) {

            $store_id = $storeData['store_id'];
            $productcount =  getStoreTotalProducts($store_id);
            if (!$storeData && $storeData['stripe'] != 1 && $productcount < 0) {

        ?>
                <div class="progress">
                    <div class="progress-bar w-25" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
        <?php }
        } ?>
      
        @if(Session::has('SuccessMessage'))
        <div class="alert alert-error alert-error-mbt" id="success_message" role="alert">
            {{ Session::get('ErrorMessage') }}
            <script>
                setTimeout(function() {
                    jQuery("#success_message").fadeOut('slow')
                }, 3000);
            </script>
        </div>
        @endif
        <?php
        $actionUrl = '';
        if (isset($storeData['store_id'])) {
            $actionUrl = 'edit/store-name';
        } else {
            $actionUrl = 'store-name';
        }

        ?>

        <div class="name-your-shop-wrapper mt-4">
            <h2>Name your shop <div class="tooltip">
                    <i class="fa fa-info-circle"></i>
                    <span class="tooltiptext">You can edit your store name in your settings later.</span>
                </div>
            </h2>

            <form method="post" action="{{URL::to($actionUrl)}}" id="storeNameCreate">
                @csrf
                <div class="form-group">
                    <input type="text" class="form-control" name="store_name" id="store_name" aria-describedby="emailHelp" value="<?php echo (isset($storeData['store_name'])) ? $storeData['store_name'] : ""; ?>" placeholder="Enter your shop name">
                </div>
                <?php  if(isset($_GET['campaign'])){ ?>
                    <input type="hidden"  name="campaign" id="campaign" value="{{$_GET['campaign']}}">
                <?php } ?>

                <?php  if(isset($_GET['referred'])){ ?>
                    <input type="hidden"  name="referred" id="referred" value="{{$_GET['referred']}}">
                <?php } ?>
               


                <div class="row">
                    <div class="col-md-6">
                        <ul>
                            <li>Between 4-70 characters </li>
                            <li>No special characters ,spaces, or accented letters.</li>
                        </ul>
                    </div>
                    </div>


                 
                   <div class="terms-text-center">
                    <div class="terms-text mt-3">
                        {{-- <h1>Terms and Conditions</h1> --}}
                        <div class="section">
                            <h3><span class="section-number">1. </span> Acceptance of Terms</h3>
                            <div class="section-content">
                                By applying to become a seller on our Mallshark E-commerce Store, you acknowledge that you have read, understood, and agree to be bound by the terms and conditions set forth in this Agreement. If you do not agree with any of these terms, please do not proceed with the registration process.
                            </div>
                        </div>
                         <div class="section">
                            <h3><span class="section-number">2. </span> Seller Registration</h3>
                            <div class="section-content">
                                <b>2.1</b> Application Process: To become a seller on our platform, you must complete the registration process, providing accurate and up-to-date information. We reserve the right to approve or reject any seller application at our sole discretion. <br />
                                <b>2.2</b> Account Security: You are responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account. You agree to notify us immediately of any unauthorized use or security breach.<br />
                            </div>
                        </div>

                         <div class="section">
                            <h3><span class="section-number">3. </span>Seller Responsibilities</h3>
                            <div class="section-content">
                                <b>3.1</b> Product Listings: As a seller, you are responsible for creating and maintaining accurate and comprehensive product listings, including product descriptions, images, pricing, and inventory levels.<br />
                                    <b>3.2</b> Order Fulfillment: You agree to fulfill all orders received through the platform promptly and ensure that products are delivered to customers in a timely manner.<br />
                                        <b> 3.3</b> Product Quality: You warrant that all products you offer for sale on our platform are of high quality, free from defects, and comply with all applicable laws and regulations.<br />
                                            <b>3.4</b> Customer Service: You are responsible for providing excellent customer service to buyers, addressing inquiries, and handling customer disputes or returns in a professional and timely manner.<br />
                            </div>
                        </div>

                         <div class="section">
                            <h3><span class="section-number">4. </span>Product Listing Guidelines</h3>
                            <div class="section-content">
                                <b> 4.1</b> Prohibited Products: You shall not list or sell any products that are illegal, counterfeit, infringe on intellectual property rights, or violate any laws or regulations.<br />
                                    <b>4.2</b> Restricted Products: Certain products may be subject to additional restrictions and regulations. You must comply with all applicable laws regarding the sale of such products.<br />
                            </div>
                        </div>

                         <div class="section">
                            <h3><span class="section-number">5. </span>Fees and Payments</h3>
                            <div class="section-content">
                                <b> 5.1</b> Commission: For each successful sale made through the platform, a commission of 4.5% will be deducted as agreed upon in the Seller's Fee Schedule.<br />
                                    <b> 5.2</b> Payment: Payments for your sales, after deducting the applicable commission, will be remitted to your designated payment account after 15 days from the date of shipment for every order successfully processed.<br />
                            </div>
                        </div>

                         <div class="section">
                            <h3><span class="section-number">6. </span>Intellectual Property</h3>
                            <div class="section-content">
                                <b> 6.1</b> Seller Content: By providing content (product images, descriptions, etc.) to the platform, you grant us a non-exclusive, worldwide, royalty-free license to use, reproduce, display, and distribute such content for promotional and marketing purposes.<br />
                                    <b>6.2</b> Trademarks: You acknowledge that all trademarks, service marks, logos, and trade names used on the platform are the property of their respective owners, and you shall not use or display any of these without appropriate authorization.<br />
                            </div>
                        </div>

                         <div class="section">
                            <h3><span class="section-number">7. </span>Termination</h3>
                            <div class="section-content">
                                <b> 7.1</b> Termination Rights: Either party may terminate this Agreement with immediate effect upon written notice if the other party breaches any material provision of this Agreement.<br />
                                    <b>7.2</b> Obligations Upon Termination: Upon termination, you shall immediately cease using the platform, and all outstanding orders must be fulfilled as agreed.<br />
                            </div>
                        </div>

                         <div class="section">
                            <h3><span class="section-number">8. </span>Amendments</h3>
                            <div class="section-content">
                                <b>8.1</b> Modifications: We reserve the right to modify this Agreement at any time. Any changes will be notified to you, and your continued use of the platform after such notification constitutes acceptance of the amended terms.<br />
                            </div>
                        </div>

                         <div class="section">
                            <h3><span class="section-number">9. </span>Governing Law</h3>
                            <div class="section-content">
                                This Agreement shall be governed by and construed in accordance with the laws of Unites States of America, without regard to its conflict of laws principles.<br />
                                Please review this Agreement carefully before joining our Multi-Vendor E-commerce Store as a seller. By proceeding with the registration process, you signify your acceptance of these terms and conditions.<br />
                                If you have any questions or concerns about this Agreement, please contact our support team at support@mallshark.net.<br /><br />
                                By clicking "I Agree to the terms and conditions" and proceeding with the registration process, you acknowledge and agree to the terms and conditions outlined in this Agreement.<br />
                            </div>
                        </div>
                    </div>


                        <!-- Your terms and conditions text goes here -->
                        {{-- <p>
                            1. Acceptance of Terms

                            By applying to become a seller on our Mallshark E-commerce Store, you acknowledge that you have read, understood, and agree to be bound by the terms and conditions set forth in this Agreement. If you do not agree with any of these terms, please do not proceed with the registration process.
                                2. Seller Registration
                            2.1 Application Process: To become a seller on our platform, you must complete the registration process, providing accurate and up-to-date information. We reserve the right to approve or reject any seller application at our sole discretion.
                            2.2 Account Security: You are responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account. You agree to notify us immediately of any unauthorized use or security breach.
                                3. Seller Responsibilities
                            3.1 Product Listings: As a seller, you are responsible for creating and maintaining accurate and comprehensive product listings, including product descriptions, images, pricing, and inventory levels.
                            3.2 Order Fulfillment: You agree to fulfill all orders received through the platform promptly and ensure that products are delivered to customers in a timely manner.
                            3.3 Product Quality: You warrant that all products you offer for sale on our platform are of high quality, free from defects, and comply with all applicable laws and regulations.
                            3.4 Customer Service: You are responsible for providing excellent customer service to buyers, addressing inquiries, and handling customer disputes or returns in a professional and timely manner.
                                4. Product Listing Guidelines
                            4.1 Prohibited Products: You shall not list or sell any products that are illegal, counterfeit, infringe on intellectual property rights, or violate any laws or regulations.
                            4.2 Restricted Products: Certain products may be subject to additional restrictions and regulations. You must comply with all applicable laws regarding the sale of such products.
                                5. Fees and Payments
                            5.1 Commission: For each successful sale made through the platform, a commission of 4.5% will be deducted as agreed upon in the Seller's Fee Schedule.
                            5.2 Payment: Payments for your sales, after deducting the applicable commission, will be remitted to your designated payment account after 15 days from the date of shipment for every order successfully processed.
                                6. Intellectual Property
                            6.1 Seller Content: By providing content (product images, descriptions, etc.) to the platform, you grant us a non-exclusive, worldwide, royalty-free license to use, reproduce, display, and distribute such content for promotional and marketing purposes.
                            6.2 Trademarks: You acknowledge that all trademarks, service marks, logos, and trade names used on the platform are the property of their respective owners, and you shall not use or display any of these without appropriate authorization.
                                7. Termination
                            7.1 Termination Rights: Either party may terminate this Agreement with immediate effect upon written notice if the other party breaches any material provision of this Agreement.
                            7.2 Obligations Upon Termination: Upon termination, you shall immediately cease using the platform, and all outstanding orders must be fulfilled as agreed.
                                8. Amendments
                            8.1 Modifications: We reserve the right to modify this Agreement at any time. Any changes will be notified to you, and your continued use of the platform after such notification constitutes acceptance of the amended terms.
                                9. Governing Law
                            This Agreement shall be governed by and construed in accordance with the laws of Unites States of America, without regard to its conflict of laws principles.
                            Please review this Agreement carefully before joining our Multi-Vendor E-commerce Store as a seller. By proceeding with the registration process, you signify your acceptance of these terms and conditions.
                            If you have any questions or concerns about this Agreement, please contact our support team at support@mallshark.net.
                            By clicking "I Agree to the terms and conditions" and proceeding with the registration process, you acknowledge and agree to the terms and conditions outlined in this Agreement.
                        </p> --}}
                    </div>
                    <div class="form-group terms">
                        <label class="checkbox-container">
                            <input type="checkbox" id="store_term_conditon" name="store_term_conditon"
                             value="1" <?php if (isset($storeData['terms_condtions']) && $storeData['terms_condtions'] == '1') { echo 'checked'; } ?> required>
                            <span class="checkmark"></span>
                            I agree to the
                            <a href="{{URL::to('/terms');}}" target="_blank">Terms and Conditions</a>
                        </label>
                    </div>
                    <div class="col-md-6 ">
                        <button class="btn btn-primary small-btn-mbt" type="submit">Save & Continue</button>
                    </div>

                
            </form>
        </div>
        <hr>
    </div>
</div>
<script>
    $(document).ready(function() {

        $('#storeNameCreate').on('submit', function(e) {
        var storeNameInput = $('#store_name').val().trim();
        
        if (storeNameInput.length === 0) {
            toastr.options = {
            closeButton: true, 
            disableTimeOut: true,
            tapToDismiss: true,
            positionClass: 'toast-top-right',
            toastClass: 'toast-icon custom-toast-success'
            };
        toastr.error("Shop name cannot be empty or contain only whitespace.");
            e.preventDefault(); 
            return false;
        }
    });
        $('[data-toggle="tooltip"]').tooltip();



        // store name form validation start

        jQuery.validator.addMethod("lettersonly", function(e, r) {
                return this.optional(r) || /^[a-z ]+$/i.test(e)
            }, "Only letters are allowed."),
            
            jQuery.validator.addMethod("notBlankSpaces", function(value, element) {
            return $.trim(value) !== "";
            }, "Store name cannot contain only blank spaces.");

            $("#storeNameCreate").validate({
                errorElement: "span",
                rules: {
                    store_name: {
                        required: true,
                        minlength: 4,
                        lettersonly: !0,
                        maxlength: 70,
                        notBlankSpaces: true,
                        remote: "/store/available"
                    },
                    store_term_conditon: {required: true},
                },
                messages: {
                    store_name: {
                        required: "Store name is required.",
                        remote: "<div class='clear'></div><div id='register_user_form'>This store name is already registered.</div></div>"
                    },
                    store_term_conditon: {required: "Terms and conditions is required."},
                },
                errorPlacement: function(error, element) {
                    error.appendTo(element.closest('.form-group'));
                },
                submitHandler: function(form) {
                    form.submit();
                }
            });
        // store name form validation end

    });
</script>
@endsection