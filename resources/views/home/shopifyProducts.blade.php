<style>
        .table td, .table th {
        vertical-align: baseline !important;
        border-top: 1px solid #dee2e6;
        padding: 0.5rem 2px !important;
}
.shopifyScroller::-webkit-scrollbar {
  width: 20px;
  margin-top:10px;
}

.shopifyScroller::-webkit-scrollbar-track {
  box-shadow: inset 0 0 5px grey; 
  border-radius: 10px;
}
 
.shopifyScroller::-webkit-scrollbar-thumb {
  background: rgba(0,0,0,.05);
  border-radius: 10px;
}
</style>

<div class="row prdcnt">
  <div class="col-12">
    <p style="margin: 25px 0px 25px 6px;"><b>{{ count($products) }}</b> Products to be imported</p>
  </div>
  @if(count($products)>0)

  <div class="row col-12 mb-3" style="margin-left: -4px;">
    <div class="col-4"><b>Shopify Category</b></div>
    <div class="col-4"><b>MallShark Categories</b></div>
    <div class="col-4"><b>Shipping Profile</b></div>
  </div>
  @foreach ($shopifyCategories as $key=> $sCategory)
  <div class="row col-12 mt-10 " style="display: flex;align-items: center; margin-left:-4px;">
    @php
    $regex = '/\/(?=\d)/'; // matches the slash before the numbers
    $parts = preg_split($regex, $key);
    $sCategoryId = $parts[1];
    @endphp
    <div class="col-4">
      <p key="{{ $key }}"  style="margin-bottom: 0px;">{{ $sCategory }}</p>
    </div>
    <div class="col-4">
      <select class="form-control sCategory" onchange="sCategoryChanged(this)" data-key={{ $sCategoryId }} >
        <option value="">Select Category</option>
        @foreach ($categories as $category)
        @if ($category->parent_id == 0)
        <!-- Check if category is a parent category -->
        {{-- <optgroup label="{{ $category->categoryTitle }}"> --}}
          <option value="{{ $category->id }}">{{ $category->categoryTitle }}</option>
          {{-- @if ($category->childCategories)
          @foreach ($category->childCategories as $childCategory)
          <option value="{{ $childCategory->id }}"> -- {{ $childCategory->categoryTitle }}</option>
          <!-- Add more nested levels as needed -->
          @endforeach
          @endif --}}
          {{--
        </optgroup> --}}
        @endif
        @endforeach
      </select>
    </div>
    <div class="form-group input-group-sm col-3 " style=" margin-bottom:0;">
      @if (count($shippingProfiles) > 0)

      <select class="form-control profile" data-key="{{ $sCategoryId }}" onchange="profileChanged(this)" style="height: calc(1.8125rem + 9px);">
        @foreach ($shippingProfiles as $profile)
        @if($profile['delete_status'] == 1)
        <option value="{{ $profile['id'] }}"> {{ $profile['profile_name'] }}</option>
        @endif
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
  <div class="row col-12 mt-10 mb-10" style="display: flex; align-items: center;margin-left:-4px;">
    <div class="col-4">
      <p class="mb-0">Uncategorized</p>
    </div>
    <div class="col-4">
      <select class="form-control sCategory" onchange="naCategoryChanged(this)">
        <option value="">Select Category</option>
        @foreach ($categories as $category)
        @if ($category->parent_id == 0)
        <!-- Check if category is a parent category -->
        {{-- <optgroup label="{{ $category->categoryTitle }}"> --}}
          <option value="{{ $category->id }}">{{ $category->categoryTitle }}</option>
          {{-- @if ($category->childCategories)
          @foreach ($category->childCategories as $childCategory)
          <option value="{{ $childCategory->id }}"> -- {{ $childCategory->categoryTitle }}</option>
          <!-- Add more nested levels as needed -->
          @endforeach
          @endif --}}
          {{--
        </optgroup> --}}
        @endif
        @endforeach
      </select>
    </div>
    <div class="form-group input-group-sm col-3" style=" margin-bottom:0">
      @if (count($shippingProfiles) > 0)

      <select class="form-control profile" onchange="naProfileChanged(this)" style="height: calc(1.8125rem + 9px);">
        @foreach ($shippingProfiles as $profile)
        @if($profile['delete_status'] == 1)
        <option value="{{ $profile['id'] }}"> {{ $profile['profile_name'] }}</option>
        @endif
        @endforeach
      </select>
      @endif
    </div>
    <div class="form-group mb-0  col-1 listing-checkbox no-brder">
      <label for="na_select_cat" class="container-check"><input type="checkbox" id="na_select_cat"
          class="na-select-cat">
        <span class="checkmark"></span>
      </label>
    </div>
  </div>
  @endif
  @endif
</div>


