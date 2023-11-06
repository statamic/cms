<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\File;
use Statamic\Facades\Path;
use Statamic\Fields\Fieldset;
use Statamic\Fields\FieldTransformer;
use Statamic\Fields\Fieldtype;
use Statamic\Statamic;
use Statamic\Support\Arr;

class Sets extends Fieldtype
{
    protected $selectable = false;

    protected static $iconsDirectory = 'vendor/statamic/cms/resources/svg/icons';
    protected static $iconsFolder = 'plump';

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
                'icon' => $this->getIconHtml(Arr::get($group, 'icon')),
                'sections' => collect($group['sets'] ?? [])->map(function ($set, $setHandle) use ($groupId) {
                    return [
                        '_id' => $setId = $groupId.'-section-'.$setHandle,
                        'handle' => $setHandle,
                        'display' => $set['display'] ?? null,
                        'instructions' => $set['instructions'] ?? null,
                        'icon' => $this->getIconHtml(Arr::get($set, 'icon')),
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
                'icon' => $this->getIconHtml(Arr::get($group, 'icon'), 'regular/folder-generic'),
                'sets' => collect($group['sets'])
                    ->map(function ($config, $name) {
                        return array_merge($config, [
                            'handle' => $name,
                            'id' => $name,
                            'fields' => (new NestedFields)->preProcessConfig(array_get($config, 'fields', [])),
                            'icon' => $this->getIconHtml(Arr::get($config, 'icon'), 'light/add'),
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
     */
    public static function setIconsDirectory($directory = null, $folder = null)
    {
        // If they are specifying new base directory, ensure we do not assume sub-folder
        if ($directory) {
            static::$iconsDirectory = $directory;
            static::$iconsFolder = null;
        }

        // Of if they are specifying just a sub-folder, use that with original base directory
        elseif ($folder) {
            static::$iconsFolder = $folder;
        }

        // Then provide to script for <icon-fieldtype> selector components in blueprint config
        Statamic::provideToScript([
            'setIconsDirectory' => static::$iconsDirectory,
            'setIconsFolder' => static::$iconsFolder,
        ]);
    }

    /**
     * Get icon HTML, because our <svg-icon> component cannot reference custom paths at runtime without a Vite re-build.
     *
     * @param  string|null  $configuredIcon
     * @param  string|null  $fallbackVendorIcon
     * @return string|null
     */
    protected function getIconHtml($configuredIcon, $fallbackVendorIcon = null)
    {
        $iconPath = collect([static::$iconsDirectory, static::$iconsFolder, $configuredIcon])
            ->filter()
            ->implode('/').'.svg';

        $absoluteIconPath = Path::isAbsolute($iconPath)
            ? $iconPath
            : Path::makeFull($iconPath);

        if ($configuredIcon && File::exists($absoluteIconPath)) {
            return File::get($absoluteIconPath);
        }

        $absoluteFallbackIconPath = base_path(static::$iconsDirectory."/{$fallbackVendorIcon}.svg");

        if ($fallbackVendorIcon && File::exists($absoluteFallbackIconPath)) {
            return File::get($absoluteFallbackIconPath);
        }

        return null;
    }
}
