<?php
$count = 0;
$countCrypto = 0;
$totalCount = 0;
if (isset($ajaxCartdata)) {
	$cartdata = $ajaxCartdata;
} else {
	$cartdata = cartDetail();
}

if (isset($ajaxCartdataCrypto)) {
	$cartdataCrypto = $ajaxCartdataCrypto;
} else {
	$cartdataCrypto = cartDetailCrypto();
}
// $countCrypto = !empty($cartdataCrypto['total_quantity']) ? $cartdataCrypto['total_quantity'] : 0;
// echo '$cartdata: <pre>' .print_r($cartdata,true). '</pre>'; 
if ($countCrypto != 0) {
	$countCrypto = $countCrypto;
}

if (!empty($cartdata)) {
	$count = !empty($cartdata['total_quantity']) ? $cartdata['total_quantity'] : 0;
	$totalCount = $count + $countCrypto;
}
?>
<h5> You have <?php echo $count; ?> item(s) in your cart</h5>
<?php if (isset($cartdata)  && !empty($cartdata['cart_data'])) {
?>


	<div class="scrollbar-cntnt">
		<?php
		$imageUrl = '';
		$subTotal = 0;
		$totalDiscount = 0;
		foreach ($cartdata['cart_data'] as $cd) {
			$productStore = getStoreInfoByIdCartApi($cd['store_id']);
			$category_id = getCategoriesByProductID($cd['product_id']);
			if(isset($category_id[0])){
				$productCategory = getCategoryByCateId($category_id[0]);
			}else{
				$productCategory = null;
			}
			
			
			$feature_image = isset($cd['feature_image']) ? $cd['feature_image'] : "";
			$product_type = isset($cd['product_type']) ?  $cd['product_type'] : "";
			$variation_image = isset($cd['product_variation_image']) ? $cd['product_variation_image'] : "";
			$attribute1 = isset($cd['attribute1']) ? $cd['attribute1'] : "";
			$attribute2 = isset($cd['attribute2']) ? $cd['attribute2'] : "";
			$attribute3 = isset($cd['attribute3']) ? $cd['attribute3'] : "";
			//  echo $attribute1.' attribute1 </br>';
			// echo $product_type.' product type </br>';
			// echo $variation_image.' variation_image imageid </br>';
			// dump($cd);
			if ($product_type == 'simple') {
				$imageUrl = getAttachmentUrlById((int) $cd['feature_image'], 'medium');
				// echo 'simple product image'.$imageUrl.'</br>';
			} elseif ($product_type == 'variant') {
				$imageUrl = getAttachmentUrlById((int) $cd['product_variation_image'], 'medium');
					if(empty($imageUrl)){
						$imageUrl = getAttachmentUrlById((int) $cd['feature_image'], 'medium');
					}
				// echo 'virtual product image'.$imageUrl.'</br>';

			} else {
				$imageUrl = getAttachmentUrlById((int) $cd['feature_image'], 'medium');
				// echo 'product_variation_image product image'.$imageUrl.'</br>';

			}
			// $imageUrl = getAttachmentUrlById((int) $cd['feature_image'], 'medium');
			// if($product_type == 'variant'){
			//     $imageUrl =   getAttachmentUrlById((int) $cd['product_variation_image'], 'small');
			// }elseif($product_type == 'simple'){
			//     $imageUrl =   getAttachmentUrlById((int) $cd['feature_image'], 'small');
			// }else{
			//     $imageUrl = getAttachmentUrlById((int) $cd['feature_image'], 'small');
			// }
		?>
			<div class="cartitm cart-basket-header-view">
				<a class="" href="<?php echo URL::to('/product/') . "/" . $cd['slug']; ?>">
					<span>
						<img src="{{ $imageUrl }}" class="mini-cart-product-thumbnail" alt="{{$cd['title']}}" title="{{$cd['title']}}" onerror="this.src='{{url('/assets/images/placeholder.jpg')}}'" width="70">
					</span>

					<span class="">
						<div class="cart-popup-title"> <?php echo $cd['title']; ?> </div>
						<div class="lst-hd-sbm">
								
							<span class="product_category" data-category="{{isset($productCategory) ? $productCategory['categoryTitle'] : '' }}"></span>
							<span class="product_product_id" data-product-id="{{$cd['product_id']}}" style="display: inline-table;">
							<span class="product_title" data-title="{{$cd['title']}}" style="width: 100%;">
							<span class="product_store" data-store="{{$productStore['store_name']}}" style="width: 100%;">
								@php
									$isVariant = $cd['product_type'] == 'variant';
                                    $onSale = isset($cd['on_sale']) && $cd['on_sale'] == 1;
                                    $variationPrice = $isVariant ? (isset($cd['variation_sale_price']) ? $cd['variation_sale_price'] : $cd['price']) : null;
									
                                    $variationSalePrice = $isVariant && $onSale ? $cd['product_variation_sale_price'] : $variationPrice;
                                    $price = !$isVariant ? ($onSale ? $cd['sale_price'] : $cd['price']) : null;
									$productActualPrice = $isVariant ? (isset($cd['variation_sale_price']) ? $cd['variation_sale_price'] : $cd['price']) : null;
									$productDiscount = $productActualPrice - $variationSalePrice;
									$subTotal += $productActualPrice;
									$totalDiscount += $productDiscount;

								@endphp

								@if($onSale)
									<del class="text-primary align-text-mbt" style="font-size: 11px !important;">
											$ {{ number_format($isVariant ? $variationPrice : $cd['price'], 2) }}
										</del>
																		<span class="text-primary align-text-mbt price-ratio" style="color: red !important;">
											$ {{ number_format($isVariant ? $variationSalePrice : $price, 2) }}
											<b class="small-mul">
												<i class="material-icons">clear</i>
											</b>
											{{ $cd['product_quantity'] }}
											<span class="product_price" data-product-price="{{ $isVariant ? $variationSalePrice : $price }}"></span>
											<span class="product_quantity" data-product-quantity="{{ $cd['product_quantity'] }}"></span>
										</span>
																	@else
																		<span class="text-primary align-text-mbt price-ratio">
											$ {{ number_format($isVariant ? $variationPrice : $price, 2) }}
											<b class="small-mul">
												<i class="material-icons">clear</i>
											</b>
											{{ $cd['product_quantity'] }}
											<span class="product_price" data-product-price="{{ $isVariant ? $variationPrice : $price }}"></span>
											<span class="product_quantity" data-product-quantity="{{ $cd['product_quantity'] }}"></span>
										</span>
								@endif
						</div>

						<div class="color-andweight mt-3 ">

							
							<div>
								<?php
								$fill = "";
								if (Session::has('userData')) {

									$favProductId = getBuyerFavProducts();
									if (!empty($favProductId) && in_array($cd['product_id'], $favProductId)) {
										$fill =  "fillheart";
									}
								} ?>
								<!-- <a class="adfv adtofv-home-cart <?php echo $fill; ?>" data-product-id="<?php echo $cd['product_id']; ?>" href="javascript:void(0);" style="position:relative; top:-1px"><img class="image-add_tofav" style="width:20px; " src="{{ URL::asset('assets/images/heart-v2.svg')}}" alt="rate"> </a> -->
								<a href="javascript:;" data-store-id="<?php echo $cd['store_id']; ?>" data-cart-id="<?php echo $cd['cart_id']; ?>" data-product-id="<?php echo $cd['product_id']; ?>" class="remove-item-mbt remove-bask" onclick=" event.stopPropagation();">
									<span>
										<!-- <i class="fa fa-times" aria-hidden="true"></i> -->
										<i class="material-icons">delete</i>
									</span>
								</a>

							</div>
					</span>
			</div>
			</a>

	</div>

<?php
		}
		
	}
	
