<?php

namespace Statamic\Http\Controllers;

use Statamic\API\Addon;
use Statamic\API\Config;
use Statamic\API\Fieldset;
use Statamic\API\Folder;
use Statamic\API\Helper;
use Statamic\API\Str;
use Statamic\API\Pattern;
use Statamic\API\Taxonomy;
use Statamic\CP\FieldtypeFactory;
use Illuminate\Support\Collection;

class FieldsetController extends CpController
{
    /**
     * List all fieldsets
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $data = [
            'title' => 'Fieldsets'
        ];

        return view('fieldsets.index', $data);
    }

    public function get()
    {
        $fieldsets = collect(Fieldset::all())->sortBy(function ($fieldset) {
            return $fieldset->title();
        })->map(function ($fieldset) {
            // If we've decided to omit hidden fieldsets, and this one should be
            // hidden, we'll just move right along.
            if (bool($this->request->query('hidden', true)) === false && $fieldset->hidden()) {
                return null;
            }

            return [
                'title'    => $fieldset->title(),
                'id'       => $fieldset->name(), // vue uses this as an id
                'uuid'     => $fieldset->name(), // keeping this here temporarily, just in case.
                'edit_url' => $fieldset->editUrl()
            ];
        })->filter()->values()->all();

        return ['columns' => ['title'], 'items' => $fieldsets];
    }

    /**
     * @param string $name
     * @return \Illuminate\View\View
     */
    public function edit($name)
    {
        $fieldset = Fieldset::get($name);

        $title = 'Editing ' . $name . '.yaml';

        return view('fieldsets.edit', compact('title', 'fieldset'));
    }

    /**
     * Delete a fieldset
     *
     * @return array
     */
    public function delete()
    {
        $ids = Helper::ensureArray($this->request->input('ids'));

        foreach ($ids as $name) {
            $fieldset = Fieldset::get($name);
            $fieldset->delete();
        }

        return ['success' => true];
    }

    public function getFieldset($fieldset)
    {
        $fieldset = $this->getInitialFieldset($fieldset);

        $fieldset->locale($this->request->input('locale', default_locale()));

        try {
            $array = $fieldset->toArray(bool($this->request->input('partials', true)));
        } catch (\Exception $e) {
            return response(['success' => false, 'message' => $e->getMessage()], 500);
        }

        if ($fieldset->name() === 'user') {
            // If logging in using emails, make sure there is no username field.
            if (Config::get('users.login_type') === 'email') {
                $array['fields'] = collect($array['fields'])->reject(function ($field) {
                    return $field['name'] === 'username';
                })->values()->all();
            }
        }

        // Default types of fieldsets should have their taxonomies formatted ready for the fields builder
        if ($fieldset->type() === 'default' && $this->request->editing) {
            $array['taxonomies'] = $this->formatTaxonomiesForEditing(
                array_get($array, 'taxonomies')
            );
        }

        if ($this->request->editing) {
            $array['fields'] = collect($array['fields'])->map(function ($field) {
                return $this->addConditions($field);
            });
        }

        return $array;
    }

    private function addConditions($field)
    {
        if (!isset($field['show_when']) && !isset($field['hide_when'])) {
            return $field;
        }

        $type = isset($field['show_when']) ? 'show' : 'hide';
        $conditions = $type === 'show' ? $field['show_when'] : $field['hide_when'];
        $style = is_string($conditions) ? 'custom' : 'standard';

        $field['conditions'] = [
            'type' => $type,
            'style' => $style,
            'custom' => $style === 'custom' ? $conditions : null,
            'conditions' => [],
        ];

        if (is_array($conditions)) {
            $field['conditions']['conditions'] = collect($conditions)->map(function ($values, $handle) {
                if (Str::startsWith($handle, 'or_')) {
                    $operator = 'or';
                    $handle = Str::removeLeft($handle, 'or_');
                }

                return [
                    'handle' => $handle,
                    'operator' => isset($operator) ? $operator : 'and',
                    'values' => Helper::ensureArray($values)
                ];
            })->values()->all();
        }

        return $field;
    }

