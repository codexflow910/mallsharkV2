<?php

namespace Packages\Order\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Adjust authorization logic as necessary
    }

    /**
     * Define the validation rules.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'status' => ['required', Rule::in(['refunded', 'cancelled', 'shipped'])],
            'order_id' => ['required', 'exists:store_orders,order_id'],

            'process_items' => ['required', 'array', 'min:1'],
            'process_items.*.order_item_id' => ['required', 'exists:order_items,order_item_id'],
            'process_items.*.item_refund_amount' => ['required_if:status,cancelled', 'numeric', 'gt:0'],
            'process_items.*.transaction' => ['required_if:status,shipped', 'nullable', 'string', 'alpha_num', 'min:1', 'max:40'],
            'process_items.*.carrier' => ['required_if:status,shipped', Rule::in(['ups', 'usps', 'fedx', 'dhl', 'others', 'amazon'])],
        ];

        foreach ($this->input('process_items', []) as $index => $item) {
            if (($item['carrier'] ?? null) === 'others') {
                $rules["process_items.$index.custom_url"] = ['required', 'url'];
            } else {
                $rules["process_items.$index.custom_url"] = ['nullable', 'url'];
            }
        }

        return $rules;
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => false,
            'message' => 'Validation errors',
            'errors' => $validator->errors()
        ], 422));
    }
}
