<div class="container" style="margin-top:20px">
    <div class="row">
        <div class="col-md-12">
            <h3>Best Sellers</h3>
        </div> 
    </div>


    <div class="row">
        <!-- <div class="col-md-3"><a href="https://stage.mallshark.io/product/art-223103"><img style="width:100%" src="{{ asset('assets/images/best-seller.jpg') }}" alt="ad" title="ad"></a></div> -->
        <div class="col-md-9">
        <div class="row">
        @if(count($bestSellers) > 0)
        @foreach ($bestSellers as $bSeller)
        @php
            
        
         $image_url = getAttachmentUrlById((int) $bSeller->feature_image, 'medium');
         if (!isImageExists($image_url)) {
         $image_url = url(asset('assets/images/placeholder.jpg', env('HTTPS_STATUS')));
            }
 
        @endphp
        <div class="col-6">
            <!-- card starts -->
            <div class="card mobcrd">
              
                                
            
                <a href="{{  URL::to('/product/' . $bSeller->slug) }}"><img src="{{ $image_url }}" class="card-img-top" alt="{{ $bSeller->title }}" title="{{ $bSeller->title }}"></a>
                <div class="card-body">
                    <h4 class="card-title"><a href="{{  URL::to('/product/' . $bSeller->slug) }}"> {{ $bSeller->title }}</a></h4>
                    <p class="card-text prc">
                        <span class="n-price"><span class="z-symbl">USDC </span> {{ $bSeller->price }}</span> 

                        <span class="n-stars  push-right">
                            @php echo getProductReviewAverage($bSeller->product_id) @endphp
                         </span>
                    </p>
            
                    <p class="card-text small  ">
                        <span class="">By: <a href="{{  URL::to('/brand/' . $bSeller->store->store_slug) }}">{{ $bSeller->store->store_name }}</a></span> 
                    </p>     
                </div>
            </div>
            <!-- card ends-->
        </div>    
        @endforeach
        @endif
    
            
    </div>
    </div>   
    </div>



 
</div>