<!-- Table Starts -->
<div class="row prdtbl" style="margin-top: 15px;">
  <div class="shopifyScroller" style="overflow: auto;">
  <div class="col-12 nopad">
    <div style="width:100%; min-width:1000px">
      <table class="table table-striped" style="margin-bottom: 37px;">
        <thead>
          <tr>
            <th scope="col small" style="padding: 25px 0px 25px 8px!important;">
              <div class="form-group mb-0 listing-checkbox no-brder">
                <label for="select_all" class="container-check mb-0" style="width: 98px; font-weight: 700; color:#2f2f2f!important; font-size: 1rem;  margin-top: 3px; padding-left:18px;"><input type="checkbox"
                    id="select_all"  style="width: 0px; padding-right: 5px;">&ensp;Select all 
                  <span class="checkmark"></span>
                </label>
              </div>
            </th>
            <th scope="col small" >Product</th>
            <th scope="col small text-center">Price</th>
            <th scope="col small text-center">Type</th>
            <th scope="col small text-center">Shopify Category</th>
            <th scope="col small text-center">MallShark Category</th>
            <th scope="col small text-center">Shipping Option</th>
          </tr>
        </thead>
        <tbody>
          @if (count($products)>0)
          @php

          $counter = 0;

          @endphp
          @foreach ($products as $product)
          {{-- @if ($product->totalVariants == 1 && $product->variants->edges[0]->node->requiresShipping == true) --}}
          @if ($product->totalVariants > 0 )

          @php

          $stock = isset($product->totalInventory) ? $product->totalInventory : 0;
          $sku = isset($product->variants) ? $product->variants->edges[0]->node->sku : '';
          $category = isset($product->productCategory) ? $product->productCategory->productTaxonomyNode->name : 'N/A';
          $categoryId = isset($product->productCategory) ? $product->productCategory->productTaxonomyNode->id :null;
          if($categoryId){

          $regex = '/\/(?=\d)/'; // matches the slash before the numbers
          $parts = preg_split($regex, $categoryId);
          $sCategoryIdKey = $parts[1];
          }else{

          $sCategoryIdKey = 0;
          }

          $type = $product->totalVariants > 1 ? 'Variable' : 'Simple';
          @endphp

          <tr class="product-row">
            <th scope="row" style="padding-left: 8px!important;">
              <div class="form-group mb-0 listing-checkbox no-brder">
                <label for="select-import-{{ $counter }}" class="container-check"><input type="checkbox"
                    id="select-import-{{ $counter }}"
                    class="select-import {{ $sCategoryIdKey }} {{ $product->productCategory ? '' : 'NA'}}"
                    name="select-import" value="{{ $product->id }}">
                  <span class="checkmark"></span>
                </label>
              </div>
            </th>
            <td>
              <div class="row" style="max-width:100%; position:relative;">
                @if (count($product->images->nodes)>0)

                <div class="2" style="max-height: 100px;
                min-height: 100px;
                height: 100px;
                position: relative;
                top: 0;
                left: -50px;
                width: 247px;"><img src="{{ $product->images->nodes[0]->url }}" width="64" style="height: 63px; object-fit: contain;"/></div>
                @else
                <div class="col-2" style="max-height: 100px;
                min-height: 100px;
                height: 100px;
                position: relative;
                top: 0;
                left: -70px;
                width: 247px;"><img src="/assets/images/placeholder.jpg" width="64" style="height: 63px; object-fit: contain;"/></div>
                @endif
                <div class="col-9 " style="position:absolute; top:0; left:10px;max-width:100%;">
                  <h4 style="font-size: 14px; margin:0; padding:0; margin-bottom:5px; white-space: nowrap; 
                  width: 100px; 
                  overflow: hidden;   
                  text-overflow: ellipsis; ">{{ $product->title }}</h4>
                  <p class="small" style="color:#699f4c; margin-bottom:5px"><span>SKU: {{ $sku }}</span>
                    &nbsp;&nbsp;|&nbsp;&nbsp; <span>Stock: {{ $stock }}</span></p>

                </div>
              </div>
            </td>
            @php
            // has variants
            // $currencyCode = $product->priceRangeV2->minVariantPrice->currencyCode;
            $currencyCode = "$";
            if($product->totalVariants > 1){

            $minPrice = $product->priceRangeV2->minVariantPrice->amount;
            $maxPrice = $product->priceRangeV2->maxVariantPrice->amount;

            if($maxPrice == $minPrice){
            $price = "{$currencyCode}{$minPrice} ";
            }else{
            $price = "{$currencyCode}{$minPrice} - {$currencyCode}{$maxPrice} ";
            }
            }
            // has no variants
            else if($product->totalVariants > 0){
            $price = $product->variants->edges[0]->node->price ? $product->variants->edges[0]->node->price : null;
            $price = "{$currencyCode}{$price}";

            }

            // dump($product);

            @endphp
            <td style="min-width: 150px" style="padding-left: 1rem!important;">{{ $price }}</td>
            <td style="padding-right: 1rem!important;">{{ $type }}</td>
            <td> {{ $category }}</td>
            <td style="width:203px;">
              <div class="form-group input-group-sm " style="max-width:150px; margin-bottom:0;min-width:150px;">

                <select class="form-control category {{ $sCategoryIdKey }} {{ $product->productCategory ? '' : 'NA'}}"
                  name="category" style="width: 197px;">
                  <option value="">Select Category</option>
                  @foreach ($categories as $category)
                  @if ($category->parent_id == 0)
                  <!-- Check if category is a parent category -->
                  {{-- <optgroup label="{{ $category->categoryTitle }}"> --}}
                    <option value="{{ $category->id }}">{{ $category->categoryTitle }}</option>
                    {{-- @if ($category->childCategories)
                    @foreach ($category->childCategories as $childCategory)
                    <option value="{{ $childCategory->id }}"> -- {{ $childCategory->categoryTitle }}</option>
                    <!-- Add more nested levels as needed -->
                    @endforeach
                    @endif --}}
                    {{--
                  </optgroup> --}}
                  @endif
                  @endforeach
                </select>
              </div>
            </td>
            <td style="width: 220px;"> 
              <div class="form-group input-group-sm " style="max-width:150px; margin-bottom:0;min-width:150px;">
                @if (count($shippingProfiles) > 0)


                @php
                $weight = $product->variants->edges[0]->node->weight ? $product->variants->edges[0]->node->weight : 0;
                if(!$weight || empty($weight)){
                  $weight = 0;
                }
                // dump($weight);
                @endphp
                {{-- @dd($shippingProfiles[0]['delete_status']) --}}
                <select class="form-control {{$weight == 0 ? 'no-weight' : ''}} profile {{ $sCategoryIdKey }} {{ $product->productCategory ? '' : 'NA'}}" style="width: 197px;" >
                  @foreach ($shippingProfiles as $profile)
                  
                  @if($profile['delete_status'] == 1)
                  @if (is_null($profile['user_id']))
                  <option value="{{ $profile['id'] }}"> {{ $profile['profile_name'] }}</option>
                  {{-- @break --}}
                  @else
                  <option value="{{ $profile['id'] }}"> {{ $profile['profile_name'] }}</option>
                  @endif
                  @endif
                  @endforeach
                </select>
                @endif
              </div>
            </td>
          </tr>
          @php
          $counter++;
          @endphp
          @endif
          @endforeach
          @endif

        </tbody>
      </table>
    </div>
  </div>
  <input type="checkbox" class="shopifyCheckBox" style="width: 74%!important;">
  {{-- @if(Session::has('userData') && isset(Session::get('userData')['accessToken']) && isset(Session::get('userData')['shopUrl'])) --}}
  <div class="text-right" style="width:99.2%;min-width:986px;margin-bottom:11px;">
    <div class="text-right" style="width:99.2%;min-width:986px;margin-bottom:11px;">
      <button class="btn btn-primary" id="addToMallshark" style="border:0px; background-color:#0510fa; color:#fff; border-radius:0px">
          Add Products To MallShark
      </button>
  </div>
  
  </div>
  {{-- @endif --}}
