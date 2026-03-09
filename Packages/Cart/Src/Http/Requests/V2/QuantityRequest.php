<?php

namespace Packages\Cart\Http\Requests\V2;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class QuantityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'guest' => 'required',
            'id' => 'required',
            'product_id' => 'required|integer|exists:products,product_id',
            'product_quantity_minus' => 'required_without:product_quantity_plus|integer|min:1',
            'product_quantity_plus' => 'required_without:product_quantity_minus|integer|min:1',
            'store_id' => 'required|integer|exists:stores,store_id',
            'variation_id' => 'nullable|integer|exists:product_variation,variation_id',
        ];
    }
    
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('product_quantity_plus') && $this->has('product_quantity_minus')) {
                $validator->errors()->add('product_quantity_plus', 'Only one of product_quantity_plus or product_quantity_minus is allowed.');
            }
        });
    }

    protected function prepareForValidation(): void
    {
        // Convert guest to a proper boolean if passed as string (e.g., "false")
        if ($this->has('guest')) {
            $this->merge([
                'guest' => filter_var($this->guest, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
    
    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */ 

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'errors' => $validator->errors(),
        ]));
    }
}