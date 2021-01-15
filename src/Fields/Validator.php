<?php

namespace Statamic\Fields;

use Illuminate\Support\Facades\Validator as LaravelValidator;

class Validator
{
    protected $fields;
    protected $data = [];
    protected $extraRules = [];
    protected $context = [];

    public function make()
    {
        return new static;
    }

    public function fields($fields)
    {
        $this->fields = $fields;

        return $this;
    }

    public function withRules($rules)
    {
        $this->extraRules = $rules;

        return $this;
    }

    public function withContext($context)
    {
        $this->context = $context;

        return $this;
    }

    public function rules()
    {
        return $this
            ->merge($this->fieldRules(), $this->extraRules)
            ->all();
    }

    private function fieldRules()
    {
        if (! $this->fields) {
            return collect();
        }

        return $this->fields->preProcessValidatables()->all()->reduce(function ($carry, $field) {
            return $carry->merge($field->setValidationContext($this->context)->rules());
        }, collect());
    }

    public function merge($original, $overrides)
    {
        foreach ($overrides as $field => $fieldRules) {
            $fieldRules = self::explodeRules($fieldRules);

            if (array_has($original, $field)) {
                $original[$field] = array_merge($original[$field], $fieldRules);
            } else {
                $original[$field] = $fieldRules;
            }

            $original[$field] = collect($original[$field])->unique()->values()->all();
        }

        return collect($original);
    }

    public function validate()
    {
        return LaravelValidator::validate(
            $this->fields->preProcessValidatables()->values()->all(),
            $this->rules(),
            [],
            $this->attributes()
        );
    }

    public function attributes()
    {
        return $this->fields->preProcessValidatables()->all()->reduce(function ($carry, $field) {
            return $carry->merge($field->validationAttributes());
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
}
