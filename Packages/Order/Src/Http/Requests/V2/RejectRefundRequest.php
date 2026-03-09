<?php

namespace Packages\Order\Http\Requests\V2;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RejectRefundRequest extends FormRequest
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
            'order_id' => 'required|exists:store_orders,order_id',
            'status' => 'required|in:disputed',
            'is_reject' => 'required|boolean', 
            'is_refund' => 'required|string|in:dispute',
            'reject_order_reason' => 'required|string|max:1000',
            'process_items' => ['required', 'array', 'min:1'],
            'process_items.*.refund_item_id' => ['required', 'exists:order_items,order_item_id'],
            'process_items.*.variation_id' => ['sometimes', 'exists:order_items,variation_id'],
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
