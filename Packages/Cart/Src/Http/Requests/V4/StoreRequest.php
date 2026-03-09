<?php

namespace Packages\Cart\Http\Requests\V4;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreRequest extends FormRequest
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
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|integer|exists:products,product_id',
            'products.*.product_quantity' => 'required|integer|min:1',
            'products.*.store_id' => 'required|integer|exists:stores,store_id',
            'products.*.variation_options' => 'nullable|array',
        ];
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