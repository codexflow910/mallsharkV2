<?php

namespace Packages\Distributors\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    
    public function rules()
    {
        return [
            'brand_name'       => 'required|string|max:255',
            'brand_url'        => 'required|string',
            'expiry_date'      => 'required|date|after:today',
            'deleted_documents' => 'nullable|array',
            'deleted_documents.*' => 'integer|exists:attachment,attachment_id',


            // ✅ Allow both file upload or existing image ID
            'distributor_logo' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (is_numeric($value)) {
                        return true; // existing ID
                    }

                    if ($value instanceof \Illuminate\Http\UploadedFile) {
                        $mime = $value->getMimeType();
                        $maxSize = 5120 * 1024; // 5MB

                        // Allow any image type
                        if (!str_starts_with($mime, 'image/')) {
                            $fail("The {$attribute} must be an image file.");
                        } elseif ($value->getSize() > $maxSize) {
                            $fail("The {$attribute} must not be greater than 5MB.");
                        }

                        return true;
                    }

                    $fail("The {$attribute} must be a valid file or an existing attachment ID.");
                },
            ],

            'documents'   => 'required|array',
            'documents.*' => [
                'sometimes',
                function ($attribute, $value, $fail) {
                    if (is_numeric($value)) {
                        return true; // existing doc ID
                    }

                    if ($value instanceof \Illuminate\Http\UploadedFile) {
                        $mime = $value->getMimeType();
                        $maxSize = 5120 * 1024; // 5MB

                        if (!str_starts_with($mime, 'image/') && $mime !== 'application/pdf') {
                            $fail("The {$attribute} must be an image or a PDF file.");
                        } elseif ($value->getSize() > $maxSize) {
                            $fail("The {$attribute} must not be greater than 5MB.");
                        }

                        return true;
                    }

                    $fail("The {$attribute} must be a valid file or an existing attachment ID.");
                },
            ],
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'data'    => $validator->errors(),
        ], 422));
    }
}
