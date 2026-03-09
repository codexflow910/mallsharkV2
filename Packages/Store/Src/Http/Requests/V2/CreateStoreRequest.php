<?php

namespace Packages\Store\Http\Requests\V2;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class CreateStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    
    public function prepareForValidation()
    {
     
    }
    public function rules()
    {
        return [
        'store_name' => ['required', new \App\Rules\UniqueStoreName],
         'store_logo' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Single optional image
        'file_upload_banner' => 'nullable', // Can be single or multiple images
        'file_upload_banner.*' => 'required|file|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Each must be an image
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
