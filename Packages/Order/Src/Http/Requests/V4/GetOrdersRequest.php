<?php

namespace Packages\Order\Http\Requests\V4;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class GetOrdersRequest extends FormRequest
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
            'status' => 'nullable|in:pending,completed,shipped,refunded,dispute,cancelled',
            'order_id' => 'nullable|exists:store_orders,order_id',
            'From_Date' => ['date_format:m/d/Y'],
            'To_Date' => ['date_format:m/d/Y', 'after_or_equal:From_Date'],
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
