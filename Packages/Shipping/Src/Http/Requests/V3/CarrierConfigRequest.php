<?php

namespace Packages\Shipping\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class CarrierConfigRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $carrier = strtolower($this->input('carrier'));

        $rules = [
            'carrier'             => ['required', Rule::in(['usps', 'fedex', 'shipstation'])],
        ];

        switch ($carrier) {
            case 'usps':
                $rules = array_merge($rules, [
                    'consumer_key'    => 'required|string',
                    'consumer_secret' => 'required|string',
                    'crid'            => 'required|string',
                    'mailer_id'       => 'required|string',
                    'eps_number'      => 'required|string',
                ]);
                break;

            case 'fedex':
                $rules = array_merge($rules, [
                    'consumer_key'    => 'required|string',
                    'consumer_secret' => 'required|string',
                    'account_number'  => 'required|string',
                ]);
                break;

            case 'shipstation':
                $rules = array_merge($rules, [
                    'consumer_key'    => 'required|string',
                    'consumer_secret' => 'required|string',
                ]);
                break;
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'carrier.in' => 'Carrier must be one of: usps, fedex, or shipstation.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status'  => false,
            'message' => 'Validation errors',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
