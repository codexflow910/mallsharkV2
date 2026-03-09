<?php

namespace Packages\Product\Http\Requests\V2;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class VariationsStore extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function prepareForValidation()
    {
        $this->merge([]);
    }

    public function rules()
    {
        return [
            'option1' => 'required_if:attribute1,!=,[]|array|min:1',
            'option1.*' => 'required_if:attribute1,!=,[]|string',
            'attribute1' => 'required|array|min:1',
            'attribute1.*' => 'required|string',
            'unit1' => 'required_if:attribute1,!=,[]|array|min:1',
            'unit1.*' => 'nullable|string',
            'option2' => 'required_if:attribute2,!=,[]|array|min:1',
            'option2.*' => 'required_if:attribute2,!=,[]|string',
            'attribute2' => 'required|array|min:1',
            'attribute2.*' => 'required|string',
            'unit2' => 'required_if:attribute2,!=,[]|array|min:1',
            'unit2.*' => 'required|string',
            'option3' => 'required_if:attribute3,!=,[]|array|min:1',
            'option3.*' => 'nullable|string',
            'attribute3' => 'required|array',
            'unit3' => 'required|array',
            'price' => 'required|array|min:1',
            'price.*' => 'required|numeric',
            'sku' => 'required|array|min:1',
            'sku.*' => 'required|string',
            'current_stock' => 'required|array|min:1',
            'current_stock.*' => 'required|integer',
            'product_variation_image' => 'required|array|min:1',
            'product_variation_image.*' => 'required|string',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $option1Count = count($this->input('option1', []));
            $option2Count = count($this->input('option2', []));
            $option3Count = count($this->input('option3', []));

            if ($option1Count !== $option2Count || $option1Count !== $option3Count) {
                $validator->errors()->add('option_count', 'Options 1, 2, and 3 must have the same number of elements.');
            }
        });
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
