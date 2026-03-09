<?php

namespace Packages\Product\Http\Requests\V2;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function prepareForValidation()
    {
        $this->merge([

            'delivery_days' => $this->has('delivery_days') ? $this->delivery_days : null,
            'maxquantitylimit' => $this->has('maxquantitylimit') ? $this->maxquantitylimit : null
        ]);
    }
    public function rules()
    {
        return [
            'productTitle' => 'required|string',
            'slug' => 'nullable|string',
            'productCategories' => 'required|array',
            'product_type' => 'required|string',
            'item_condition' => 'required|string',          
            'productPrice' => 'nullable|required_if:product_type,simple|string',
            'productcurrentstock' => 'nullable|required_if:product_type,simple|string',
            'productSKU' => 'nullable|string',
            'HS_code' => 'nullable|string',
            'feature_image' => 'required',
            'ProductvariationData' => 'nullable|array',
            'productVideoURL' => 'nullable|string',
            'productTags' => 'nullable|array',
            'productShortDescription' => 'required|string',
            'productDescription' => 'required|string',
           'weight_type'   => 'nullable|string',
           'productSize_length' => 'nullable|string',
           'productSize_width' => 'nullable|string',
           'productSize_height' => 'nullable|string',
            'featureImageAlt' => 'array',
            'featureImageAlt.*' => 'string|nullable',
            'productImagesAlt' => 'array',
            'productImagesAlt.*' => 'string|nullable',
           'dimention_type' => [
               Rule::requiredIf(function () {
                   return !empty($this->productSize_length) ||
                       !empty($this->productSize_height) ||
                       !empty($this->productSize_width);
               }),
               'string'
           ],
           'productWeight' => 'nullable|string',
            'faqQuestions' => 'nullable|array',
            'faqAnswers' => [
                'array',
                Rule::requiredIf(function () {
                    return is_array($this->faqQuestions) && count($this->faqQuestions) > 0;
                }),
            ],
            'productImages' => 'required|array',
            'delivery_days' => 'nullable|string',
            'maxquantitylimit' => 'nullable|string',
            'shipping_option_id' => 'nullable|array',
            'new_shipping' => 'required|boolean',
            'specification_titles' => 'nullable|array',
            'specification_values' => 'nullable|array',
            'brand_distributor_id' => 'nullable|exists:brand_distributors,id',
            'brand_id' => 'nullable|exists:brands,id',
            'product_variation_deleted' => 'nullable|array',
            'unique_product' => 'boolean',
            'is_custom' => 'boolean',
            'allow_text' => 'boolean',
            'buyer_text_limit' => 'nullable|integer',
            'allow_image' => 'boolean',
            'custom_price' => 'nullable|numeric',
            'text_styles' => 'nullable|array',
            'text_styles.*.style_name' => 'required|string',
            'text_styles.*.id' => 'nullable|integer',
            'text_styles.*.options' => 'required|array',
            'text_styles.*.options.*.id' => 'nullable|integer',
            'text_styles.*.options.*.value' => 'required|string',
            'image_settings.min_width' => 'nullable|integer',
            'image_settings.min_height' => 'nullable|integer',
        ];
    }

    public function failedValidation(Validator $validator)
    {

        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Validation errors',
            'errors'      => $validator->errors()
        ]));
    }
}
