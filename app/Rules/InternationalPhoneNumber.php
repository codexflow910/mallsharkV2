<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class InternationalPhoneNumber implements Rule
{
    public function passes($attribute, $value)
    {
        // This pattern allows for international numbers starting with +, followed by country code and number.
        return preg_match('/^\+?[1-9]\d{1,14}$/', $value);
    }

    public function message()
    {
        return 'The :attribute must be a valid international phone number.';
    }
}
