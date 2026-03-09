<?php

namespace Packages\Product\Http\Requests\V2;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SyncVariationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'product_id' => ['required', 'exists:products,product_id'],

            'deleted_variations' => ['nullable', 'array'],
            'deleted_variations.*' => ['integer'],

            'tier_sets' => ['nullable', 'array'],
            'tier_sets.*' => ['nullable', 'array'],

            'variation_ids' => ['required', 'array'],
            'variation_ids.*' => ['integer', 'exists:product_variation,variation_id'],
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
