<?php

namespace Packages\Distributors\Http\Requests\V4;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    
    public function rules()
    {
        return [
            'brand_name'       => 'required|string|max:255',
            'brand_url'        => 'required|string',
            'expiry_date'      => 'required|date|after:today',
            'distributor_logo' => 'required|file|mimes:png,jpg,jpeg,webp|max:5120',
            'documents'        => 'required|array',
            'documents.*'      => 'file|mimes:png,jpg,jpeg,pdf,webp|max:5120',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'data'    => $validator->errors(),
        ], 422));
    }
}
