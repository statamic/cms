<?php

namespace Statamic\GraphQL\Fields;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Field;

class DateField extends Field
{
    public function type(): Type
    {
        return Type::string();
    }

    public function args(): array
    {
        return [
            'format' => [
                'type' => Type::string(),
            ]
        ];
    }

    protected function resolve($entry, $args, $context, ResolveInfo $info)
    {
        if (! $date = $entry->resolveGqlValue($info->fieldName)) {
            return null;
        }

        if ($format = $args['format'] ?? null) {
            return $date->format($format);
        }

        return (string) $date;
    }
}
