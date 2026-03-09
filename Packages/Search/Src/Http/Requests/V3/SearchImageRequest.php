<?php

namespace Packages\Search\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class SearchImageRequest extends FormRequest
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
            'image' => 'required|file|image|mimes:jpeg,png,jpg,webp|max:5120',
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
