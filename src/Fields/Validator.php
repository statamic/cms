<?php

namespace Statamic\Fields;

use Illuminate\Support\Facades\Validator as LaravelValidator;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class Validator
{
    protected $fields;
    protected $replacements = [];
    protected $extraRules = [];
    protected $customMessages = [];
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

    public function withMessages($messages)
    {
        $this->customMessages = $messages;

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
            ->map(function ($rules) {
                return collect($rules)->map(function ($rule) {
                    return $this->parse($rule);
                })->all();
            })->all();
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

    public function withReplacements($replacements)
    {
        $this->replacements = $replacements;

        return $this;
    }

    public function validate()
    {
        return LaravelValidator::validate(
            $this->fields->preProcessValidatables()->values()->all(),
            $this->rules(),
            $this->customMessages,
            $this->attributes()
        );
    }

    public function attributes()
    {
        return $this->fields->preProcessValidatables()->all()->reduce(function ($carry, $field) {
            return $carry->merge($field->validationAttributes());
        }, collect())->all();
    }

    private function parse($rule)
    {
        if (! is_string($rule) ||
            ! Str::contains($rule, '{') ||
            Str::startsWith($rule, 'regex:') ||
            Str::startsWith($rule, 'not_regex:')
        ) {
            return $rule;
        }

        $rule = str_replace('{this}.', $this->context['prefix'] ?? '', $rule);

        return preg_replace_callback('/{\s*([a-zA-Z0-9_\-]+)\s*}/', function ($match) {
            return Arr::get($this->replacements, $match[1], 'NULL');
        }, $rule);
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
