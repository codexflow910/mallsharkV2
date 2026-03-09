<div class="container" style="margin-top:20px">
    <div class="row">
        <div class="col-md-12">
            <h3>Recent Products </h3>
        </div>
         <div class="view-al place-into-tap" style="top:0px;">
            <a href="{{  URL::to('/category?name=')  }}" class="viewalllink">View All</a>
        </div>
    </div>
<div class="row">           
        @if(count($recentProducts) >0)
        @foreach ($recentProducts as $rProduct)
        @php
            
            // dd($rProduct->store);
             $image_url = getAttachmentUrlById((int) $rProduct->feature_image, 'medium');
             if (!isImageExists($image_url)) {
             $image_url = url(asset('assets/images/placeholder.jpg', env('HTTPS_STATUS')));
                }
     
        @endphp
        <div class="col-6">
            <!-- card starts -->
            <div class="card mobcrd">
            
                <a href="{{  URL::to('/product/' . $rProduct->slug) }}"><img src="{{ $image_url }}" class="card-img-top" alt="{{ $rProduct->title }}" title="{{ $rProduct->title }}"></a>
                <div class="card-body">
                    <h4 class="card-title"><a href="{{  URL::to('/product/' . $rProduct->slug) }}"> {{ $rProduct->title }}</a></h4>
                    <p class="card-text prc">
                        <span class="n-price"><span class="z-symbl">USDC </span> {{ $rProduct->price }}</span> 

                        <span class="n-stars  push-right">
                            @php echo getProductReviewAverage($rProduct->product_id) @endphp
                         </span>
                    </p>
            
                    <p class="card-text small  ">
                        <span class="">By: <a href="{{  URL::to('/brand/' . $rProduct->store->store_slug) }}">{{ $rProduct->store->store_name }}</a></span> 
                    </p>     
                </div>
            </div>
            <!-- card ends-->

        </div>

            
        @endforeach
        @endif

        
</div>
</div>