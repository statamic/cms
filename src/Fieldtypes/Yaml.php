<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\GraphQL;
use Statamic\Fields\Fieldtype;
use Statamic\Yaml\ParseException;

class Yaml extends Fieldtype
{
    protected $categories = ['special'];
    protected $keywords = ['yml'];

    protected function configFieldItems(): array
    {
        return [
            'default' => [
                'display' => __('Default Value'),
                'instructions' => __('statamic::messages.fields_default_instructions'),
                'type' => 'yaml',
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
        if (empty($data)) {
            return null;
        }

        try {
            return \Statamic\Facades\YAML::parse($data);
        } catch (ParseException $e) {
            return $data;
        }
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
