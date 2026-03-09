<div class="container prdctlov" style="margin-top:20px">
    <div class="row">
        <div class="col-md-12">
            <h3>Products You Love</h3>
        </div> 
    </div>
<div class="row">           
        @if(count($productsYouLove) > 0)
        @foreach ($productsYouLove as $product)
        @php
        
         $image_url = getAttachmentUrlById((int) $product->feature_image, 'medium');
         if (!isImageExists($image_url)) {
         $image_url = url(asset('assets/images/placeholder.jpg', env('HTTPS_STATUS')));
            }
 
        @endphp
        <div class="col-6">
            <!-- card starts -->
            <div class="card mobcrd">
            
                <a href="{{  URL::to('/product/' . $product->slug) }}"><img src="{{ $image_url }}" class="card-img-top" alt="{{ $product->title }}" title="{{ $product->title }}"></a>
                <div class="card-body">
                    <h4 class="card-title"><a href="{{  URL::to('/product/' . $product->slug) }}"> {{ $product->title }}</a></h4>
                    <p class="card-text prc">
                        <span class="n-price"><span class="z-symbl">USDC </span> {{ $product->price }}</span> 

                        <span class="n-stars  push-right">
                            @php echo getProductReviewAverage($product->product_id) @endphp
                         </span>
                    </p>
            
                    <p class="card-text small  ">
                        <span class="">By: <a href="{{  URL::to('/brand/' . $product->store->store_slug) }}">{{ $product->store->store_name }}</a></span> 
                    </p>     
                </div>
            </div>
            <!-- card ends-->

        </div>
        @endforeach
        @endif


        
</div>
</div>