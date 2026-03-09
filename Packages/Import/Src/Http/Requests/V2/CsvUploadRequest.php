<?php

namespace Packages\Import\Http\Requests\V2;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CsvUploadRequest extends FormRequest
{
    private $requiredHeaders = ['Handle', 'Title', 'Variant Price', 'Variant Inventory', 'Product Type'];

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Define validation rules for the CSV upload request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'csv_file' => [
                'required',
                'file',
                'mimes:csv',
                function ($attribute, $value, $fail) {
                    $headers = $this->getHeaders($value);
                    $validation = $this->validateHeaders($headers, $this->requiredHeaders);

                    if (!$validation['valid']) {
                        $fail("Missing required headers: " . implode(', ', $validation['missing']));
                    }
                },
            ],
        ];
    }

    /**
     * Extract headers from the CSV file.
     */
    private function getHeaders($file)
    {
        $handle = fopen($file, 'r');
        $headers = fgetcsv($handle);
        fclose($handle);

        return $headers ?: [];
    }

    /**
     * Validate headers against required headers.
     */
    private function validateHeaders($headers, $requiredHeaders)
    {
        $missingHeaders = array_diff($requiredHeaders, $headers);
        if (!empty($missingHeaders)) {
            return [
                'valid' => false,
                'missing' => $missingHeaders,
            ];
        }

        return ['valid' => true];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  Validator  $validator
     * @return void
     *
     * @throws HttpResponseException
     */
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
