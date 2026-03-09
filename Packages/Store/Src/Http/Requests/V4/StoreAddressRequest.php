<?php

namespace Packages\Store\Http\Requests\V4;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreAddressRequest extends FormRequest
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
            'street_address' => 'required',
            'store_state' => 'required|numeric|exists:states,state_id',
            'store_city' => 'required|regex:/^[\pL\s]+$/u',
            'zip_code' => 'required',
             'store_phone' => 'nullable|numeric|digits_between:10,15', // Phone number should be between 10-15 digits and numeric.
            'store_country' => 'nullable|numeric|exists:countries,country_code', // Country code should be numeric and exist in the 'countries' table.
            'about_store' => 'nullable|string', // About store should be a string (non-empty).
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