?>
<?php if (isset($cartdata)  && !empty($cartdata['cart_data'])) { ?>

	<div class="view-basket d-flex justify-content-between">
		<a href="{{URL::to('/cart')}}" class="col">View cart</a>
		<a href="{{URL::to('/checkout')}}" class="col">
			Checkout
		</a>
	</div>

<?php } elseif (isset($cartdataCrypto)  && !empty($cartdataCrypto['cart_data'])) { ?>
	<div class="view-basket d-flex justify-content-between">
		<a href="{{URL::to('/cart')}}" class="col">View cart</a>
		<a href="{{URL::to('/checkout')}}" class="col">
			Checkout
		</a>
	</div>

<?php } ?>
<script>
	$(document).ready(function() {
		$(".remove-item-mbt").on("click", function() {
			currentHtml = $(this).closest('.cartitm');
			data = {};
			cart_id = $(this).attr('data-cart-id')
			data['cart_id'] = $(this).attr('data-cart-id');
			p_id = $(this).attr('data-product-id');
			if (p_id != '') {
				jQuery('.cartadd').each(function() {
					new_id = jQuery(this).attr('data-product-id');
					if (new_id == p_id) {
						jQuery(this).closest('.crtbtn').find('input[name=cart_quantity]').val(1);
						jQuery(this).closest('.crtbtn').find('.plus-icon').removeClass('disabled');
						jQuery(this).parent().removeClass('show-quantitiy-panel');
					}
				});
			}
		// GTM Event to track cart items delete
		let product_title = $(this).closest('.cart-basket-header-view').find('.product_title').data('title');
        let product_brand = $(this).closest('.cart-basket-header-view').find('.product_store').data('store');
        let product_price = $(this).closest('.cart-basket-header-view').find('.product_price').data('product-price');
        let product_category = $(this).closest('.cart-basket-header-view').find('.product_category').data('category');
        let product_stock = $(this).closest('.cart-basket-header-view').find('.product_quantity').data('product-quantity');
        let product_id = $(this).closest('.cart-basket-header-view').find('.product_product_id').data('product-id');
             
            dataLayer.push({ ecommerce: null });  
            dataLayer.push({
            event: "remove_from_cart",
            ecommerce: {
            currency: "USD",
                    value: 7.77,
                    items: [
                        {
                        item_id: product_id,
                        item_name: product_title,
                        index: 0,
                        item_brand: product_brand,
                        item_category: product_category,
						item_list_id: "remove_from_cart",
         			    item_list_name: "Delete Cart Product",
                        price: product_price,
                        quantity: product_stock
                        }
                    ]
                }
            });
			sendreq(data, 'cart', 'deletebasket', 'delProduct');
		});


		$(document).on('click', '.adtofv-home-cart', function() {

			setTimeout(() => {
				var current = $(this);
				var productId = current.data('product-id');

				var data = {
					'product_id': productId
				};
				if (current.hasClass('fillheart')) {
					// Already favorited, remove it
					sendreq(data, 'favourite', 'add', 'callbackfavourites');
					current.removeClass('fillheart');
				} else {
					// Not favorited, add it
					sendreq(data, 'favourite', 'add', 'callbackfavourites');
					current.addClass('fillheart');
				}
			}, 500);
		});
	});

	function delProduct(data) {
		if (data.status) {
			toastr.options = {
				closeButton: true, // Add close button
				disableTimeOut: true,
				tapToDismiss: true,
				positionClass: 'toast-bottom-right',
				toastClass: 'toast-icon custom-toast-success'
			};
			toastr.error(data.message);
			currentHtml.remove();
			$(".cartmenu").html(data.view);
			$("#cart_counter_ajax").html(data.cartCounter);
		}
	}
</script>