    /**
     * @param string $fieldset  Name of the fieldset, as specified in the URL.
     * @return \Statamic\Contracts\CP\Fieldset
     */
    private function getInitialFieldset($fieldset)
    {
        // When using the builder to create a new fieldset, we need an object to work
        // with, but obviously one doesn't exist. So, we'll just use a temporary one.
        if ($fieldset === 'create' || $this->request->creating === 'true') {
            return Fieldset::create('temporary');
        }

        // Addon fieldsets will be specified using "addon.addonname.fieldsetname"
        if (substr_count($fieldset, '.') === 2) {
            return $this->getAddonFieldset($fieldset);
        }

        // Settings fieldsets will be specified using "settings.area"
        if (substr_count($fieldset, '.') === 1) {
            return $this->getSettingsFieldset($fieldset);
        }

        // Otherwise, just get a regular fieldset.
        return Fieldset::get($fieldset);
    }

    private function getAddonFieldset($fieldset)
    {
        list(, $addonName, $fieldsetName) = explode('.', $fieldset);

        if ($fieldsetName !== 'settings') {
            throw new \Exception('Cannot get non-settings fieldset.');
        }

        return Addon::create($addonName)->settingsFieldset();
    }

    private function getSettingsFieldset($fieldset)
    {
        list(, $fieldset) = explode('.', $fieldset);

        return Fieldset::get($fieldset, 'settings');
    }

    /**
     * Format taxonomies for editing
     *
     * Taxonomies exist in the fieldset file in one format, but the Vue component used
     * for editing them requires them in another format.
     *
     * @param array $array
     * @return array
     */
    private function formatTaxonomiesForEditing($array)
    {
        $taxonomies = collect();

        foreach (Taxonomy::all() as $handle => $taxonomy) {
            // Since we're adding all the taxonomies to the response, we'll
            // need another way to know which should and shouldn't be there.
            $hidden = false;

            // If *any* taxonomies have been defined, *and* the currently iterated taxonomy is not found,
            // we want to mark it as hidden. We check for *any* taxonomies being defined because if it's
            // completely empty, then *all* taxonomies should be shown.
            if (! is_null($array) && ! isset($array[$handle])) {
                $hidden = true;
            }

            $config = array_get($array, $handle, []);

            // Taxonomies can be defined by simply adding "true".
            $config = ($config === true) ? [] : $config;

            $defaults = [
                'taxonomy' => $handle,
                'display' => $taxonomy->title(),
                'hidden' => $hidden
            ];

            $taxonomies[$handle] = array_merge($defaults, $config);
        }

        // Sort the fields by the order in which they were provided.
        if (! empty($array)) {
            $taxonomies = $taxonomies->sortBy(function ($arr) use ($array, $taxonomies) {
                $handle = $arr['taxonomy'];

                // If the taxonomy was not provided, put it at the end.
                if (! isset($array[$handle])) {
                    return count($taxonomies);
                }

                return array_search($handle, array_keys($array));
            });
        }

        return $taxonomies->values()->all();
    }

    public function update($name)
    {
        $contents = $this->request->input('fieldset');

        $fieldset = $this->prepareFieldset($name, $contents);

        $fieldset->save();

        $this->success(translate('cp.fieldset_updated'));

        return [
            'success' => true,
            'redirect' => route('fieldset.edit', $fieldset->name())
        ];
    }

    private function process($fields, $fallback_type = 'text')
    {
        // Go through each field in the fieldset
        foreach ($fields as $field_name => $field_config) {
            // Get the config fieldset for that field's fieldtype. Still with me?
            $type = array_get($field_config, 'type', $fallback_type);
            $fieldtype = FieldtypeFactory::create($type);
            $fieldtypes = $fieldtype->getConfigFieldset()->fieldtypes();

            // Go through each fieldtype in the config fieldset and process the values.
            foreach ($fieldtypes as $k => $field) {
                // If a non-array is encountered, it's probably a "handle: true" used in the "taxonomies" section.
                if (! is_array($field_config)) {
                    continue;
                }

                if (! in_array($field->getName(), array_keys($field_config))) {
                    continue;
                }

                $fields[$field_name][$field->getName()] = $field->process($fields[$field_name][$field->getName()]);
            }
        }

        return $fields;
    }

    /**
     * Process conditions submitted through the Vue component.
     *
     * @param  array $contents  Fieldset contents.
     * @return array            The fieldset contents with condition syntax appropriately updated.
     */
    private function processConditions($contents)
    {
        $contents['fields'] = collect($contents['fields'])->map(function ($field) {
            return $this->processFieldConditions($field);
        })->all();

        return $contents;
    }

