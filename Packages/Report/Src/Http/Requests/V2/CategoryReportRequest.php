<?php

namespace Packages\Report\Http\Requests\V2;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CategoryReportRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    
    public function rules()
    {
        return [
            'category_ids' => 'array',
            'category_ids.*' => 'exists:categories,id',
            'filter' => 'nullable|string|in:year,last_month,last_30_days,this_month,last_7_days,custom,today,all',
            'start_date' => 'nullable|required_if:filter,custom|date_format:Y-m-d',
            'end_date' => 'nullable|required_if:filter,custom|date_format:Y-m-d|after_or_equal:start_date',
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
