function callbackDeleteCheckout(response){
    // debugger
    $('#loading').hide();
    window.location.reload();
}
$(document).on('click', '.remove-product', function () {
    productId = $(this).data('product-id');
    variationId = $(this).data('variation-id');
    if (productId) {
        let data = {};
        data = {productId,variationId}
        swal({
            title: "Are you sure?",
            text: "Are you sure want to remove this product from checkout?",
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-success",
            confirmButtonText: "Yes, Do it!",
            closeOnConfirm: true
        },

            function () {

                $('#loading').show();
                sendreq(data, 'cart', 'deleteCheckout', 'callbackDeleteCheckout');
                // //jQuery('.uploadImages').show();



                // $(".cart-item-table").each(function () {
                //     totalProductlenght = totalProductlenght + $(this).length;
                // });

                // if (totalProductlenght <= 1) {
                //     sideBar.remove();
                //     $(".cart-class").html("No record Found");
                // }


            });

    }
    return;
    // checkout
    currentDeletedItem = $(this).closest('.cart-item-table');

    sideBar = $(this).closest('.main');
    data = {};
    data['cart_id'] = $(this).attr('data-cart-id');
    var totalProductlenght = 0;

    swal({
        title: "Are you sure?",
        text: "Are you sure want to remove this product from cart?",
        type: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn-success",
        confirmButtonText: "Yes, Do it!",
        closeOnConfirm: false
    },

        function () {

            $(".large-loader").show();
            //jQuery('.uploadImages').show();



            $(".cart-item-table").each(function () {
                totalProductlenght = totalProductlenght + $(this).length;
            });

            sendreq(data, 'cart', 'delete', 'delproducts');
            if (totalProductlenght <= 1) {
                sideBar.remove();
                $(".cart-class").html("No record Found");
            }


        });
    /*
     if (!result) {
     $(".large-loader").hide();
     return false;
     }
     */

});