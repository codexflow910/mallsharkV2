
<div class="tpbrnds" style="background-color:#fff; ">                   
    <div class="container ">
            <div class="row">
                    <div class="col-md-12">
                        <h3>Top Brands</h3>
                    </div>
                    @if (count($brands) > 0)
                    <div class="col-md-12 topbrands">
                        <div class="topbrandsin"> 
                            <ul>
                    @foreach ($brands as $brand)
                    @php
                        $image_url = getAttachmentUrlById((int) $brand->store_logo, 'small');
                            if (!isImageExists($image_url)) {
                                $image_url = url(asset('assets/images/placeholder.jpg', env('HTTPS_STATUS')));
                            }
                    @endphp
                    <li class="ml-3 mr-3"><a href="{{ URL::to('/brand/' . $brand->store_slug) }}">
                            <img src="{{$image_url }}" alt="{{ $brand->store_name }}" title="{{ $brand->store_name }}"></a></li>
                    @endforeach
                </ul>
            </div>
        </div>  
                        
                    @endif
                    {{-- <div class="col-md-12 topbrands">
                        <div class="topbrandsin"> 
                            <ul>
                            <li><a href="#"><img src="{{ asset('assets/images/dummy-logo.jpg',env('HTTPS_STATUS')) }}" alt=" " title="ad"></a></li>
                            <li><a href="#"><img src="{{ asset('assets/images/dummy-logo.jpg',env('HTTPS_STATUS')) }}" alt=" " title="ad"></a></li>
                            <li><a href="#"><img src="{{ asset('assets/images/dummy-logo.jpg',env('HTTPS_STATUS')) }}" alt=" " title="ad"></a></li>
                            <li><a href="#"><img src="{{ asset('assets/images/dummy-logo.jpg',env('HTTPS_STATUS')) }}" alt=" " title="ad"></a></li>
                            <li><a href="#"><img src="{{ asset('assets/images/dummy-logo.jpg',env('HTTPS_STATUS')) }}" alt=" " title="ad"></a></li>
                            
                            </ul>
                        </div>
                    </div>   --}}
            </div>
                   
    </div>
                        </div>  