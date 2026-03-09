<style>
    .table td,
    .table th {
        vertical-align: baseline !important;
        border-top: 1px solid #dee2e6;
        padding: 0.5rem 2px !important;
    }
</style>

<div class="row prdcnt">
    <div class="col-12">
        <p style="margin:20px 0px 10px 6px;"><b>{{ count($products) }}</b> Products to be imported</p>
    </div>
    @if (count($products) > 0)
        <div class="row col-12 mt-10" style="margin-left:-4px;margin-bottom:10px;">
            <div class="col-4"><b>Shopify Category</b></div>
            <div class="col-4"><b>MallShark Categories</b></div>
            <div class="col-4"><b>Shipping Profile</b></div>
        </div>
        @foreach ($shopifyCategories as $key => $sCategory)
            <div class="row col-12 mt-10 " style="display: flex;align-items: center;">
                @php
                    $regex = '/\/(?=\d)/'; // matches the slash before the numbers
                    $parts = preg_split($regex, $key);
                    $sCategoryId = isset($parts[1]) ? $parts[1] : null;
                @endphp
                <div class="col-4">
                    <p key="{{ $key }}" style="margin-bottom: 0px;">{{ $sCategory }}</p>
                </div>
                <div class="col-4">
                    <select class="form-control sCategory" onchange="sCategoryChanged(this)"
                        data-key={{ $sCategoryId }}>
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
                        <select class="form-control profile" data-key="{{ $sCategoryId }}"
                            onchange="profileChanged(this)" style="height: calc(1.8125rem + 9px);">
                            @foreach ($shippingProfiles as $profile)
                                @if ($profile['delete_status'] == 1)
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
            <div class="row col-12 mt-10 mb-10"
                style="display: flex; align-items: center;margin-left:-4px;margin-bottom:20px!important;">
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

                        <select class="form-control profile" onchange="naProfileChanged(this)"
                            style="height: calc(1.8125rem + 9px);">
                            @foreach ($shippingProfiles as $profile)
                                @if ($profile['delete_status'] == 1)
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
<div class="row prdtbl">
    <div class="col-12 nopad">
        <div style="width:100%; min-width:1000px;overflow:hidden;overflow-x:auto;">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col small" style="padding:20px 6px!important;">

                            <div class="form-group mb-0 listing-checkbox no-brder">
                                <label for="select_all" class="container-check mb-0"
                                    style="width: 98px; font-weight: 700;font-size: 16px;color:#2f2f2f!important; padding-left:18px;  margin-top: 3px;"><input
                                        type="checkbox" id="select_all"
                                        style="width: 0px; padding-right: 5px;">&ensp;Select
                                    all
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                        </th>
                        <th scope="col small">Product</th>
                        <th scope="col small text-center">Price</th>
                        <th scope="col small text-center">Type</th>
                        <th scope="col smal text-center">Shopify Category</th>
                        <th scope="col small text-center">MallShark Category</th>
                        <th scope="col small text-center">Shipping Option</th>
                    </tr>
                </thead>
                <tbody>
                    @if (count($products) > 0)
                        @php
                            $counter = 0;
                        @endphp
                        @foreach ($products as $product)
                            {{-- @if ($product->totalVariants == 1 && $product->variants->edges[0]->node->requiresShipping == true) --}}
                            @php
                                $stock = $product['stock'];
                                $sku = $product['sku'];
                                $category = $product['category'];
                                $categoryId = null;
                                if ($categoryId) {
                                    $regex = '/\/(?=\d)/'; // matches the slash before the numbers
                                    $parts = preg_split($regex, $categoryId);
                                    $sCategoryIdKey = $parts[1];
                                } else {
                                    $sCategoryIdKey = 0;
                                }

                                $type = $product['type'];
                            @endphp

                            <tr class="product-row">
                                <th scope="row" style="padding-left:7px!important;">
                                    <div class="form-group mb-0 listing-checkbox no-brder">
                                        <label for="select-import-{{ $counter }}" class="container-check"><input
                                                type="checkbox" id="select-import-{{ $counter }}"
                                                class="select-import {{ $sCategoryIdKey }} {{ $category }}"
                                                name="select-import" value="{{ $product['handle'] }}">
                                            <input type="checkbox" id="select-import-{{ $counter }}"
                                                class="product-title {{ $sCategoryIdKey }} {{ $category }}"
                                                name="product-title" value="{{ $product['title'] }}">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </th>
                                <td>
                                    <div class="row" style="max-width:100%; position:relative;">
                                        @if (isset($product['img']))
                                            <div class="col-2"
                                                style="max-height: 100px;
                                            min-height: 100px;
                                            position: relative;
                                            top: 0;
                                            left: -70px;
                                            width: 247px;">
                                                <img src="{{ $product['img'] }}" width="64"
                                                    style="height: 63px; object-fit: contain;" />
                                            </div>
                                        @else
                                            <div class="col-2"
                                                style="max-height: 100px;
                                            min-height: 100px;
                                            height: 100px;
                                            position: relative;
                                            top: 0;
                                            left: -70px;
                                            width: 247px;">
                                                <img src="/assets/images/placeholder.jpg" width="64"
                                                    style="height: 63px; object-fit: contain;" />
                                            </div>
                                        @endif
                                        <div class="col-9"
                                            style="position:absolute; top:0; left:10px;max-width:100%;">
                                            <h4
                                                style="font-size: 14px; margin:0; padding:0; margin-bottom:5px; white-space: nowrap; 
                                            width: 100px; 
                                            overflow: hidden;   
                                            text-overflow: ellipsis; ">
                                                {{ $product['title'] }}</h4>
                                            <p class="small" style="color:#699f4c; margin-bottom:5px"><span>SKU:
                                                    {{ $sku }}</span>
                                                &nbsp;&nbsp;|&nbsp;&nbsp; <span>Stock: {{ $stock }}</span></p>

                                        </div>
                                    </div>
                                </td>
                                {{-- @php
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
  
                      @endphp --}}
                                <td style="min-width: 150px text-align:center;" style="padding-left: 1rem!important;">
                                    {{ $product['price'] }}</td>
                                <td class="text-center" style="padding-right: 1rem!important;">{{ $product['type'] }}
                                </td>
                                <td class="text-center"> {{ $category }}</td>
                                <td style="width:203px;">
                                    <div class="form-group input-group-sm "
                                        style="max-width:150px; margin-bottom:0;min-width:150px;">

                                        <select
                                            class="form-control category {{ $sCategoryIdKey }} {{ $product['category'] }}"
                                            name="category" style="width: 197px;">
                                            <option value="">Select Category</option>
                                            @foreach ($categories as $category)
                                                @if ($category->parent_id == 0)
                                                    <!-- Check if category is a parent category -->
                                                    {{-- <optgroup label="{{ $category->categoryTitle }}"> --}}
                                                    <option value="{{ $category->id }}">
                                                        {{ $category->categoryTitle }}</option>
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
                                    <div class="form-group input-group-sm "
                                        style="max-width:150px; margin-bottom:0;min-width:150px;">
                                        @if (count($shippingProfiles) > 0)
                                            @php
                                                $weight = $product['weight'] ? $product['weight'] : 0;
                                                if (!$weight || empty($weight)) {
                                                    $weight = 0;
                                                }
                                                // dump($weight);
                                            @endphp
                                            {{-- @dd($shippingProfiles) --}}
                                            <select
                                                class="form-control {{ $weight == 0 ? 'no-weight' : '' }} profile {{ $sCategoryIdKey }} {{ $product['category'] ? $product['category'] : 'NA' }}"
                                                style="width: 197px;">
                                                @foreach ($shippingProfiles as $profile)
                                                    {{-- @if ($profile['delete_status'] == 1) --}}
                                                    @if (is_null($profile['user_id']))
                                                        <option value="{{ $profile['id'] }}">
                                                            {{ $profile['profile_name'] }}</option>
                                                        {{-- @break --}}
                                                    @else
                                                        <option value="{{ $profile['id'] }}">
                                                            {{ $profile['profile_name'] }}</option>
                                                    @endif
                                                    {{-- @endif --}}
                                                @endforeach
                                            </select>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @php
                                $counter++;
                            @endphp
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    <input type="checkbox" class="shopifyCheckBox" style="width: 73%!important; display:none;">
    {{-- @if (Session::has('userData') && isset(Session::get('userData')['accessToken']) && isset(Session::get('userData')['shopUrl'])) --}}
    <div class="text-right" style="width:100%;margin-right:32px;min-width:986px;margin-bottom:11px;"><a
            class="btn btn-primary" id="addToMallshark"
            style="border:0px; background-color:#0510fa; color:#fff; border-radius:0px">Add
            Products To MallShark</a>
    </div>
    {{-- @endif --}}
