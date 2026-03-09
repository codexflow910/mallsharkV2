<div class="container" style="margin-top:50px; display:none">
    <div class="row">           
        @if (count($simpleProducts) > 0)
        @foreach ($simpleProducts as $sProduct)
         @php
             $image_url = getAttachmentUrlById((int) $sProduct->feature_image, 'medium');
             if (!isImageExists($image_url)) {
             $image_url = url(asset('assets/images/placeholder.jpg', env('HTTPS_STATUS')));
                }
         @endphp   
        <div class="col-md-3">
            <!-- card starts -->
            <div class="card">
                <a href="{{  URL::to('/product/' . $sProduct->slug) }}"><img src="{{ $image_url }}" class="card-img-top" alt="{{ $sProduct->title }}" title="{{ $sProduct->title }}"></a>
                <div class="card-body">
                    <h4 class="card-title"><a href={{  URL::to('/product/' . $sProduct->slug) }}> {{ $sProduct->title }}</a></h4>
                    <p class="card-text prc">
                        @if($sProduct->on_sale == 1)
                            <span class="n-price red-color"><span class="z-symbl">USDC </span> {{ $sProduct->sale_price }}</span>
                            <del class="sale-price"><span class="z-symbl">USDC </span> {{ $sProduct->price }}</del>
                            @if($sProduct->discout_percentage > 0)
                            <span class="n-price mybadge">- {{ $sProduct->discout_percentage }}%</span>
                            @else
                            <span class="n-price mybadge">{{ $sProduct->discout_percentage }}%</span>
                            @endif
                        @else
                            <span class="n-price"><span class="z-symbl">USDC </span> {{ $sProduct->price }}</span>
                        @endif
                        <span class="n-stars  push-right">
                           @php echo getProductReviewAverage($sProduct->product_id) @endphp
                        </span>
                    </p>
                   
                    <p class="card-text small  ">
                        
                    </p>     
                </div>
            </div>
            <!-- card ends-->

        </div>
        @endforeach
            
        @endif
            
    </div>
</div>