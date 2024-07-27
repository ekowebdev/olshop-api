<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class SortType implements Rule
{
    public function passes($attribute, $value)
    {
        $this->attribute = $attribute;
        if (is_array($value)) {
            foreach ($value as $key_val => $value_val) {
                if ($value_val != 'desc' && $value_val != 'asc') {
                    return false;
                }
            }
        } else {
            if ($value != 'desc' && $value != 'asc') {
                return false;
            }
        }

        return true;
    }

    public function message()
    {
        return trans('validation.attributes.sort_type',[
            'attr' => $this->attribute
        ]);
    }
}
