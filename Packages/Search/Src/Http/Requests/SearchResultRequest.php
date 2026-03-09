<?php

namespace Packages\Search\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class SearchResultRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function prepareForValidation()
    {
        $this->merge([

            'order' => $this->has('order') ? $this->order : 'asc',
            'page' => $this->has('page') ? $this->page : 1,
        ]);
    }
    public function rules()
    {
        return [
            'name' => 'nullable|string',
            'category' => 'nullable|string',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'page' => 'nullable',
            'on_sale' => 'nullable',
            'mallshark_selected' => 'nullable|numeric',
            'shipping_option_id' => 'nullable|numeric',
            'slug_deal' => 'nullable|string|in:welcome-deal,super-deal,summer-deal',
            'per_page' => 'nullable',
            'sort_by' => 'nullable',
            'order' => 'nullable|string'
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Validation errors',
            'data'      => $validator->errors()
        ]));
    }
}
