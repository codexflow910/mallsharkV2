<?php

namespace Packages\Auth\Http\Requests\V2;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateAddressRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'user_id' => 'numeric|exists:users,user_id',
            'city' => 'required',
            'user_country' => 'required',
            'user_state' => 'required|numeric|exists:states,state_id',
            'zip_code' => 'required',
            'street' => 'required',
            'apartment_type' => 'nullable|string|max:255',
            'apartment_number' => 'nullable|string|max:255',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'errors' => $validator->errors(),
        ]));
    }
}
