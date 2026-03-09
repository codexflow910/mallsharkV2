<div class="strnav">
        <div class="col-md-12">
            <nav class="container-fluide dshbrd"> 
                <div class="row">
                    <ul class="nav">
                        <?php  if (collect(request()->segments())->last() == 'brand/'.$storeId) {
            ?> 
                        <li class="nav-item crnt"><a class="nav-link active" href="#">Products</a></li>
                        <?php }else{ ?>
                         <li class="nav-item"><a class="nav-link active" href="#">Products</a></li>
                        <?php } ?>
                        <li class="nav-item "><a class="nav-link" href="#">Review</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">About</a></li>
                        <li class="nav-item" style="display:none;"><a class="nav-link active" href="{{URL::to('/affiliate-programe')}}">Affiliate Program</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Policies</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Contact</a></li>     
                    </ul>

                    <span class=" strsoc pull-right">  
                        <a class="sclicn" href=""><img src="{{ asset('assets/images/icon-fb.jpg',env('HTTPS_STATUS')) }}" alt="icon-fb" title="icon-fb"></a> &nbsp;
                        <a class="sclicn" href=""><img src="{{ asset('assets/images/icon-twt.jpg',env('HTTPS_STATUS')) }}" alt="icon-twt" title="icon-twt"></a> &nbsp;
                        <a class="sclicn" href=""><img src="{{ asset('assets/images/icon-ggl.jpg',env('HTTPS_STATUS')) }}" alt="icon-ggl" title="icon-ggl"></a> &nbsp;
                        <a class="sclicn" href=""><img src="{{ asset('assets/images/icon-pnt.jpg',env('HTTPS_STATUS')) }}" alt="icon-pnt" title="icon-pnt"></a>
                    </span>
                </div>
            </nav>     
        </div>    
    </div>