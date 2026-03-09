<div class="superDeailInnerWrapper">
    <h3 class="d-flex align-items-center secHeadingTm">
        <img src="{{ URL::asset('assets/images/star_award_icon.svg')}}" alt="" style="max-width: 24px;" />
        <span class="dealTxtFl superDealsText">Super Deals</span>
    </h3>
    
    
    <div class="row rowCm">
        <div class="col-md-6">
            <div class="deailsBoxWrap boxGreen radius10">
                <h4 class="d-flex align-item-center justify-content-between secHeadTp">
                    <span style="color: #000;">Welcome <strong>Deal</strong></span>
                    <span style="color: #09ab62 ;" class="increasesized"><strong>Upto 20% Off</strong></span>
                </h4>
                    <div class="dealsSecOne">
                            <div class="productItems">
                                <div class="slideItem">
                                    <?php 
                                    $saleUpto20 = saleUpto(20);
                                    ?>
                                    @foreach($saleUpto20 as $product)
                                    <?php
                                        $image = getAttachmentUrlById($product->feature_image);
                                    ?>
                                     <a href="/welcome-deal">
                                    <div class="productImage">
                                        <img src="{{$image}}" title="{{$product->title}}">
                                    </div>
                                </a> 
                                    @endforeach             
                                </div>
                        </div>
                    </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="deailsBoxWrap radius10" style="background-color:#f9ebc8;">
                <h4 class="d-flex align-item-center justify-content-between secHeadTp">
                    <span style="color: #000;">Best <strong>Sellers</strong></span>
                    <span style="color: #ff9c00;" class="increasesized"><strong>Upto 50% Off</strong></span>
                </h4>
                    <div class="dealsSecOne">
                            <div class="productItems">
                                <div class="slideItem">
                                    <?php 
                                    $saleUpto20 = saleUpto(50);
                                    ?>
                                    @foreach($saleUpto20 as $product)
                                    <?php
                                        $image = getAttachmentUrlById($product->feature_image);
                                    ?>
                                    <a href="/best-sellers">
                                   <div class="productImage">
                                    <img src="{{$image}}" title="{{$product->title}}">
                                </div>
                            </a>
                                @endforeach              
                                </div>
                        </div>
                    </div>
            </div>
        </div>


    </div>




</div>