    /**
     * Process a single field's conditions.
     *
     * @param  array $config  The field's config.
     * @return array          The field's config, with condition syntax appropriately updated.
     */
    private function processFieldConditions($config)
    {
        unset($config['show_when'], $config['hide_when']);

        if (! $conditions = array_pull($config, 'conditions')) {
            return $config;
        }

        if (! $type = array_get($conditions, 'type')) {
            return $config;
        }

        $values = ($conditions['style'] === 'custom')
            ? $conditions['custom']
            : $this->processStandardFieldConditions($conditions['conditions']);

        $config[$type . '_when'] = $values;

        return $config;
    }

    private function processStandardFieldConditions($conditions)
    {
        return collect($conditions)->map(function ($condition) {
            $handle = $condition['handle'];

            if ($condition['operator'] === 'or') {
                $handle = 'or_' . $handle;
            }

            $values = $this->normalizeConditionValues($condition['values']);
            $values = (count($values) === 1) ? $values[0] : $values;

            return compact('handle', 'values');
        })->pluck('values', 'handle')->all();
    }

    private function normalizeConditionValues($values)
    {
        return collect($values)->map(function ($value) {
            switch ($value) {
                case 'true':
                    return true;
                case 'false':
                    return false;
                default:
                    return $value;
            }
        })->all();
    }

    public function updateLayout($fieldset)
    {
        $layout = collect($this->request->input('fields'))->keyBy('name')->toArray();

        $fieldset = Fieldset::get($fieldset);

        $contents = $fieldset->contents();

        $fields = array_get($contents, 'fields', []);

        $title_field = $fields['title'];

        $updated_fields = [];

        foreach ($layout as $name => $item) {
            $field = $fields[$name];

            if (isset($item['width'])) {
                $field['width'] = $item['width'];
            }

            $updated_fields[$name] = $field;
        }

        // Put back the title field at the front.
        $updated_fields = array_merge(['title' => $title_field], $updated_fields);

        $contents['fields'] = $updated_fields;

        $fieldset->contents($contents);

        $fieldset->save();
    }

    public function create()
    {
        return view('fieldsets.create', [
            'title' => 'Create fieldset'
        ]);
    }

    public function store()
    {
        $contents = $this->request->input('fieldset');

        $slug = $this->request->has('slug')
            ? $this->request->input('slug')
            : Str::slug(array_get($contents, 'title'), '_');

        $fieldset = $this->prepareFieldset($slug, $contents);

        $fieldset->save();

        $this->success(translate('cp.fieldset_created'));

        return [
            'success' => true,
            'redirect' => route('fieldset.edit', $fieldset->name())
        ];
    }

    /**
     * Quickly create a new barebones fieldset from within the fieldtype
     *
     * @return array
     */
    public function quickStore()
    {
        $title = $this->request->name;
        $name = Str::slug($title, '_');

        if (Fieldset::exists($name)) {
            return ['success' => true];
        }

        $fieldset = Fieldset::create($name);
        $fieldset->title($title);
        $fieldset->save();

        return ['success' => true];
    }

    private function prepareFieldset($slug, $contents)
    {
        $contents = $this->processConditions($contents);

        // We need to key the array by name
        $fields = [];
        foreach ($contents['fields'] as $field) {
            $field_name = $field['name'];
            unset($field['name']);
            $fields[$field_name] = $field;
        }

        $contents['fields'] = $this->process($fields);

        $contents['taxonomies'] = $this->processTaxonomies($contents['taxonomies']);

        $fieldset = Fieldset::create($slug, $contents);

        return $fieldset;
    }

    /**
     * Process taxonomies
     *
     * The "taxonomies" key of the fieldset will be submitted differently than
     * should be saved. It also needs to be ran through the fieldtype processor.
     *
     * @param array $taxonomies
     * @return array
     */
    private function processTaxonomies($taxonomies)
    {
        $taxonomies = collect($taxonomies)->reject(function ($item) {
            return $item['hidden'] === true;
        })->keyBy('taxonomy')->map(function ($item, $handle) {
            unset($item['hidden'], $item['taxonomy']);

            if ($item['display'] === Taxonomy::whereHandle($handle)->title()) {
                unset($item['display']);
            }

            $item = array_filter($item);

            // Visible taxonomy fields with no configuration are specified by "true"
            if (empty($item)) {
                return true;
            }

            return $item;
        });

        // If everything should be hidden, we specify that with "false"
        if ($taxonomies->isEmpty()) {
            return false;
        }

        return $this->process($taxonomies->all(), 'taxonomy');
    }
}
