<?php

namespace Statamic\Http\Controllers;

use Statamic\API\Config;
use Statamic\API\Folder;
use Statamic\API\Fieldset;
use Statamic\API\File;
use Statamic\API\YAML;
use Statamic\API\Helper;
use Statamic\API\Cache;
use Statamic\API\Stache;
use Statamic\Config\Settings;

class SettingsController extends CpController
{
    protected $name;

    public function index()
    {
        return redirect()->route('settings.edit', 'system');
    }

    public function edit($name)
    {
        $data = Config::get($name);

        $fieldset = Fieldset::get($name, 'settings');

        $data = $this->preProcessData($data, $fieldset);

        $data = $this->populateWithBlanks($fieldset, $data);

        return view('settings.edit', [
            'title' => t('settings_'.$name),
            'extra' => [
                'env' => array_get(app(Settings::class)->env(), $name)
            ],
            'slug' => $name,
            'content_data' => $data,
            'content_type' => 'settings',
            'fieldset' => 'settings.'.$name,
        ]);
    }

    public function update($name)
    {
        $this->name = $name;

        $data = $this->processFields();

        // Replace environment-managed values with their raw equivalents

        if ($environment_variables = $this->request->input('extra.env')) {
            foreach ($environment_variables as $key => $env) {
                $data[$key] = $env['raw'];
            }
        }

        $contents = YAML::dump($data);

        $file = settings_path($name . '.yaml');
        File::put($file, $contents);

        Cache::clear();
        Stache::clear();

        $this->success('Settings updated');

        return ['success' => true, 'redirect' => route('settings.edit', $name)];
    }

    private function processFields()
    {
        $fieldset = Fieldset::get($this->name, 'settings');
        $data = $this->request->input('fields');

        foreach ($fieldset->fieldtypes() as $field) {
            if (! in_array($field->getName(), array_keys($data))) {
                continue;
            }

            $data[$field->getName()] = $field->process($data[$field->getName()]);
        }

        // Get rid of null fields
        $data = array_filter($data, function($value) {
            return !is_null($value);
        });

        return $data;
    }

    /**
     * Create the data array, populating it with blank values for all fields in
     * the fieldset, then overriding with the actual data where applicable.
     *
     * @param string $fieldset
     * @param array $data
     * @return array
     */
    private function populateWithBlanks($fieldset, $data)
    {
        // Get the fieldtypes
        $fieldtypes = collect($fieldset->fieldtypes())->keyBy(function($ft) {
            return $ft->getName();
        });

        // Build up the blanks
        $blanks = [];
        foreach ($fieldset->fields() as $name => $config) {
            $blanks[$name] = $fieldtypes->get($name)->blank();
        }

        return array_merge($blanks, $data);
    }

    private function preProcessData($data, $fieldset)
    {
        $fieldtypes = collect($fieldset->fieldtypes())->keyBy(function($fieldtype) {
            return $fieldtype->getFieldConfig('name');
        });

        foreach ($data as $field_name => $field_data) {
            if ($fieldtype = $fieldtypes->get($field_name)) {
                $data[$field_name] = $fieldtype->preProcess($field_data);
            }
        }

        return $data;
    }

    public function licenseKey()
    {
        Config::set('system.license_key', $this->request->input('key'));

        return back();
    }
}
