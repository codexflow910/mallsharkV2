<?php

namespace Packages\Core\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class ShippingRateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules() {
        return [
            'buyer_postal_code' => 'required|string',
            'buyer_state_id' => 'required|integer|exists:states,state_id',
            'buyer_country_code' => 'required|string',
            'mail_class' => 'required|string|in:PARCEL_SELECT,USPS_GROUND_ADVANTAGE,PRIORITY_MAIL',
            
            'products' => 'required|array|min:1',
            'products.*.new_shipping' => 'required|boolean',
            'products.*.product_id' => 'required|integer|exists:products,product_id',
            'products.*.product_type' => 'required',
            'products.*.store_id' => 'required|integer|exists:stores,store_id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.weight' => 'required|numeric|min:0',
            'products.*.variation_id' => 'nullable|integer',
            'products.*.price' => 'required|numeric|min:0',
            'products.*.weight_unit' => 'required',

            'products.*.length' => 'nullable|numeric|min:0.01',
            'products.*.width' => 'nullable|numeric|min:0.01',
            'products.*.height' => 'nullable|numeric|min:0.01',
            'products.*.dimension_unit' => [
                'required',
                Rule::in(['cm', 'm', 'in', 'ft']),
            ],
            'shipping_option_id' => 'nullable|integer'
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
