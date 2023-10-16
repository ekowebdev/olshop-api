<?php

namespace App\Rules;

use App\Http\Models\Address;
use Illuminate\Contracts\Validation\Rule;

class UniqueMainAddress implements Rule
{
    protected $value;
    
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
        $this->value = $value;
        $count = Address::where($attribute, $value)->where('is_main', 'yes')->count();
        if($count > 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('error.main_address_exists', ['id' => $this->value]);
    }
}
