<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Store;

class UniqueStoreName implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return !Store::where('store_name', $value)->where('user_id', '!=' , auth()->user()->user_id)->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The store name is already taken.';
    }
}
