<?php

namespace Packages\Auth\Http\Requests\V2;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required',
            'email' => 'unique:users,email|required|email:rfc,dns',
            'contact_no' => 'nullable',
            'password' => 'required',
            'confirm_password' => 'same:password',
        ];
    }
    public function withValidator($validator)
    {
        if ($this->input('app_flag') === 'buyer') {
            $validator->addRules([
                'guest' => 'required',
                'id' => 'nullable',
            ]);
        }
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