</div>
<script>
    var uploadedFilePath = "{{ $fileName }}";
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
        if (checked) {
            $('.select-import.' + key).prop('checked', true)
        } else {
            $('.select-import.' + key).prop('checked', false)

        }


    });
    $('.na-select-cat').on('change', function() {
        let key = $(this).data('key');
        let checked = $(this).prop('checked')
        if (checked) {
            $('.select-import.NA').prop('checked', true)
        } else {
            $('.select-import.NA').prop('checked', false)

        }
    });
    $('#addToMallshark').on('click', function(e) {
        $('#loading').show()
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
            $('#loading').hide()
            return false; // Prevent form submission or other actions if needed
        }
        selected.each(function() {
            let productHandle = $(this).val();
            let categoryId = $(this).closest('tr.product-row').find('select.category').val();
            let title = $(this).closest('tr.product-row').find('.product-title').val();
            let profileId = $(this).closest('tr.product-row').find('select.profile').val();
            if (!categoryId) {
                errors = true;
                if ($(this).closest('tr.product-row').find('select.category').next('.error').length ===
                    0) {
                    $(this).closest('tr.product-row').find('select.category').after(
                        '<span class="error">Select Category</span>');


                }
                if (counter == 0) {
                    focusHere = $(this).closest('tr.product-row');
                }
                counter++;
            } else {
                productsData.push({
                    productHandle,
                    categoryId,
                    profileId,
                    title

                });
            }
        });

        if (!errors && productsData.length > 0) {

            // debugger
            //     $('#addToMallshark').prop('disabled', true);
            //   var totalProducts = productsData.length;
            //   var successfulSaves = 0;
            //   setTimeout(() => {
            //   productsData.forEach(function(product) {
            //     $.ajax({
            //         url: "{{ route('save.csv.products') }}",
            //         type: "POST",
            //         data: {
            //             product,
            //             uploadedFilePath

            //         },
            //         async: false,
            //         success: function(response) {
            //           console.log(successfulSaves)
            //           successfulSaves++;
            //           if (successfulSaves == totalProducts) {
            //             $('#loading').hide();
            //         //   window.location.replace('/store-products');
            //         window.location.href = '/store-products';
            //           }

            //         },
            //         error: function(error) {
            //           console.log(error);
            //             $('#loading').hide();
            //             window.location.reload();
            //             $('.fileDataError').removeClass('d-none');
            //             $('#addToMallshark').prop('disabled', false);
            //         }
            //     });

            //   });
            // }, 1000);
            var totalProducts = productsData.length;
            var successfulSaves = 0;

            // Function to send AJAX request for a single product
            function saveProduct(product) {
                return new Promise(function(resolve, reject) {
                    $.ajax({
                        url: "{{ route('save.csv.products') }}",
                        type: "POST",
                        data: {
                            product: product,
                            uploadedFilePath: uploadedFilePath
                        },
                        success: function(response) {
                            successfulSaves++;
                            console.log("Product saved:", product);
                            resolve(response);
                        },
                        error: function(error) {
                            console.error("Error saving product:", product, error);
                            reject(error);
                        }
                    });
                });
            }

            // Array to hold promises for all AJAX requests
            var promises = [];

            // Loop through each product and send AJAX request asynchronously
            productsData.forEach(function(product) {
                promises.push(saveProduct(product));
            });

            // Wait for all promises to be resolved or rejected
            Promise.all(promises)
                .then(function() {
                    console.log("All products saved successfully.");
                    $('#loading').hide();
                    window.location.href = '/store-products';
                })
                .catch(function(error) {
                    console.error("Error saving products:", error);
                    $('#loading').hide();
                 //   window.location.reload();
                    $('.fileDataError').removeClass('d-none');
                    $('#addToMallshark').prop('disabled', false);
                });

        } else {
            $('#loading').hide()
            $('html, body').scrollTop(focusHere.offset().top - 100);
        }
    })

    function sCategoryChanged(e) {
        let sCategoryId = e.dataset.key;
        let mCategoryId = e.value;
        if (sCategoryId && mCategoryId) {
            $('select.category.' + sCategoryId).each(function() {
                $(this).val(mCategoryId)
            });


        }

    }

    function naCategoryChanged(e) {
        let mCategoryId = e.value;
        if (mCategoryId) {
            $('select.category.NA').each(function() {
                $(this).val(mCategoryId)
            });


        }

    }

    function profileChanged(e) {
        let sKey = e.dataset.key;
        let mProfileId = e.value;
        if (sKey && mProfileId) {
            let set = $('select.profile.' + sKey).each(function() {
                // if (!$(this).hasClass('no-weight')) {

                $(this).val(mProfileId)
                // }
            })

        }
    }

    function naProfileChanged(e) {

        let mProfileId = e.value;
        console.log(mProfileId);
        if (mProfileId) {

            let set = $('select.profile.NA').each(function() {
                // if ($(this).hasClass('no-weight')) {

                $(this).val(mProfileId)
                // }
            })

        }
    }
</script>
