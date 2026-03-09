<?php

namespace Packages\Report\Http\Requests\V4;

use Illuminate\Foundation\Http\FormRequest;

class ShippingSummaryRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'filter' => 'nullable|in:today,last_7_days,this_month,last_month,custom',
            'start_date' => 'nullable|date_format:Y-m-d|required_if:filter,custom',
            'end_date' => 'nullable|date_format:Y-m-d|required_if:filter,custom|after_or_equal:start_date',
        ];
    }

    public function messages()
    {
        return [
            'filter.in' => 'Invalid filter. Allowed values: today, last_7_days, this_month, last_month, custom',
            'start_date.required_if' => 'start_date is required when using custom filter',
            'end_date.required_if' => 'end_date is required when using custom filter',
            'end_date.after_or_equal' => 'end_date must be after or equal to start_date',
        ];
    }
}
