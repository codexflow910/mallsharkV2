<?php

namespace Packages\Shipping\Http\Requests\V2;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class ShippingProfileRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {

        return [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'country_id' => 'integer|exists:countries,id',
            'state_id' => 'integer|exists:states,state_id',
            'zip_code' => 'required|string|max:20',
            'is_general' => 'required|boolean',

            'shipping_details' => 'nullable|array',

            // General shipping details
            'shipping_details.fixed_shipping' => 'nullable|array',
            'shipping_details.fixed_shipping.shipping_per_item' => 'nullable|boolean',
            'shipping_details.fixed_shipping.per_item_price' => 'required_if:shipping_details.fixed_shipping.shipping_per_item,1|numeric',
            'shipping_details.fixed_shipping.max_items_allowed_for_price' => 'required_if:shipping_details.fixed_shipping.shipping_per_item,1|integer',
            'shipping_details.fixed_shipping.additional_price' => 'required_if:shipping_details.fixed_shipping.shipping_per_item,1|numeric',
            'shipping_details.fixed_shipping.shipping_by_weight' => 'nullable|boolean',
            'shipping_details.fixed_shipping.base_price' => 'required_if:shipping_details.fixed_shipping.shipping_by_weight,1|numeric',
            'shipping_details.fixed_shipping.weight_unit' => 'required_if:shipping_details.fixed_shipping.shipping_by_weight,1|string',
            'shipping_details.fixed_shipping.base_price_per_weight' => 'required_if:shipping_details.fixed_shipping.shipping_by_weight,1|numeric',
            'shipping_details.fixed_shipping.max_weight_allowed_for_price' => 'required_if:shipping_details.fixed_shipping.shipping_by_weight,1|numeric',
            'shipping_details.fixed_shipping.additional_weight_price' => 'required_if:shipping_details.fixed_shipping.shipping_by_weight,1|numeric',
            'shipping_details.fixed_shipping.expected_delivery_time' => 'nullable|string',

            'shipping_details.carrier_shipping' => 'nullable|array',
            'shipping_details.carrier_shipping.carrier_id' => 'required_with:shipping_details.carrier_shipping|integer|exists:shipping_carriers,id',
            'shipping_details.carrier_shipping.additional_fee' => 'nullable|boolean',
            'shipping_details.carrier_shipping.fee_type' => 'required_if:shipping_details.carrier_shipping.additional_fee,1|in:fixed,percentage',
            'shipping_details.carrier_shipping.fee_value' => 'required_if:shipping_details.carrier_shipping.additional_fee,1|numeric',

            'shipping_details.free_shipping' => 'nullable|array',
            'shipping_details.free_shipping.free_shipping_time' => 'required_with:shipping_details.free_shipping|string',

            // Zones
            'zones' => 'required_if:is_general,0|array',
            'zones.*.name' => 'required|string|max:255',
            'zones.*.state_ids' => 'required|array|min:1',
            'zones.*.state_ids.*' => 'required|integer|exists:states,state_id',
            'zones.*.shipping_details' => 'nullable|array',

            // Zone - fixed_shipping
            'zones.*.shipping_details.fixed_shipping' => 'nullable|array',
            'zones.*.shipping_details.fixed_shipping.shipping_per_item' => 'nullable|boolean',
            'zones.*.shipping_details.fixed_shipping.per_item_price' => 'required_if:zones.*.shipping_details.fixed_shipping.shipping_per_item,1|numeric',
            'zones.*.shipping_details.fixed_shipping.max_items_allowed_for_price' => 'required_if:zones.*.shipping_details.fixed_shipping.shipping_per_item,1|integer',
            'zones.*.shipping_details.fixed_shipping.additional_price' => 'required_if:zones.*.shipping_details.fixed_shipping.shipping_per_item,1|numeric',
            'zones.*.shipping_details.fixed_shipping.shipping_by_weight' => 'nullable|boolean',
            'zones.*.shipping_details.fixed_shipping.base_price' => 'required_if:zones.*.shipping_details.fixed_shipping.shipping_by_weight,1|numeric',
            'zones.*.shipping_details.fixed_shipping.weight_unit' => 'required_if:zones.*.shipping_details.fixed_shipping.shipping_by_weight,1|string',
            'zones.*.shipping_details.fixed_shipping.base_price_per_weight' => 'required_if:zones.*.shipping_details.fixed_shipping.shipping_by_weight,1|numeric',
            'zones.*.shipping_details.fixed_shipping.max_weight_allowed_for_price' => 'required_if:zones.*.shipping_details.fixed_shipping.shipping_by_weight,1|numeric',
            'zones.*.shipping_details.fixed_shipping.additional_weight_price' => 'required_if:zones.*.shipping_details.fixed_shipping.shipping_by_weight,1|numeric',
            'zones.*.shipping_details.fixed_shipping.expected_delivery_time' => 'nullable|string',

            // Zone - carrier_shipping
            'zones.*.shipping_details.carrier_shipping' => 'nullable|array',
            'zones.*.shipping_details.carrier_shipping.carrier_id' => 'required_with:zones.*.shipping_details.carrier_shipping|integer|exists:shipping_carriers,id',
            'zones.*.shipping_details.carrier_shipping.additional_fee' => 'nullable|boolean',
            'zones.*.shipping_details.carrier_shipping.fee_type' => 'required_if:zones.*.shipping_details.carrier_shipping.additional_fee,1|in:fixed,percentage',
            'zones.*.shipping_details.carrier_shipping.fee_value' => 'required_if:zones.*.shipping_details.carrier_shipping.additional_fee,1|numeric',

            // Zone - free_shipping
            'zones.*.shipping_details.free_shipping' => 'nullable|array',
            'zones.*.shipping_details.free_shipping.free_shipping_time' => 'required_with:zones.*.shipping_details.free_shipping|string',
        ];

    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status'   => false,
            'message'   => 'Validation errors',
            'data'      => $validator->errors()
        ]));
    }
}
