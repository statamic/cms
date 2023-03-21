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
    public function preProcess($sets)
    {
        $sets = collect($sets);

        // If the first set has a "fields" key, it would be the legacy format.
        // We'll put it in a "main" group so it's compatible with the new format.
        if (Arr::has($sets->first(), 'fields')) {
            $sets = collect([
                'main' => [
                    'display' => __('Main'),
                    'sets' => $sets->all(),
                ],
            ]);
        }

        return collect($sets)->map(function ($group, $groupHandle) {
            return [
                '_id' => $groupId = 'group-'.$groupHandle,
                'handle' => $groupHandle,
                'display' => $group['display'] ?? null,
                'instructions' => $group['instructions'] ?? null,
                'sections' => collect($group['sets'] ?? [])->map(function ($set, $setHandle) use ($groupId) {
                    return [
                        '_id' => $setId = $groupId.'-section-'.$setHandle,
                        'handle' => $setHandle,
                        'display' => $set['display'] ?? null,
                        'instructions' => $set['instructions'] ?? null,
                        'fields' => collect($set['fields'])->map(function ($field, $i) use ($setId) {
                            return array_merge(FieldTransformer::toVue($field), ['_id' => $setId.'-'.$i]);
                        })->all(),
                    ];
                })->values()->all(),
            ];
        })->values()->all();
    }

    /**
     * Converts the "sets" array of a Replicator (or Bard) field into what
     * the <replicator-fieldtype> is expecting in its config.sets array.
     */
    public function preProcessConfig($sets)
    {
        $sets = collect($sets);

        // If the first set has a "fields" key, it would be the legacy format.
        // We'll put it in a "main" group so it's compatible with the new format.
        if (Arr::has($sets->first(), 'fields')) {
            $sets = collect([
                'main' => [
                    'sets' => $sets->all(),
                ],
            ]);
        }

        return collect($sets)->map(function ($group, $groupHandle) {
            return array_merge($group, [
                'handle' => $groupHandle,
                'sets' => collect($group['sets'])->map(function ($config, $name) {
                    return array_merge($config, [
                        'handle' => $name,
                        'id' => $name,
                        'fields' => (new NestedFields)->preProcessConfig(array_get($config, 'fields', [])),
                    ]);
                })
                ->values()
                ->all(),
            ]);
        })->values()->all();
    }

    /**
     * Converts the Blueprint/Fieldset builder Settings Vue component's representation of the
     * Replicator's "sets" array into what should be saved to the Blueprint/Fieldset's YAML.
     * Triggered in the AJAX request when you click "finish" when editing a Replicator field.
     */
    public function process($tabs)
    {
        return collect($tabs)->mapWithKeys(function ($tab) {
            return [
                $tab['handle'] => [
                    'display' => $tab['display'],
                    'instructions' => $tab['instructions'] ?? null,
                    'sets' => collect($tab['sections'])->mapWithKeys(function ($section) {
                        return [
                            $section['handle'] => [
                                'display' => $section['display'],
                                'instructions' => $section['instructions'] ?? null,
                                'fields' => collect($section['fields'])->map(function ($field) {
                                    return FieldTransformer::fromVue($field);
                                })->all(),
                            ],
                        ];
                    })->all(),
                ],
            ];
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
