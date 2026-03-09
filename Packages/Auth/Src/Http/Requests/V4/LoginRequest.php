<?php

namespace Packages\Auth\Http\Requests\V4;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => 'required|email',
            'password' => 'required',
            'app_flag' => 'nullable',
            'fcm_token' => 'nullable',
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
