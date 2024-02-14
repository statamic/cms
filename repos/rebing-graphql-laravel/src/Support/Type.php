<?php

declare(strict_types = 1);
namespace Rebing\GraphQL\Support;

use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type as GraphqlType;
use Illuminate\Support\Str;
use Rebing\GraphQL\Support\Contracts\TypeConvertible;

/**
 * @property string $name
 */
abstract class Type implements TypeConvertible
{
    /** @var array<string,mixed> */
    protected $attributes = [];

    /**
     * @return array<string,mixed>
     */
    public function attributes(): array
    {
        return [];
    }

    /**
     * @return array<int|string,array<string,mixed>|string|FieldDefinition|Field>
     */
    public function fields(): array
    {
        return [];
    }

    /**
     * @return array<GraphqlType|callable>
     */
    public function interfaces(): array
    {
        return [];
    }

    protected function getFieldResolver(string $name, array $field): ?callable
    {
        if (isset($field['resolve'])) {
            return $field['resolve'];
        }

        $resolveMethod = 'resolve' . Str::studly($name) . 'Field';

        if (method_exists($this, $resolveMethod)) {
            $resolver = [$this, $resolveMethod];

            return function () use ($resolver) {
                $args = \func_get_args();

                return \call_user_func_array($resolver, $args);
            };
        }

        if (isset($field['alias']) && \is_string($field['alias']) && !($this instanceof InputType)) {
            $alias = $field['alias'];

            return function ($type) use ($alias) {
                return $type->{$alias};
            };
        }

        return null;
    }

    /**
     * @return array<string,mixed>
     */
    public function getFields(): array
    {
        $fields = $this->fields();
        $allFields = [];

        foreach ($fields as $name => $field) {
            if (\is_string($field)) {
                $field = app($field);
                $field->name = $name;
                $allFields[$name] = $field->toArray();
            } elseif ($field instanceof Field) {
                $field->name = $name;
                $allFields[$name] = $field->toArray();
            } elseif ($field instanceof FieldDefinition) {
                $allFields[$field->name] = $field;
            } else {
                $resolver = $this->getFieldResolver($name, $field);

                if ($resolver) {
                    $field['resolve'] = $resolver;
                }
                $allFields[$name] = $field;
            }
        }

        return $allFields;
    }

    /**
     * Get the attributes from the container.
     * @return array<string,mixed>
     */
    public function getAttributes(): array
    {
        $attributes = $this->attributes();

        return array_merge($this->attributes, [
            'fields' => function () {
                return $this->getFields();
            },
            'interfaces' => function () {
                return $this->interfaces();
            },
        ], $attributes);
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return $this->getAttributes();
    }

    public function toType(): GraphqlType
    {
        return new ObjectType($this->toArray());
    }

    /**
     * Dynamically retrieve the value of an attribute.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        $attributes = $this->getAttributes();

        return $attributes[$key] ?? null;
    }

    /**
     * @param mixed $value
     */
    public function __set(string $key, $value): void
    {
        $this->attributes[$key] = $value;
    }
}
