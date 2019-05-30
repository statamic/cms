<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldset;
use Statamic\Fields\Fieldtype;
use Statamic\CP\FieldtypeFactory;

class Sets extends Fieldtype
{
    protected $selectable = false;

    public function preProcess($data)
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

    public function process($data)
    {
        if (! $data) {
            return;
        }

        $processed = [];

        foreach ($data as $set) {
            $set_name = $set['handle'];
            unset($set['handle']);
            $set['fields'] = $this->moveOutNameKey($set['fields']);
            // Method is called cleanField but the logic applies to the sets too.
            // We want to get rid of the Vue stuff like ids, isNew, isMeta, etc.
            $processed[$set_name] = Fieldset::cleanFieldForSaving($set);
        }

        return empty($processed) ? null : $processed;
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
