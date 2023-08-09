<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class UniqueRucRule implements Rule
{
   public $user_id;

    public function __construct($user_id)
    {
        $this->user_id=$user_id;
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
        $fail('The RUC has already been taken.');
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The validation error message.';
    }
}
