<style>
    .has-error{
    border:1px solid red !important;
    }
</style>

<form action="" method="post">
    <div class="review-body" style="margin-left:0px;">
        <div class="response"></div>
        <p class="review-body-text">Describing your experience with this product or seller</p>
        <textarea class="form-control" id="rating-text"></textarea>
        <!--Rating Stars Box -->
        <div class='rating-stars clearfix'>
            <div class="your-rating-headeing">Your Rating:</div>
            <div class="star-rating-panel-mbt">
                <div class="star-rating">
                    <span class="fa fa-star-o" data-rating="1"></span>
                    <span class="fa fa-star-o" data-rating="2"></span>
                    <span class="fa fa-star-o" data-rating="3"></span>
                    <span class="fa fa-star-o" data-rating="4"></span>
                    <span class="fa fa-star-o" data-rating="5"></span>
                    <div class="ratingerror" style="color:red;font-size: 13px;margin-top: -10px;">
                    </div>
                    <input type="hidden" id="rating_count" name="rating_count" class="rating-value" value="0">
                    <input type="hidden" id="author_id" name="author_id" value="{{getCurrentUserId()}}">
                    <input type="hidden" id="product_id" name="product_id" value="{{$product_id}}">
                    <input type="hidden" id="orderId" name="orderId" value="{{$orderId}}">
                </div>
            </div>

            <div class="review-button-post">
                <button class="btn btn-primary" id="submit-review">
                    Post Your Review
                </button>
                <span>Note: you can post your review and rating only once.</span>
            </div>
        </div>

    </div>
</form>

<script>
     $(document).ready(function() {
        var $star_rating = $('.star-rating .fa');
        
        var SetRatingStar = function() {
            return $star_rating.each(function() {
                if (parseInt($star_rating.siblings('input.rating-value').val()) >= parseInt($(this)
                        .data('rating'))) {
                    return $(this).removeClass('fa-star-o').addClass('fa-star');
                } else {
                    return $(this).removeClass('fa-star').addClass('fa-star-o');
                }
            });
        };
        $star_rating.on('click', function() {
            $star_rating.siblings('input.rating-value').val($(this).data('rating'));
            return SetRatingStar();
        });
        SetRatingStar();
        $(document).ready(function() {

        });
    });

    var isSubmitInProgress = false;
    jQuery(document).on('click', '#submit-review', function(event) {
        //jQuery('.ratingerror').html('');

        if (isSubmitInProgress) {
            return;
        }

        jQuery('.ratingerror').hide();
        comment = jQuery('#rating-text').val();
        author_id = jQuery('#author_id').val();
        product_id = jQuery('#product_id').val();
        orderId = jQuery('#orderId').val();
        jQuery('#rating-text').removeClass('has-error');
        event.preventDefault();
        rating_count = jQuery('#rating_count').val();
        if(comment === '') {
            jQuery('#rating-text').addClass('has-error');
            return;
        }
        if (rating_count < 1) {
            jQuery('.ratingerror').html('Please select at least one star');
            jQuery('.ratingerror').show('fast', 'linear');
            return false;
        }
        
        if (rating_count != '' && comment != '') {
            
            jQuery(this).show();
            jQuery("#submit-review").hide();
            jQuery('.uploadImages').show();
            jQuery('#review-popup').hide();
            isSubmitInProgress = true;
            jQuery.ajax({
                url: "/add-review",
                data: 'product_id=' + product_id + '&author_id='+author_id+'&rating_count=' +
                    rating_count + '&comment=' + comment + '&action=add_review' + '&orderId=' + orderId,
                method: 'POST',
                success: function(res) {
                    jQuery('.review-popup-modal').hide();
                    var obj = jQuery.parseJSON(res);
                    jQuery('.response').html(obj.message);
                    swal("Success!", "Review added successfully", "success");
                    setTimeout(function() {
                        //location.reload();
                        window.location.href = '/new-buyer-orders-detail/'+ orderId;
                    }, 1000);
                }, error: function() {
                alert('Failed to submit review.');
            },
            complete: function() {
                isSubmitInProgress = false;
            }
            });
        }
    });
    
</script>