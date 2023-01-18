<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\GraphQL;
use Statamic\Fields\Fieldtype;

class Yaml extends Fieldtype
{
    protected $categories = ['special'];

    protected function configFieldItems(): array
    {
        return [
            'default' => [
                'display' => __('Default Value'),
                'instructions' => __('statamic::messages.fields_default_instructions'),
                'type' => 'yaml',
                'width' => 100,
            ],
        ];
    }

    // Turn the YAML back into a string
    public function preProcess($data)
    {
        if (is_array($data)) {
            return count($data) > 0 ? \Statamic\Facades\YAML::dump($data) : '';
        }

        return $data;
    }

    public function process($data)
    {
        if (substr_count($data, "\n") > 0 || substr_count($data, ': ') > 0) {
            $data = \Statamic\Facades\YAML::parse($data);
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
