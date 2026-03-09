@extends('layouts.frontend-main')
@section('content')

<style>
    .unsubsribe-email:disabled {
    border-radius: 20px;
}
</style>
<div class="container">
    <div class="error-wrapper coming-soon">
        <div class="error-detail">
            <div class="error-title">
                <h1>Un-Subscribe Email</h1>
            </div>

            <div class="error-description">
                <h6>Unsubscribe from the MallShark newsletter.</h6>
                <p>By unsubscribing, you will no longer receive our newsletter, including exclusive offers, promotions, and updates about our products and services.</p>
                <p>We invite you to stay connected with MallShark through our website and social media channels, where you can still access the latest news, announcements, and product information.</p>
            </div>

            <?php 
                $userInfo = getUserById($user_id);
                $Unsubscribe = '';
                $disable = '';
                if($userInfo->subscription == 0){
                    $Unsubscribe = 'Unsubscribed';
                    $disable = 'disabled';
                }else{
                    $Unsubscribe = 'Unsubscribe';
                    $disable = '';
                }
            ?>
            <div class="error-button mt-3">
                <a href="javascript:void(0)"> <button data-id="{{$user_id}}" class="btn btn-primary unsubsribe-email" {{$disable}}>{{$Unsubscribe}}</button></a>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {

        $('.unsubsribe-email').click(function() {
            // Perform AJAX request
            var user_id = $(this).attr('data-id');
            
            $.ajax({
            url: '/ubsubscribe/user', 
            type: 'POST',
            data: {
                user_id: user_id,
              
            },
            success: function(response) {
                if (response.status == '1') {
                    $(".unsubsribe-email").prop('disabled', true);
                    $(".unsubsribe-email").text('Unsubscribed');
                    $(".unsubscribe-email").css('border-radius', '20px');

                toastr.options = {
                    closeButton: true, // Add close button
                    disableTimeOut: true,
                    tapToDismiss: true,
                    positionClass: 'toast-top-right',
                    toastClass: 'toast-icon custom-toast-success'
                };
                toastr.success("You have unsubscribed from the email.");
            } else {
                $(".unsubsribe-email").prop('disabled', true);
                toastr.options = {
                    closeButton: true, // Add close button
                    disableTimeOut: true,
                    tapToDismiss: true,
                    positionClass: 'toast-top-right',
                    toastClass: 'toast-icon custom-toast-success'
                };
                toastr.error("This email has already been unsubscribed.");
            }
            },
            error: function(xhr, status, error) {
                // Handle any errors that occur during the request
                console.error('AJAX request error');
                console.error(error);
            }
            });
        });

    });
</script>
@endsection