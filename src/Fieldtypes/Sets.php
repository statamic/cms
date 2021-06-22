<?php

namespace Statamic\Fieldtypes;

use Statamic\CP\FieldtypeFactory;
use Statamic\Fields\Fieldset;
use Statamic\Fields\FieldTransformer;
use Statamic\Fields\Fieldtype;
use Statamic\Support\Arr;

class Sets extends Fieldtype
{
    protected $selectable = false;

    /**
     * Converts the "sets" array of a Replicator (or Bard) field into what the
     * <sets-fieldtype> Vue component is expecting, within either the Blueprint
     * or Fieldset builders in the AJAX request performed when opening the field.
     */
    public function preProcess($data)
    {
        return collect($data)->map(function ($set, $handle) {
            $set['_id'] = $handle;
            $set['handle'] = $handle;
            $set['fields'] = collect($set['fields'])->map(function ($field, $i) {
                return array_merge(FieldTransformer::toVue($field), ['_id' => $i]);
            })->all();

            return $set;
        })->values()->all();
    }

    /**
     * Converts the "sets" array of a Replicator (or Bard) field into what
     * the <replicator-fieldtype> is expecting in its config.sets array.
     */
    public function preProcessConfig($data)
    {
        return collect($data)
            ->map(function ($config, $name) {
                return array_merge($config, [
                    'handle' => $name,
                    'id' => $name,
                    'fields' => (new NestedFields)->preProcessConfig(array_get($config, 'fields', [])),
                ]);
            })
            ->values()
            ->all();
    }

    /**
     * Converts the Blueprint/Fieldset builder Settings Vue component's representation of the
     * Replicator's "sets" array into what should be saved to the Blueprint/Fieldset's YAML.
     * Triggered in the AJAX request when you click "finish" when editing a Replicator field.
     */
    public function process($sets)
    {
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
