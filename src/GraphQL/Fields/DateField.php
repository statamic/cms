<?php

namespace Statamic\GraphQL\Fields;

use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Field;

class DateField extends Field
{
    private $valueResolver;

    public function type(): Type
    {
        return Type::string();
    }

    public function args(): array
    {
        return [
            'format' => [
                'type' => Type::string(),
            ],
        ];
    }

    protected function resolve($entry, $args, $context, ResolveInfo $info)
    {
        if (! $date = $this->getValueResolver()($entry, $args, $context, $info)) {
            return null;
        }

        if ($format = $args['format'] ?? null) {
            return $date->format($format);
        }

        return (string) $date;
    }

    public function setValueResolver(Closure $resolver)
    {
        $this->valueResolver = $resolver;

        return $this;
    }

    public function getValueResolver()
    {
        return $this->valueResolver ?? function ($entry, $args, $context, $info) {
            return $entry->resolveGqlValue($info->fieldName);
        };
    }
}
