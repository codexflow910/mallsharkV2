<?php

namespace Packages\Report\Http\Requests\V4;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class FinanceReportRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    
    public function rules()
    {
        return [
            'filter' => 'nullable|string|in:today,last_7_days,this_month,last_month,custom',
            'start_date' => 'nullable|required_if:filter,custom|date_format:Y-m-d',
            'end_date' => 'nullable|required_if:filter,custom|date_format:Y-m-d|after_or_equal:start_date',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => false,
            'message' => 'Validation errors',
            'errors' => $validator->errors()
        ]));
    }
}
