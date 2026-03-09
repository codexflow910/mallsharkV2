<?php

namespace Packages\Review\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }


    public function rules()
    {
        return [

        'rating_count'         => 'required|integer|between:1,5',
        'comment'              => 'required|string|max:1000',

        'gallery'              => 'nullable|array',
        'gallery.*'            => 'integer',

        'deleted_gallery'      => 'nullable|array',
        'deleted_gallery.*'    => 'integer',

        'new_gallery_image'    => 'nullable|array',
        'new_gallery_image.*'  => 'image|mimes:jpg,jpeg,png,webp|max:2048',
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
