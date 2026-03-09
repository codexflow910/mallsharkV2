<?php

namespace Packages\Klavio\Http\Requests\V2;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class KlavioRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    
    public function prepareForValidation()
    {
       
    }
    public function rules()
    {
      return [
        'email' => 'required|email',
        'first_name' => 'nullable|string',
        'last_name' => 'nullable|string',
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
