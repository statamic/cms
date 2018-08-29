<?php

namespace Statamic\Addons\Fields;

use Statamic\CP\Fieldset;
use Statamic\CP\FieldtypeFactory;
use Statamic\Extend\Fieldtype;

class FieldsFieldtype extends Fieldtype
{
    public function preProcess($data)
    {
        $processed = [];

        foreach ($data as $field_name => $field_config) {
            $field_config['name'] = $field_name;
            $processed[] = $this->recursivelyPreProcess($field_config);
        }

        return $processed;
    }

    private function recursivelyPreProcess($config)
    {
        // Get the fieldtype for this field
        $type = $config['type'];
        $config_fieldtype = FieldtypeFactory::create($type);

        // Get the fieldtype's config fieldset
        $fieldset = $config_fieldtype->getConfigFieldset();

        // Pre-process all the fields in the fieldset
        foreach ($fieldset->fieldtypes() as $field) {
            // Ignore if the field isn't in the config
            if (! in_array($field->getName(), array_keys($config))) {
                continue;
            }

            $field->is_config = true;
            $config[$field->getName()] = $field->preProcess($config[$field->getName()]);
        }

        return $config;
    }

    public function process($data)
    {
        $processed = [];

        foreach ($data as $key => $field_config) {
            $name = $field_config['name'];
            unset($field_config['name']);
            $processed[$name] = $this->recursivelyProcess($field_config);
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
