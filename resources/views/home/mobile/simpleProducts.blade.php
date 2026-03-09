<div class="container" style="margin-top:12px">
    <div class="row">           
        @if (count($simpleProducts) > 0)
        @foreach ($simpleProducts as $sProduct)
         @php
             $image_url = getAttachmentUrlById((int) $sProduct->feature_image, 'medium');
             if (!isImageExists($image_url)) {
             $image_url = url(asset('assets/images/placeholder.jpg', env('HTTPS_STATUS')));
                }
         @endphp   
        <div class="col-6">
            <!-- card starts -->
            <div class="card mobcrd">
               
                <a href="{{  URL::to('/product/' . $sProduct->slug) }}"><img src="{{ $image_url }}" class="card-img-top" alt="{{ $sProduct->title }}" title="{{ $sProduct->title }}"></a>
                <div class="card-body">
                    <h4 class="card-title"><a href={{  URL::to('/product/' . $sProduct->slug) }}> {{ $sProduct->title }}</a></h4>
                    <p class="card-text prc">
                        <span class="n-price"><span class="z-symbl">USDC </span> {{ $sProduct->price }}</span> 

                        <span class="n-stars  push-right">
                           @php echo getProductReviewAverage($sProduct->product_id) @endphp
                        </span>
                    </p>
                   
                    <p class="card-text small  ">
                        <span class="">By: <a href="{{  URL::to('/brand/' . $sProduct->store->store_slug) }}">{{ $sProduct->store->store_name }}</a></span> 
                    </p>     
                </div>
            </div>
            <!-- card ends-->

        </div>
        @endforeach
            
        @endif
            
    </div>
</div>