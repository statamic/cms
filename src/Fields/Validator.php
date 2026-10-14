<?php

namespace Statamic\Fields;

use Illuminate\Contracts\Validation\CompilableRules;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator as LaravelValidator;
use InvalidArgumentException;
use Statamic\Support\Arr;
use Statamic\Support\Str;

/**
 * @phpstan-consistent-constructor
 */
class Validator
{
    protected $fields;
    protected $preProcessedFields;
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
        $this->preProcessedFields = null;

        return $this;
    }

    protected function preProcessedFields()
    {
        return $this->preProcessedFields ??= $this->fields->preProcessValidatables();
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
        $rules = $this
            ->merge($this->fieldRules(), $this->extraRules)
            ->map(function ($rules) {
                return collect($rules)->map(function ($rule) {
                    return $this->parse($rule);
                })->all();
            })->all();

        return $this->filterPrecognitiveRules($rules);
    }

    private function fieldRules()
    {
        if (! $this->fields) {
            return collect();
        }

        return $this->preProcessedFields()->all()->reduce(function ($carry, $field) {
            if (request()->isPrecognitive() && $field->type() == 'assets') {
                return $carry;
            }

            return $carry->merge($field->setValidationContext($this->context)->rules());
        }, collect());
    }

    public function merge($original, $overrides)
    {
        foreach ($overrides as $field => $fieldRules) {
            $fieldRules = self::explodeRules($fieldRules);

            if (Arr::has($original, $field)) {
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

    public function validator()
    {
        return LaravelValidator::make(
            $this->preProcessedFields()->values()->all(),
            $this->rules(),
            $this->customMessages,
            $this->attributes()
        );
    }

    public function validate()
    {
        return $this->validator()->validate();
    }

    public function attributes()
    {
        return $this->preProcessedFields()->all()->reduce(function ($carry, $field) {
            return $carry->merge($field->validationAttributes());
        }, collect())->all();
    }

    public function parse($rule)
    {
        if (is_string($rule) && Str::startsWith($rule, 'new ')) {
            return $this->parseClassBasedRule($rule);
        }

        if (is_string($rule) && Str::contains($rule, '{') && ! Str::startsWith($rule, ['regex:', 'not_regex:'])) {
            return $this->parseStringBasedRule($rule);
        }

        return $rule;
    }

    private function parseClassBasedRule($rule)
    {
        $rule = preg_replace_callback('/{\s*([a-zA-Z0-9_\-]+)\s*}/', function ($match) {
            $value = Arr::get($this->replacements, $match[1]);

            if ($value === null) {
                return 'null';
            }

            if ($value === true) {
                return 'true';
            }

            if ($value === false) {
                return 'false';
            }

            return is_string($value) ? "'{$value}'" : $value;
        }, $rule);

        [$class, $arguments] = (new ClassRuleParser)->parse($rule);

        $class = ltrim(trim($class), '\\');

        if (! $this->isValidationRuleClass($class)) {
            throw new InvalidArgumentException("[{$class}] is not a valid validation rule class.");
        }

        return new $class(...$arguments);
    }

    private function isValidationRuleClass(string $class): bool
    {
        if (! preg_match('/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*(\\\\[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*)*$/', $class)) {
            return false;
        }

        if (! class_exists($class)) {
            return false;
        }

        $contracts = [
            Rule::class,
            ValidationRule::class,
            InvokableRule::class,
            CompilableRules::class,
        ];

        foreach ($contracts as $contract) {
            if (is_subclass_of($class, $contract)) {
                return true;
            }
        }

        return Str::startsWith($class, 'Illuminate\\Validation\\Rules\\');
    }

    private function parseStringBasedRule($rule)
    {
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

    public function filterPrecognitiveRules($rules)
    {
        $request = request();

        if (! $request->isPrecognitive()) {
            return $rules;
        }

        return $request->filterPrecognitiveRules($rules);
    }
}
