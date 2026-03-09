<?php

namespace Packages\Product\Http\Requests\V2;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class ProductTierRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'product_id' => ['required', 'exists:products,product_id'],
            'tiers' => ['nullable', 'array'],
            'tiers.*.tier_id' => ['nullable', 'exists:product_tiers,id'],
            'tiers.*.min_quantity' => ['required', 'integer', 'min:1'],
            'tiers.*.max_quantity' => ['required', 'integer', 'min:1', 'gt:tiers.*.min_quantity'],
            'tiers.*.unit_price' => ['required', 'numeric', 'min:0'],
            'tiers.*.shipping_price' => ['required', 'numeric', 'min:0'],
            'tiers.*.variation_id' => ['nullable', 'exists:product_variation,variation_id'],
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
