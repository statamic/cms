<?php

namespace Statamic\Validation;

use Statamic\Fields\Field;
use Statamic\Contracts\CP\Fieldset;

class Compiler
{
    protected $fieldset;
    protected $extraRules = [];

    public function fieldset(Fieldset $fieldset)
    {
        $this->fieldset = $fieldset;

        return $this;
    }

    public function with($rules)
    {
        $this->extraRules = $rules;

        return $this;
    }

    public function rules()
    {
        $rules = $this->fields()->reduce(function ($carry, $field) {
            return $carry->merge($field->rules());
        }, collect());

        foreach ($this->extraRules as $field => $fieldRules) {
            $fieldRules = self::explodeRules($fieldRules);

            if ($rules->has($field)) {
                $rules[$field] = array_merge($rules[$field], $fieldRules);
            } else {
                $rules[$field] = $fieldRules;
            }
        }

        return $rules->all();
    }

    public function attributes()
    {
        return $this->fields()->reduce(function ($carry, $field) {
            return $carry->merge($field->attributes());
        }, collect())->all();
    }

    public static function explodeRules($rules)
    {
        if (! $rules) {
            return [];
        }

        if (is_string($rules)) {
            $rules = explode('|', $rules);
        }

        return $rules;
    }

    private function fields()
    {
        return collect($this->fieldset->inlinedFields())->map(function ($field, $handle) {
            return (new Field($handle, $field));
        });
    }
}
