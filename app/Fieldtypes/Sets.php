<?php

namespace Statamic\Fieldtypes;

use Statamic\Support\Arr;
use Statamic\Fields\Fieldset;
use Statamic\Fields\Fieldtype;
use Statamic\CP\FieldtypeFactory;
use Statamic\Fields\FieldTransformer;

class Sets extends Fieldtype
{
    protected $selectable = false;

    public function preProcess($data)
    {
        return collect($data)->map(function ($set, $handle) {
            $set['handle'] = $handle;
            $set['fields'] = collect($set['fields'])->map(function ($field, $i) {
                return array_merge(FieldTransformer::toVue($field), ['_id' => $i]);
            })->all();
            return $set;
        })->values()->all();
    }

    public function preProcessConfig($data)
    {
        return collect($data)
            ->map(function ($config, $name) {
                return array_merge($config, [
                    'handle' => $name,
                    'id' => $name,
                    'fields' => (new NestedFields)->preProcess(array_get($config, 'fields', [])),
                ]);
            })
            ->values()
            ->all();
    }

    public function process($sets)
    {
        // $sets is what you get from the SetsFieldtype.vue when you hit 'finish' when editing a replicator field
        // in the blueprint or fieldset builders.
        return collect($sets)
            ->mapWithKeys(function ($set) {
                $handle = Arr::pull($set, 'handle');
                $set = Arr::except($set, '_id');
                $set['fields'] = collect($set['fields'])->map(function ($field) {
                    return FieldTransformer::fromVue($field);
                })->all();
                return [$handle => $set];
            })
            ->all();
    }

    private function moveOutNameKey($fields)
    {
        $processed = [];

        foreach ($fields as $field) {
            $handle = $field['handle'];
            unset($field['handle']);
            $processed[$handle] = $this->recursivelyProcess($field);
        }

        return $processed;
    }

    private function recursivelyProcess($config)
    {
        // Get the fieldtype for this field
        $type = $config['type'];
        $config_fieldtype = FieldtypeFactory::create($type);

        // Get the fieldtype's config fieldset
        $fieldset = $config_fieldtype->getConfigFieldset();

        // Process all the fields in the fieldset
        foreach ($fieldset->fieldtypes() as $field) {
            // Ignore if the field isn't in the config
            if (! in_array($field->getName(), array_keys($config))) {
                continue;
            }

            $config[$field->getName()] = $field->process($config[$field->getName()]);
        }

        return Fieldset::cleanFieldForSaving($config);
    }
}
