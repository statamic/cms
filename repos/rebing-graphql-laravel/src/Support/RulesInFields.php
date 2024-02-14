<?php

declare(strict_types = 1);
namespace Rebing\GraphQL\Support;

use GraphQL\Error\InvariantViolation;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\WrappingType;

class RulesInFields
{
    /** @var Type */
    protected $parentType;

    /** @var array<string,mixed> */
    protected $fieldsAndArguments;

    /**
     * @param array<string,mixed> $fieldsAndArgumentsSelection
     */
    public function __construct(Type $parentType, array $fieldsAndArgumentsSelection)
    {
        $this->parentType = $parentType instanceof WrappingType
            ? $parentType->getInnermostType()
            : $parentType;
        $this->fieldsAndArguments = $fieldsAndArgumentsSelection;
    }

    /**
     * @return array<string,mixed>
     */
    public function get(): array
    {
        return $this->getRules($this->fieldsAndArguments, null, $this->parentType);
    }

    /**
     * @param array<string,mixed>|string|callable $rules
     * @param array<string,mixed> $arguments
     * @return array<string,mixed>|string
     */
    protected function resolveRules($rules, array $arguments)
    {
        if (\is_callable($rules)) {
            return $rules($arguments);
        }

        return $rules;
    }

    /**
     * Get rules from fields.
     *
     * @param array<string,mixed> $fields
     * @return array<string,mixed>
     */
    protected function getRules(array $fields, ?string $prefix, Type $parentType): array
    {
        $rules = [];

        foreach ($fields as $name => $field) {
            $key = null === $prefix ? $name : "{$prefix}.{$name}";

            try {
                if (!method_exists($parentType, 'getField')) {
                    continue;
                }
                $fieldObject = $parentType->getField($name);
            } catch (InvariantViolation $e) {
                continue;
            }

            if (\is_array($field['fields'])) {
                $rules = $rules + $this->getRules($field['fields'], $key . '.fields', $fieldObject->getType());
            }

            $args = $fieldObject->config['args'] ?? [];

            foreach ($args as $argName => $info) {
                if (isset($info['rules'])) {
                    $rules[$key . '.args.' . $argName] = $this->resolveRules($info['rules'], $field['args']);
                }
            }
        }

        return $rules;
    }
}
