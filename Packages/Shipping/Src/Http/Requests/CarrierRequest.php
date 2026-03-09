<?php

namespace Packages\Shipping\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class CarrierRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules() {
        return [
            'store_id' => 'required|integer|exists:stores,store_id',
            'shipping_carrier_id' => 'required|integer|exists:shipping_carriers,id',
            'config' => 'required|array', // add custom validation if needed
            'enabled' => 'boolean',
        ];
    }

    public function failedValidation(Validator $validator)
    {

        throw new HttpResponseException(response()->json([
            'status'   => false,
            'message'   => 'Validation errors',
            'errors'      => $validator->errors()
        ]));
    }
}
