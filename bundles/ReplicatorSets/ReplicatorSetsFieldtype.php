<?php

namespace Statamic\Addons\ReplicatorSets;

use Statamic\CP\Fieldset;
use Statamic\Extend\Fieldtype;
use Statamic\CP\FieldtypeFactory;

class ReplicatorSetsFieldtype extends Fieldtype
{
    public function preProcess($data)
    {
        $processed = [];

        foreach ($data as $set_name => $set_config) {
            $set_config['name'] = $set_name;
            $set_config['id'] = $set_name; // Used by Vue so the name can be modified freely and not lose track.
            $set_config['fields'] = $this->moveInNameKey(array_get($set_config, 'fields', []));
            $processed[] = $set_config;
        }

        return $processed;
    }

    private function moveInNameKey($fields)
    {
        $processed = [];

        foreach ($fields as $name => $config) {
            $config['name'] = $name;
            $processed[] = $this->recursivelyPreProcess($config);
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
        if (! $data) {
            return;
        }

        $processed = [];

        foreach ($data as $set) {
            $set_name = $set['name'];
            unset($set['name']);
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
            $name = $field['name'];
            unset($field['name']);
            $processed[$name] = $this->recursivelyProcess($field);
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
