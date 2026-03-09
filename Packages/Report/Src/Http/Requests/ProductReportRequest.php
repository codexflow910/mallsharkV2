<?php

namespace Packages\Report\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProductReportRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    
    public function rules()
    {
        return [
            'filter' => 'nullable|string|in:year,last_month,this_month,last_7_days,last_90_days,custom,todaylast_month,this_month,last_7_days,last_90_days,custom,today',
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
