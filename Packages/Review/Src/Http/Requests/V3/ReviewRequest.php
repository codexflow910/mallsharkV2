<?php

namespace Packages\Review\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class ReviewRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }


    public function rules()
    {
        return [

            'rating_count' => 'required|numeric|min:1|max:5',
            'comment' => 'required|string|max:1000',
            'product_id' => 'required|exists:products,product_id',
            'order_id' => 'nullable|exists:store_orders,order_id',
            'email' => 'nullable|email|exists:users,email',
            'gallery' => 'nullable|array',
            'gallery.*' => 'nullable|image'
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
