<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\GraphQL;
use Statamic\Fields\Fieldtype;

class Yaml extends Fieldtype
{
    protected function defaultConfigFieldItem(): ?array
    {
        return array_replace(parent::defaultConfigFieldItem(), ['type' => 'yaml']);
    }

    // Turn the YAML back into a string
    public function preProcess($data)
    {
        if (is_array($data)) {
            return count($data) > 0 ? \Statamic\Facades\Yaml::dump($data) : '';
        }

        return $data;
    }

    public function process($data)
    {
        if (substr_count($data, "\n") > 0 || substr_count($data, ': ') > 0) {
            $data = \Statamic\Facades\Yaml::parse($data);
        }

        if (empty($data)) {
            $data = null;
        }

        return $data;
    }

    public function toGqlType()
    {
        return [
            'type' => GraphQL::string(),
            'resolve' => function ($entry, $args, $context, $info) {
                if ($value = $entry->resolveRawGqlValue($info->fieldName)) {
                    return \Statamic\Facades\YAML::dump($value);
                }
            },
        ];
    }
}
