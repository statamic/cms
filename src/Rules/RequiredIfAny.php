<?php

namespace Statamic\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Arr;
use Illuminate\Validation\Concerns\ValidatesAttributes;
use Statamic\Contracts\GraphQL\CastableToValidationString;
use Stringable;

class RequiredIfAny implements CastableToValidationString, DataAwareRule, Stringable, ValidationRule
{
    use ValidatesAttributes;

    protected $parameters;
    protected $data = [];
    protected $field_name;
    protected $check_values;

    public function __construct(
        ...$parameters,
    ) {
        $parameters = count($parameters) === 1 ? $parameters[0] : $parameters;

        if (is_string($parameters)) {
            $parameters = explode(',', $parameters);
        }

        $this->parameters = $parameters;
        $this->field_name = array_shift($parameters);
        $this->check_values = $parameters ?? [];
    }

    public function setData(array $data)
    {
        $this->data = $data;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $field_values = Arr::wrap($this->data[$this->field_name] ?? []);

        // If none of the specified values are present, skip validation.
        if (! array_intersect($this->check_values, $field_values)) {
            return;
        }

        // If any of the specified values are present, validate as required.
        if ($this->validateRequired($attribute, $value)) {
            return;
        }

        $fail("The {$attribute} field is required when ".Arr::join($this->check_values, ', ', ' or ')." is present in {$this->field_name}.")->translate();
    }

    public function __toString(): string
    {
        return 'required_if_any:'.implode(',', $this->parameters);
    }

    public function toGqlValidationString(): string
    {
        return $this->__toString();
    }
}
