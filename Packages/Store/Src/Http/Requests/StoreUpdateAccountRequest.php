<?php

namespace Packages\Store\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
// use App\Rules\InternationalPhoneNumber;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreUpdateAccountRequest extends FormRequest
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
            'email' => 'required',
            // 'contact_no' => ['required', new InternationalPhoneNumber],
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
