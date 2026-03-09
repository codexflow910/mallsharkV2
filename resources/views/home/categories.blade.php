<div class="container catbx" style="margin-top:50px">
    <div class="row">
        <div class="col-md-12">
            <h3>Categories</h3>
        </div> 
    </div>
<div class="row">
    @php
    $categories = getAllParentCategories(8);   
    @endphp           
    @if(count($categories)>0)
    @foreach ($categories as $category)
    @php
    
             $image_url = getAttachmentUrlById((int) $category->image_id, 'medium');
             if (!isImageExists($image_url)) {
             $image_url = url(asset('assets/images/placeholder.jpg', env('HTTPS_STATUS')));
                }
    @endphp  
    <div class="col-md-3">
        <!-- card starts -->
        <div class="card">
            
        
            <a href=" {{ URL::to('/category?name=') .$category->category_slug  }}"><img src="{{ $image_url }}" class="card-img-top" alt="{{$category->categoryTitle}}" title="{{$category->categoryTitle}}"></a>
            <div class="card-body">
                <h4 class="catttl"><a href=" {{ URL::to('/category?name=') .$category->category_slug  }}"> {{ $category->categoryTitle }}</a></h4>
                
                 
            </div>
        </div>
        <!-- card ends-->
        
    </div>
    @endforeach
    @endif
 


        
</div>
</div>

<!-- Category Box Ends -->