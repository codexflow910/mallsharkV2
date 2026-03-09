<style>
  .table td, .table th {
  vertical-align: baseline !important;
  border-top: 1px solid #dee2e6;
  padding: 0.5rem 2px !important;
}
</style>
<div class="row prdcnt">
    <div class="col-12">
      <p style="margin: 25px 0px 25px 6px;"><b>{{ count($products) }}</b> Products to be imported</p>
    </div>
    @if(count($products)>0)
        
    <div class="row col-12 mb-3" style="margin-left: -4px;">
      <div class="col-4"><b>Woocommerce Category</b></div>
      <div class="col-4"><b>MallShark Categories</b></div>
      <div class="col-4"><b>Shipping Profile</b></div>
    </div>
    @foreach ($shopifyCategories as $key=> $sCategory)
    <div class="row col-12 mt-10 mb-10" style="display: flex; align-items: center;margin-left: -4px;">
      @php
          // $regex = '/\/(?=\d)/'; // matches the slash before the numbers
          // $parts = preg_split($regex, $key);
          // $sCategoryId = $parts[1];
          $sCategoryId = $key;
      @endphp
      <div class="col-4">
        <p key="{{ $key }}" class="mb-0">{{ $sCategory }}</p>
      </div>
      <div class="col-4">
        <select class="form-control sCategory" onchange="sCategoryChanged(this)" data-key={{ $sCategoryId }}>
          <option value="">Select Category</option>
          @foreach ($categories as $category)
            @if ($category->parent_id == 0) <!-- Check if category is a parent category -->
              {{-- <optgroup label="{{ $category->categoryTitle }}"> --}}
                <option value="{{ $category->id }}">{{ $category->categoryTitle }}</option>
                {{-- @if ($category->childCategories)
                  @foreach ($category->childCategories as $childCategory)
                    <option value="{{ $childCategory->id }}"> -- {{ $childCategory->categoryTitle }}</option>
                    <!-- Add more nested levels as needed -->
                  @endforeach
                @endif --}}
              {{-- </optgroup> --}}
            @endif
          @endforeach
        </select>
      </div>
      <div class="form-group input-group-sm col-3 " style=" margin-bottom:0">
        @if (count($shippingProfiles) > 0)
            
        <select class="form-control profile" data-key="{{ $sCategoryId }}" onchange="profileChanged(this)" style="height: calc(1.8125rem + 9px);">
          @foreach ($shippingProfiles as $profile)
              <option value="{{ $profile['id'] }}"> {{ $profile['profile_name'] }}</option>
          @endforeach
        </select>
        @endif
        </div>
        <div class="form-group mb-0  col-1 listing-checkbox no-brder">
          <label for="select_cat_{{ $sCategoryId }}" class="container-check"><input type="checkbox"
              id="select_cat_{{ $sCategoryId }}" class="select-cat" data-key="{{ $sCategoryId }}">
            <span class="checkmark"></span>
          </label>
        </div>
    </div>
    @endforeach
    @if ($NA)
    <div class="row col-12 mt-10">
      <div class="col-4">
        <p>Uncategorized</p>
      </div>
      <div class="col-4">
        <select class="form-control sCategory" onchange="naCategoryChanged(this)">
          <option value="">Select Category</option>
          @foreach ($categories as $category)
            @if ($category->parent_id == 0) <!-- Check if category is a parent category -->
              {{-- <optgroup label="{{ $category->categoryTitle }}"> --}}
                <option value="{{ $category->id }}" >{{ $category->categoryTitle }}</option>
                {{-- @if ($category->childCategories)
                  @foreach ($category->childCategories as $childCategory)
                    <option value="{{ $childCategory->id }}"> -- {{ $childCategory->categoryTitle }}</option>
                    <!-- Add more nested levels as needed -->
                  @endforeach
                @endif --}}
              {{-- </optgroup> --}}
            @endif
          @endforeach
        </select>
      </div>
       <div class="form-group input-group-sm col-3" style=" margin-bottom:0">
        @if (count($shippingProfiles) > 0)
            
        <select class="form-control profile"  onchange="naProfileChanged(this)" style="height: calc(1.8125rem + 9px);">
          @foreach ($shippingProfiles as $profile)
              <option value="{{ $profile['id'] }}"> {{ $profile['profile_name'] }}</option>
          @endforeach
        </select>
        @endif
        </div>
        <div class="form-group mb-0  col-1 listing-checkbox no-brder">
          <label for="na_select_cat" class="container-check"><input type="checkbox"
              id="na_select_cat" class="na-select-cat">
            <span class="checkmark"></span>
          </label>
        </div>
    </div>
    @endif
    @endif
  </div>


  <!-- Table Starts -->
  <div class="row prdtbl" style="margin-top: 15px;">
    <div class="col-12 nopad">
      <div style="width:100%; min-width:1000px">
        <table class="table table-striped" style="margin-bottom: 37px;">
          <thead>
            <tr>
              <th scope="col small" style="padding:25px 0px 25px 8px!important;">

                <div class="form-group mb-0 listing-checkbox no-brder">
                  <label for="select_all" class="container-check mb-0" style="width: 98px; color:#2f2f2f!important; font-weight: 700;font-size: 1rem;  margin-top: 3px;padding-left:18px;"><input type="checkbox"
                      id="select_all"  style="width: 0px; padding-right: 5px;">&ensp;Select all 
                    <span class="checkmark"></span>
                  </label>
                </div>
              </th>
              <th scope="col small" style="padding-left:92px!important;">Product</th>
              <th scope="col small text-center">Price</th>
              <th scope="col small text-center">Type</th>
              <th scope="col small text-center">Woocommerce Category</th>
              <th scope="col small" style="text-align: center;">Mall Shark Category</th>
              <th scope="col small" style="text-align: center;">Shipping Option</th>
            </tr>
          </thead>
          <tbody>
            @if (count($products)>0)
            @php
                // dd($products);
                $counter = 0;
               
                @endphp
            @foreach ($products as $product)
            {{-- @if ($product->totalVariants == 1 && $product->variants->edges[0]->node->requiresShipping == true) --}}
            {{-- @if ($product->totalVariants > 0 ) --}}
                
            @php
                
                if($product->type == 'simple'){
                  // product level MANAGE STOCK for simple false
                  if(!$product->manage_stock){
                    if($product->stock_status == 'instock'){
                      $stock = 'instock';
                    }else{ 
                      $stock = 0; //out of stock
                    }
                  }else if($product->manage_stock){  //if MANAGE STOCK true
                    // $stock 
                    if($product->stock_status == 'instock'){
                      $stock = $product->stock_quantity;
                    }else{
                      $stock = 0;
                    }
                  }

                }else if($product->type == 'variable'){
                  $stock = 0;
                  
                  foreach ($product->variations_data as $key => $variation) {
                    //  count stock quantity of total variations, and check if there's any variation with instock
                    if($variation->stock_quantity && $variation->stock_quantity > 0){

                      $stock += $variation->stock_quantity;
                      // dump($stock);
                    }
                    if($variation->stock_status == 'instock'){
                      $inStock = true;
                    }
                    
                  }
                  // now check if quantity is less than 0 show instock else show quantity
                  
                  $stock = $stock > 0 ? $stock  :( $inStock ? 'instock' : 0);
                    
                  // dump($product->variations_data);
                }


                $sku = isset($product->sku) ? $product->sku : '';
                $category =  isset($product->category[0]) ? $product->category[0]->name : 'N/A';
                $categoryId =  isset($product->category[0]) ? $product->category[0]->id :null;
                if($categoryId){
                  
                  // $regex = '/\/(?=\d)/'; // matches the slash before the numbers
                  // $parts = preg_split($regex, $categoryId);
                  // $sCategoryIdKey = $parts[1];                
                  $sCategoryIdKey = $categoryId;                
                  // dd('call');    
                }else{
                  
                  $sCategoryIdKey = 0;                
                }
             
                
                $type =  $product->type  == 'variable' ? 'Variable' : 'Simple';
                @endphp
          
          <tr class="product-row">
              <th scope="row" style="padding-left:8px!important;">
                <div class="form-group mb-0 listing-checkbox no-brder">
                  <label for="select-import-{{ $counter }}" class="container-check"><input type="checkbox"
                    id="select-import-{{ $counter }}" class="select-import {{ $sCategoryIdKey }} {{ isset($product->category[0]) ? '' : 'NA'}}"  name="select-import" value="{{ $product->id }}">
                    <span class="checkmark"></span>
                  </label>
                </div>
              </th>
              <td>
                <div class="row" style="width:100%;">
                  @if (count($product->images)>0)
                      
                  <div class="col-3" style="max-height: 80px;min-height:80px;height:80px;"><img src="{{ $product->images[0]->src }}" width="64"></div>
                  @else
                  <div class="col-3" style="max-height: 80px;min-height:80px;height:80px;"><img src="" width="64"></div>
                  @endif
                  <div class="col-9">
                    <h4 style="font-size: 14px; margin:0; padding:0; margin-bottom:5px">{{ $product->title }}</h4>
                    <p class="small" style="color:#699f4c; margin-bottom:5px"><span>SKU: {{ $sku }}</span>
                      &nbsp;&nbsp;|&nbsp;&nbsp; <span>Stock: {{ $stock }}</span></p>
                   
                  </div>
                </div>
              </td>
              @php
              // has variants
              $currencyCode = "$";
              if($product->type == 'variable'){
                if($product->min_price == $product->max_price){
                  $price = "{$currencyCode} {$product->max_price}";
                }else{

                  $price = "{$currencyCode} {$product->min_price} - {$product->max_price} ";
                }
              }else{

                $price = $product->price;
                
                $price = " {$currencyCode} {$price}";
              }
              @endphp
              <td>{{ $price }}</td>
              <td>{{ $type }}</td>
           
              <td> {{ $category }}</td>
              <td style="width: 205px;">
                <div class="form-group input-group-sm " style="max-width:150px; margin-bottom:0">
                  
                  <select class="form-control category {{ $sCategoryIdKey }} {{ isset($product->category[0]) ? '' : 'NA'}}"   name="category" style="width: 197px;">
                    <option value="">Select Category</option>
                    @foreach ($categories as $category)
                      @if ($category->parent_id == 0) <!-- Check if category is a parent category -->
                        {{-- <optgroup label="{{ $category->categoryTitle }}"> --}}
                          <option value="{{ $category->id }}">{{ $category->categoryTitle }}</option>
                          {{-- @if ($category->childCategories)
                            @foreach ($category->childCategories as $childCategory)
                              <option value="{{ $childCategory->id }}"> -- {{ $childCategory->categoryTitle }}</option>
                              <!-- Add more nested levels as needed -->
                            @endforeach
                          @endif --}}
                        {{-- </optgroup> --}}
                      @endif
                    @endforeach
                  </select>
                </div>
              </td>
              <td style="width: 220px;">
                <div class="form-group input-group-sm " style="max-width:150px; margin-bottom:0">
                @if (count($shippingProfiles) > 0)
                    
                <select class="form-control profile {{ $sCategoryIdKey }} {{ isset($product->category[0]) ? '' : 'NA'}}" style="width: 197px;">
                  @foreach ($shippingProfiles as $profile)
                      <option value="{{ $profile['id'] }}"> {{ $profile['profile_name'] }}</option>
                  @endforeach
                </select>
                @endif
                </div>
              </td>
            </tr>
            @php
                $counter++;
            @endphp
              {{-- @endif     --}}
            @endforeach    
            @endif
            
          </tbody>
        </table>
      </div>
    </div>
    <input type="checkbox" class="wooCommerceCheckbox" style="width: 74%!important;">
    {{-- @if($stripe == 1 && $connected && !$foundError ) --}}
          <div class="text-right"><a class="btn btn-primary" id="addToMallshark"
              style="border:0px; background-color:#0510fa; color:#fff; border-radius:0px">Add Products To MallShark</a>
          </div>
          {{-- @endif --}}
  </div>
  <script>
      $('#select_all').on('change', function() {
    if ($(this).prop('checked')) {
      // Check all checkboxes with class "select-import"
      $('.select-import').prop('checked', true);
    } else {
      // Uncheck all checkboxes with class "select-import"
      $('.select-import').prop('checked', false);
    }
  });
      $('.select-cat').on('change', function() {
        let key = $(this).data('key');
        let checked = $(this).prop('checked')
        if(checked){
          $('.select-import.'+key).prop('checked',true)
        }else{
          $('.select-import.'+key).prop('checked',false)

        }
        
    
  });
      $('.na-select-cat').on('change', function() {
        let key = $(this).data('key');
        let checked = $(this).prop('checked')
        if(checked){
          $('.select-import.NA').prop('checked',true)
        }else{
          $('.select-import.NA').prop('checked',false)

        }
        
    
  });
  $('#addToMallshark').on('click',function(e){
    e.preventDefault();
    let errors = false;
    let selected = $('input.select-import:checked');
    let productsData = [];
    let counter = 0;
    let focusHere = null;
    if (selected.length === 0) {
            toastr.options = {
                closeButton: true,
                tapToDismiss: true,
                positionClass: 'toast-top-right',
                toastClass: 'toast-icon custom-toast-error',
                progressBar: true,
            };
            toastr.error('Please select at least one Product');
            return false; // Prevent form submission or other actions if needed
        }
    selected.each(function() {
  let productId = $(this).val();
  let categoryId = $(this).closest('tr.product-row').find('select.category').val();
  
  let profileId = $(this).closest('tr.product-row').find('select.profile').val();
  if(!categoryId){
    errors = true;
    
    if ($(this).closest('tr.product-row').find('select.category').next('.error').length === 0) {
      
     $(this).closest('tr.product-row').find('select.category').after('<span class="error">Select Category</span>');
      
    }
    if(counter == 0){
      
      focusHere = $(this).closest('tr.product-row'); 
      
      
    }
    
    counter++;
  }else{
    
    productsData.push({productId,categoryId,profileId});
  }
});

if(!errors && productsData.length > 0){
  $('#loading').show()
  $.ajax({
    url : "{{ route('saveWooProducts') }}",
    type: "POST",
    data : {productsData},
    success : function(response){
      $('#loading').hide()
      window.location.replace('/store-products')
    },
    error : function(error){
      $('#loading').hide()
    }
  })
  
}else{
  $('#loading').hide()
  $('html, body').scrollTop(focusHere.offset().top-100);
}
  


})

  </script>