<?php

namespace Statamic\Forms;

use Carbon\Carbon;
use Statamic\API\Helper;
use Statamic\API\Storage;
use Statamic\Exceptions\PublishException;
use Statamic\Exceptions\SilentFormFailureException;
use Statamic\Contracts\Forms\Submission as SubmissionContract;

class Submission implements SubmissionContract
{
    /**
     * @var bool
     */
    private $guard = true;

    /**
     * @var string
     */
    private $id;

    /**
     * @var Form
     */
    public $form;

    /**
     * @var array
     */
    private $data = [];

    /**
     * Get or set the ID
     *
     * @param mixed|null
     * @return mixed
     */
    public function id($id = null)
    {
        if (is_null($id)) {
            return $this->id ?: time();
        }

        $this->id = $id;
    }

    /**
     * Get or set the form
     *
     * @param Form|null $form
     * @return Form
     */
    public function form($form = null)
    {
        if (is_null($form)) {
            return $this->form;
        }

        $this->form = $form;
    }

    /**
     * Get the formset
     *
     * @return Formset
     */
    public function formset()
    {
        return $this->form()->formset();
    }

    /**
     * Get the fields in the formset
     *
     * @return array
     */
    public function fields()
    {
        return $this->formset()->fields();
    }

    /**
     * Get or set the columns
     *
     * @return array
     */
    public function columns()
    {
        return $this->formset()->columns();
    }

    /**
     * Get the date when this was submitted
     *
     * @return Carbon
     */
    public function date()
    {
        return Carbon::createFromTimestamp($this->id());
    }

    /**
     * Get the date, formatted by what's specified in the formset
     *
     * @return string
     */
    public function formattedDate()
    {
        return $this->date()->format(
            $this->form()->dateFormat()
        );
    }

    /**
     * Disable validation
     */
    public function unguard()
    {
        $this->guard = false;
    }

    /**
     * Enable validation
     */
    public function guard()
    {
        $this->guard = true;
    }

    /**
     * Get or set the data
     *
     * @param array|null $data
     * @return array
     * @throws PublishException|HoneypotException
     */
    public function data($data = null)
    {
        if (is_null($data)) {
            return $this->data;
        }

        // If a honeypot exists, throw an exception.
        if (array_get($data, $this->formset()->get('honeypot', 'honeypot'))) {
            throw new SilentFormFailureException('Honeypot field has been populated.');
        }

        if ($this->guard) {
            $this->validate($data);

            // Remove any fields that aren't present in the formset.
            $data = array_intersect_key($data, array_flip(array_keys($this->fields())));
        }

        $this->data = $data;
    }

    /**
     * Upload files
     */
    public function uploadFiles()
    {
        $request = request();

        collect($this->fields())->filter(function ($field) {
            // Only deal with uploadable fields
            return in_array(array_get($field, 'type'), ['file', 'files', 'asset', 'assets']);

        })->map(function ($config, $field) {
            // Map into a nicer data schema to work with
            return compact('field', 'config');

        })->reject(function ($arr) use ($request) {
            // Remove if no file was uploaded
            return !$request->hasFile($arr['field']);

        })->map(function ($arr, $field) use ($request) {
            // Add the uploaded files to our data array
            $files = collect(array_filter(Helper::ensureArray($request->file($field))));
            $arr['files'] = $files;
            return $arr;

        })->each(function ($arr) {
            // A plural type uses the singular version. assets => asset, etc.
            $type = rtrim(array_get($arr, 'config.type'), 's');

            // Upload the files
            $class = 'Statamic\Forms\Uploaders\\'.ucfirst($type).'Uploader';
            $uploader = new $class(array_get($arr, 'config'), array_get($arr, 'files'));
            $data = $uploader->upload();

            // Add the resulting paths to our submission
            array_set($this->data, $arr['field'], $data);
        });
    }

    /**
     * Validate an array of data against rules in the formset
     *
     * @param  array $data       Data to validate
     * @throws PublishException  An exception will be thrown if it doesn't validate
     */
    private function validate($data)
    {
        $rules = [];
        $attributes = [];

        // Merge in field rules
        foreach ($this->fields() as $field_name => $field_config) {
            if ($field_rules = array_get($field_config, 'validate')) {
                $rules[$field_name] = $field_rules;
            }

            // Define the attribute (friendly name) so it doesn't appear as field.fieldname
            $attributes[$field_name] = translate('cp.attribute_field_name', [
                'attribute' => array_get($field_config, 'display', $field_name),
            ]);
        }

        $validator = app('validator')->make($data, $rules, [], $attributes);

        if ($validator->fails()) {
            $e = new PublishException;
            $e->setErrors($validator->errors()->toArray());
            throw $e;
        }
    }

    /**
     * Get a value of a field
     *
     * @param  string $key
     * @return mixed
     */
    public function get($field)
    {
        return array_get($this->data(), $field);
    }

    /**
     * Set a value of a field
     *
     * @param string $field
     * @param mixed  $value
     * @return void
     */
    public function set($field, $value)
    {
        array_set($this->data, $field, $value);
    }

    /**
     * Save the submission
     */
    public function save()
    {
        $filename = 'forms/' . $this->formset()->name() . '/' . $this->id();

        Storage::putYAML($filename, $this->data());
    }

    /**
     * Delete this submission
     */
    public function delete()
    {
        Storage::delete($this->getPath());
    }

    /**
     * Get the path to the file
     *
     * @return string
     */
    public function getPath()
    {
        return 'forms/' . $this->formset()->name() . '/' . $this->id() . '.yaml';
    }

    /**
     * Convert to an array
     *
     * @return array
     */
    public function toArray()
    {
        $data = $this->data();
        $data['id'] = $this->id();
        $data['date'] = $this->date();
        $fields = $this->formset()->fields();
        $field_names = array_keys($fields);

        // Populate the missing fields with empty values.
        foreach ($field_names as $field) {
            $data[$field] = array_get($data, $field);
        }

        // Ensure the array is ordered the same way the fields are.
        $data = array_merge(array_flip($field_names), $data);

        return $data;
    }
}
