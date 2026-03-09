<?php

namespace Packages\Admin\Http\Requests;

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
            'maxquantitylimit' => $this->has('maxquantitylimit') ? $this->maxquantitylimit : null,
        ]);
    }
    public function rules()
    {
        return [


            'productTitle' => 'required|string',
            'productCategory' => 'required|array',
            'product_type' => 'required|string',
            'item_condition' => 'required|string',
            'productPrice' => 'required|string',
            'productcurrentstock' => 'required|string',
            'productSKU' => 'nullable|string',
            'feature_image' => 'required|string',
            'ProductvariationData' => 'nullable|array',
            'productVideoURL' => 'nullable|string',
            'productTags' => 'nullable|array',
            'productShortDescription' => 'required|string',
            'productDescription' => 'required|string',
            'weight_type'   => 'required|string',
            'productSize_length' => 'nullable|string',
            'productSize_width' => 'nullable|string',
            'productSize_height' => 'nullable|string',
            'dimention_type' => [
                Rule::requiredIf(function () {
                    return !empty($this->productSize_length) ||
                        !empty($this->productSize_height) ||
                        !empty($this->productSize_width);
                }),
                'string'
            ],
            'productWeight' => 'required|string',
            'faqQuestion' => 'array',
            'faqAnswer' => [
                'array',
                Rule::requiredIf(function () {
                    return count($this->faqAnswer) > 0;
                }),
            ],
            'productImages' => 'required|array',
            'delivery_days' => 'nullable|string',
            'maxquantitylimit' => 'nullable|string',

        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Validation errors',
            'data'      => $validator->errors()
        ]));
    }
}