</div>
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
  $('#addToMallshark').on('click', function(e) {
        
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
              // debugger
              focusHere = $(this).closest('tr.product-row'); 
              
              
            }
            
            counter++;
          }else{
            
            productsData.push({productId,categoryId,profileId});
          }
        });
        if (!errors && productsData.length > 0) {
            // debugger
            $('#loading').show()
            $('#addToMallshark').prop('disabled', true);

          var totalProducts = productsData.length;
          var successfulSaves = 0;
          setTimeout(() => {
            $.ajax({
                url : "{{ route('saveImportedProducts') }}",
                type: "POST",
                data : {productsData},
                success : function(response){
                  $('#loading').hide()
                  window.location.replace('/store-products')
                },
                
                error : function(error){
                  $('#loading').hide();
                }
              })
        }, 1000);
        }else {
            $('#loading').hide()
            $('html, body').scrollTop(focusHere.offset().top - 100);
        }
    })

//   $('#addToMallshark').on('click',function(e){
//     e.preventDefault();
//     $('#loading').show()
//     let errors = false;
//     let selected = $('input.select-import:checked');
//     let productsData = [];
//     let counter = 0;
//     let focusHere = null;
//     selected.each(function() {
//   let productId = $(this).val();
//   let categoryId = $(this).closest('tr.product-row').find('select.category').val();
  
//   let profileId = $(this).closest('tr.product-row').find('select.profile').val();
//   if(!categoryId){
//     errors = true;
    
//     if ($(this).closest('tr.product-row').find('select.category').next('.error').length === 0) {
      
//      $(this).closest('tr.product-row').find('select.category').after('<span class="error">Select Category</span>');
      
//     }
//     if(counter == 0){
//       // debugger
//       focusHere = $(this).closest('tr.product-row'); 
      
      
//     }
    
//     counter++;
//   }else{
    
//     productsData.push({productId,categoryId,profileId});
//   }
// });

// if(!errors && productsData.length > 0){
  
//   $.ajax({
//     url : "{{ route('saveImportedProducts') }}",
//     type: "POST",
//     data : {productsData},
//     success : function(response){
//       $('#loading').hide()
//       window.location.replace('/store-products')
//     },
//     error : function(error){
//       $('#loading').hide();
//       window.location.reload();
//     }
//   })
  
// }else{
//   $('#loading').hide()
//   $('html, body').scrollTop(focusHere.offset().top-100);
// }
  


// })
</script>