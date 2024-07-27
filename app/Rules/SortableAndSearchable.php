<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class SortableAndSearchable implements Rule
{
    public $sortAbleAndSearchAbleColumn;

    public function __construct(array $data)
    {
        $this->sortAbleAndSearchAbleColumn = $data;
    }

    public function passes($attribute, $value)
    {
        $this->value = $value;
        if (is_array($value)) {
            foreach ($value as $column) {
                $this->value = $column;
                if (!isset($this->sortAbleAndSearchAbleColumn[$column])) {
                    $this->notExist = 1;
                    return false;
                } else {
                    if (!$this->sortAbleAndSearchAbleColumn[$column]) {
                        return false;
                    }
                }
            }
        } else {
            if (!isset($this->sortAbleAndSearchAbleColumn[$value])) {
                $this->notExist = 1;
                return false;
            } else {
                if (!$this->sortAbleAndSearchAbleColumn[$value]) {
                    return false;
                }
            }
        }

        return true;
    }

    public function message()
    {
        if (isset($this->notExist)) {
            return trans('validation.attributes.sortable_and_searchable_exist', ['value' => $this->value]);
        }

        return trans('validation.attributes.sortable_and_searchable', ['value' => $this->value]);
    }
}
