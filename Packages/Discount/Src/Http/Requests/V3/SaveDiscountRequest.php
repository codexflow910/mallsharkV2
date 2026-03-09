<?php

namespace Packages\Discount\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class SaveDiscountRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $couponId = $this->route('id'); 

        return [

            'discountActive' => 'required|in:Sales,Coupons',
            'autoDiscountTitle' => 'required_if:discountActive,Sales',
            'discountCode' => [
                'nullable','required_if:discountActive,Coupons',
            ],
            'discount_type' => 'required|in:percentage,fixed',
            'discount_applicable' => 'nullable|in:wholeInventory,product,category',
            
            // Applicable categories
            'applicable_categories' => 'nullable|array',
            'applicable_categories.*' => 'exists:categories,id',

            // Selective products with custom discount options
            'applicable_products' => 'nullable|array',
            'applicable_products.*.applicable_id' => 'required|numeric',
            'applicable_products.*.applicable_type' => 'required|in:product,variation',
            'applicable_products.*.custom_discount_type' => 'nullable|in:percent,fixed',
            'applicable_products.*.custom_discount_value' => 'nullable|numeric|min:1',

            'start_date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_date' => 'nullable|date',
            'end_time' => 'nullable|date_format:H:i',
            'min_purchase_amount_value' => 'nullable|numeric|min:1',
            'max_purchase_amount_value' => 'nullable|numeric|min:1',
            'min_quantity_value' => 'nullable|numeric|min:1',
            'max_quantity_value' => 'nullable|numeric|min:1',
            'percentage_value' => 'nullable|required_if:discount_type,percentage|numeric|min:0|max:100',
            'fixed_value' => 'nullable|required_if:discount_type,fixed|numeric|min:0',
            'totalUses' => 'nullable|integer|min:1',
            'one_use_per_customer' => 'nullable|boolean',
            //

        ];
    }

    public function failedValidation(Validator $validator)
    {

        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Validation errors',
            'errors'      => $validator->errors()
        ]));
    }
}
