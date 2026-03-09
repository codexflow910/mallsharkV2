<?php

namespace Packages\Order\Http\Requests;

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
       return [
            'status' => ['required', Rule::in(['refunded', 'cancelled', 'shipped'])],
            'order_id' => ['required', 'exists:store_orders,order_id'],
            'transaction' => [
                'nullable',
                'string',
                'alpha_num',
                'min:1',
                'max:40',
                Rule::requiredIf($this->status === 'shipped'),
            ],
            'carrier' => [
                'nullable',
                Rule::in(['ups', 'usps', 'fedx','dhl','others', 'amazon']),
                Rule::requiredIf($this->status === 'shipped'),
            ],
            'custom_url' => [
                'nullable',
                'url',
                Rule::requiredIf($this->carrier === 'others'),
            ]
        ];
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
