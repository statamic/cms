<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\File;
use Statamic\Fields\Fieldset;
use Statamic\Fields\FieldTransformer;
use Statamic\Fields\Fieldtype;
use Statamic\Statamic;
use Statamic\Support\Arr;

class Sets extends Fieldtype
{
    protected $selectable = false;

    protected static $iconsDirectory = null;

    /**
     * Converts the "sets" array of a Replicator (or Bard) field into what the
     * <sets-fieldtype> Vue component is expecting, within either the Blueprint
     * or Fieldset builders in the AJAX request performed when opening the field.
     */
    public function preProcess($sets)
    {
        $sets = collect($sets);

        if ($sets->isEmpty()) {
            return [];
        }

        // If the first set doesn't have a "sets" key, it would be the legacy format.
        // We'll put it in a "main" group so it's compatible with the new format.
        if (! Arr::has($sets->first(), 'sets')) {
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
                'icon' => $group['icon'] ?? null,
                'sections' => collect($group['sets'] ?? [])->map(function ($set, $setHandle) use ($groupId) {
                    return [
                        '_id' => $setId = $groupId.'-section-'.$setHandle,
                        'handle' => $setHandle,
                        'display' => $set['display'] ?? null,
                        'instructions' => $set['instructions'] ?? null,
                        'icon' => $set['icon'] ?? null,
                        'hide' => $set['hide'] ?? null,
                        'fields' => collect($set['fields'] ?? [])->map(function ($field, $i) use ($setId) {
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

        if ($sets->isEmpty()) {
            return [];
        }

        // If the first set doesn't have a "sets" key, it would be the legacy format.
        // We'll put it in a "main" group so it's compatible with the new format.
        if (! Arr::has($sets->first(), 'sets')) {
            $sets = collect([
                'main' => [
                    'sets' => $sets->all(),
                ],
            ]);
        }

        return collect($sets)->map(function ($group, $groupHandle) {
            return array_merge($group, [
                'handle' => $groupHandle,
                'sets' => collect($group['sets'])
                    ->map(function ($config, $name) {
                        return array_merge($config, [
                            'handle' => $name,
                            'id' => $name,
                            'fields' => (new NestedFields)->preProcessConfig(Arr::get($config, 'fields', [])),
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
                    'icon' => $tab['icon'] ?? null,
                    'sets' => collect($tab['sections'])->mapWithKeys(function ($section) {
                        return [
                            $section['handle'] => [
                                'display' => $section['display'],
                                'instructions' => $section['instructions'] ?? null,
                                'icon' => $section['icon'] ?? null,
                                'hide' => $section['hide'] ?? null,
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

    /**
     * Allow the user to set custom icon directory and/or folder for SVG set icons.
     *
     * @param  string|null  $directory
     * @param  string|null  $folder
     */
    public static function setIconsDirectory($directory = null, $folder = null)
    {
        // If they are specifying new base directory, ensure we do not assume sub-folder
        if ($directory) {
            static::$iconsDirectory = $directory;
            static::$iconsFolder = $folder;
        }

        // Of if they are specifying just a sub-folder, use that with original base directory
        elseif ($folder) {
            static::$iconsFolder = $folder;
        }

        // Then provide to script for <icon-fieldtype> selector components in blueprint config
        Statamic::provideToScript([
            'setIconsDirectory' => static::$iconsDirectory,
            'setIconsFolder' => null,
        ]);

        // And finally, provide the file contents of all custom svg icons to script,
        // but only if custom directory because our <svg-icon> component cannot
        // reference custom paths at runtime without a full Vite re-build
        if ($directory) {
            Icon::provideCustomSvgIconsToScript($directory, $folder);
        }
    }
}
