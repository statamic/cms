<?php

namespace Statamic\CP;

use Statamic\API\Str;
use Statamic\API\File;
use Statamic\API\Path;
use Statamic\API\Taxonomy;
use Statamic\API\YAML;
use Statamic\API\Fieldset as FieldsetAPI;
use Statamic\Contracts\CP\Fieldset as FieldsetContract;

/**
 * A fieldset
 */
class Fieldset implements FieldsetContract
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type = 'default';

    /**
     * @var string
     */
    private $locale;

    /**
     * @var array
     */
    private $contents;

    /**
     * @var array
     */
    private $fieldtypes;

    /**
     * Initialize a new fieldset
     */
    public function __construct()
    {
        $this->locale(default_locale());
    }

    /**
     * Get or set the type
     *
     * @param string|null $type
     * @return mixed
     * @throws \Exception
     */
    public function type($type = null)
    {
        if (is_null($type)) {
            return $this->type;
        }

        if (! in_array($type, ['default', 'global', 'settings', 'fieldtype', 'theme', 'addon'])) {
            throw new \Exception('Invalid fieldset type. Must be `default`, `global`, `settings`, `fieldtype`, `theme`, or `addon`.');
        }

        $this->type = $type;
    }

    /**
     * Get or set the locale
     *
     * @param string|null $locale
     * @return mixed
     */
    public function locale($locale = null)
    {
        if (is_null($locale)) {
            return $this->locale;
        }

        $this->locale = $locale;
    }

    /**
     * Get or set the name
     *
     * @param string|null $name
     * @return mixed
     */
    public function name($name = null)
    {
        if (is_null($name)) {
            return $this->name;
        }

        $this->name = $name;
    }

    /**
     * Get or set the title
     *
     * Either the `title` key, or fallback to the filename
     *
     * @param string|null $title
     * @return mixed
     */
    public function title($title = null)
    {
        if (! is_null($title)) {
            $this->contents['title'] = $title;
        }

        if (! $title = array_get($this->contents(), 'title')) {
            $title = ucfirst($this->name());
        }

        return $title;
    }

    /**
     * Get or set whether this fieldset is hidden from the selection dialog
     *
     * @param  bool|null $hidden
     * @return bool
     */
    public function hidden($hidden = null)
    {
        if (is_null($hidden)) {
            return array_get($this->contents, 'hide', false);
        }

        $this->contents['hide'] = $hidden;
    }

    /**
     * Get the path to the file
     *
     * @return string
     */
    public function path()
    {
        if ($this->type === 'addon') {
            list($addon, $name) = explode('.', $this->name());
            return Path::makeRelative(addons_path($addon.'/'.$name . '.yaml'));
        } else {
            $path = base_path('resources/fieldsets/');
        }

        return Path::makeRelative($path . $this->name() . '.yaml');
    }

    /**
     * Get or set the contents
     *
     * @param array|null $contents
     * @return mixed
     */
    public function contents($contents = null)
    {
        if (is_null($contents)) {
            return $this->contents;
        }

        $this->contents = $contents;
    }

    /**
     * Get or set the fields
     *
     * @param array|null $fields
     * @param boolean $inline_partials
     * @return mixed
     */
    public function fields($fields = null, $inline_partials = true)
    {
        if (is_null($fields)) {
            $fields = array_get($this->contents, 'fields', []);

            if ($inline_partials) {
                $fields = $this->inlinePartials($fields);
            }

            return $fields;
        }

        $this->contents['fields'] = $fields;
    }

    /**
     * Get all fields, without inlining partials
     *
     * @return array
     */
    public function fieldsWithPartials()
    {
        return $this->fields(null, false);
    }

    /**
     * Bring the fields in partial fieldsets into the parent
     *
     * @param  array $fields
     * @return array
     */
    private function inlinePartials($fields)
    {
        $inlined = [];

        foreach ($fields as $name => $config) {
            // Not a partial? Carry on.
            if (array_get($config, 'type', 'text') !== 'partial') {
                $inlined[$name] = $config;
                continue;
            }

            $inlined = array_merge($inlined, FieldsetAPI::get($config['fieldset'])->fields());
        }

        return $inlined;
    }

    /**
     * Get the fieldtypes in the fieldset
     *
     * @return \Statamic\Extend\Fieldtype[]
     */
    public function fieldtypes()
    {
        if ($this->fieldtypes) {
            return $this->fieldtypes;
        }

        $fieldtypes = [];

        $configs = array_merge(
            $this->fields(),
            $this->getTaxonomyFieldConfigs()
        );

        foreach ($configs as $name => $config) {
            // When merging fields without configs, they'd just be
            // empty strings so we'll set them to empty arrays.
            if (! is_array($config)) {
                $config = [];
            }

            $type = array_get($config, 'type', 'text');
            $config['name'] = $name;
            $fieldtypes[] = FieldtypeFactory::create($type, $config);
        }

        return $this->fieldtypes = $fieldtypes;
    }

    /**
     * Get the taxonomy field configurations
     *
     * @return array
     */
    private function getTaxonomyFieldConfigs()
    {
        if ($this->type() !== 'default') {
            return [];
        }

        $configs = $this->taxonomies();

        // If the configuration has been specifically set to false, there should be none.
        if ($configs === false) {
            return [];
        }

        // If there's no configs, we want to create empty arrays for all the taxonomies in the system.
        if (! $configs || $configs === []) {
            $configs = Taxonomy::all()->keyBy(function ($taxonomy) {
                return $taxonomy->path();
            })->map(function () { return []; })->all();
        }

        // Add type and name the configs so the fieldtypes will know they are taxonomy fields.
        foreach ($configs as $handle => &$config) {
            // Allow a primitive list of taxonomy handles.
            if (is_string($config)) {
                $handle = $config;
                $config = [];
            }

            // Allow passing "true" to allow a field without any configuration.
            if ($config === true) {
                $config = [];
            }

            $config['type'] = 'taxonomy';
            $config['name'] = $handle;
        }

        // Key them by the name. This ensures that if a primitive list was used, the indexes get changed.
        return collect($configs)->keyBy('name')->all();
    }

    /**
     * Save the fieldset to file
     */
    public function save()
    {
        $contents = $this->contents;
        $fields = array_get($contents, 'fields', []);

        // Do some cleaning up
        foreach ($fields as $name => $field) {
            // When a JS submission is involved, the fields array contains
            // some extra data that is used for display purposes.
            // We don't need them in the actual fieldset, so we'll get rid of those.
            foreach (['name'] as $unneeded) {
                unset($field[$unneeded]);
            }

            // Fields are 100% by default. Don't need to save it.
            if (in_array(array_get($field, 'width'), [100, '100'])) {
                unset($field['width']);
            }

            // Remove required field
            // We add it to the toArray so it can be used elsewhere,
            // but we don't want it added to the file itself.
            unset($field['required']);

            // Blank keys can be discarded.
            $field = $this->discardBlankKeys($field);

            // Replace it, making sure to use the name as the key.
            $fields[$name] = $field;
        }

        // Remove falsey keys where the defaults are falsey
        foreach (['hide', 'author', 'template'] as $falsey) {
            if (array_get($contents, $falsey) === false) {
                unset($contents[$falsey]);
            }
        }

        // Remove and then re-set the fields key, so it's last. It'll be the longest array so it's
        // just a little bit nicer to have everything else before it.
        unset($contents['fields']);
        $contents['fields'] = $fields;

        $yaml = YAML::dump($contents);

        File::put($this->path(), $yaml);
    }

    /**
     * Discard any blank/falsey keys
     *
     * @param  array $array
     * @return array
     */
    private function discardBlankKeys($array)
    {
        foreach ($array as $key => $value) {
            // We want to keep falsey values in these keys.
            if (in_array($key, ['show_when', 'hide_when'])) {
                continue;
            }

            if (is_array($value)) {
                $array[$key] = array_filter_recursive($value);
            } else {
                if (empty($value)) {
                    unset($array[$key]);
                }
            }
        }

        return $array;
    }

    public function delete()
    {
        File::delete($this->path());
    }

    /**
     * Get the instance as an array.
     *
     * @param boolean $inline_partials
     * @return array
     */
    public function toArray($inline_partials = true)
    {
        $localized = $this->locale() !== default_locale();

        $contents = $this->contents();
        $contents['fields'] = [];

        $fields = ($inline_partials) ? $this->fields() : $this->fieldsWithPartials();

        foreach ($fields as $name => $config) {
            // When merging fields without configs, they'd just be
            // empty strings so we'll set them to empty arrays.
            if (! is_array($config)) {
                $config = [];
            }

            // Skip any non-localizable fields if this isn't the default locale.
            if ($localized && !array_get($config, 'localizable')) {
                continue;
            }

            // Useful to have the field name as part of the array itself
            $config['name'] = $name;

            // Populate with appropriate defaults and fallbacks
            $config['type'] = array_get($config, 'type', 'text');
            $config['display'] = $this->getDisplayText($name, $config);
            $config['instructions'] = $this->getInstructionsText($name, $config);
            $config['required'] = Str::contains(array_get($config, 'validate'), 'required');
            $config['localizable'] = $name === 'title' ? true : array_get($config, 'localizable', false);

            $contents['fields'][] = $this->preProcess($config);
        }

        return $contents;
    }

    /**
     * Pre-process config fields
     *
     * Takes a field's config, and pre-processes each field by it's appropriate
     * fieldtype's preProcess method.
     *
     * For example, a replicator field's `sets` key is actually a ReplicatorSets fieldtype.
     * It will modify the array by moving the keys (set names) into each array.
     *
     * @param array $config
     * @return array
     * @throws \Statamic\Exceptions\FatalException
     */
    private function preProcess($config)
    {
        $fieldtype = FieldtypeFactory::create($config['type']);
        $fieldtypes = $fieldtype->getConfigFieldset()->fieldtypes();

        // Go through each fieldtype in the config fieldset and process the values.
        foreach ($fieldtypes as $field) {
            if (! in_array($field->getName(), array_keys($config))) {
                continue;
            }

            $field->is_config = true;
            $config[$field->getName()] = $field->preProcess($config[$field->getName()]);
        }

        return $config;
    }

    private function getDisplayText($name, $config)
    {
        if ($display = array_get($config, 'display')) {
            return $display;
        }

        if ($this->type === 'settings') {
            $key = "fieldsets/{$this->name}.{$name}";
            $translation = translate($key);
            if ($translation !== $key) {
                return $translation;
            }

            $key = 'fields.'.$name;
            $translation = translate($key);
            if ($translation !== $key) {
                return $translation;
            }
        }

        if ($this->type === 'addon') {
            $addon = explode('.', $this->name())[0];
            $key = "addons.{$addon}::settings.{$name}";
            $translation = translate($key);
            if ($translation !== $key) {
                return $translation;
            }
        }

        return null;
    }

    private function getInstructionsText($name, $config)
    {
        if ($instructions = array_get($config, 'instructions')) {
            return $instructions;
        }

        if ($this->type === 'settings') {
            $key = "fieldsets/{$this->name}.{$name}_instruct";
            $translation = translate($key);
            if ($translation !== $key) {
                return $translation;
            }

            $key = 'fields.'.$name.'_instruct';
            $translation = translate($key);
            if ($translation !== $key) {
                return $translation;
            }
        }

        if ($this->type === 'addon') {
            $addon = explode('.', $this->name())[0];
            $key = "addons.{$addon}::settings.{$name}_instruct";
            $translation = translate($key);
            if ($translation !== $key) {
                return $translation;
            }
        }

        return null;
    }

    /**
     * The URL to edit it in the CP
     *
     * @return mixed
     */
    public function editUrl()
    {
        return cp_route('fieldset.edit', $this->name());
    }

    /**
     * Get or set the taxonomies
     *
     * @param array|null $taxonomies
     * @return mixed
     */
    public function taxonomies($taxonomies = null)
    {
        if (is_null($taxonomies)) {
            return array_get($this->contents, 'taxonomies');
        }

        $this->contents['taxonomies'] = $taxonomies;
    }
}
