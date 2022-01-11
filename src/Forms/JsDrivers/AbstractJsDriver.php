<?php

namespace Statamic\Forms\JsDrivers;

use Statamic\Support\Str;

abstract class AbstractJsDriver implements JsDriver
{
    public $options;

    /**
     * Instantiate JS driver.
     *
     * @param  array  $options
     */
    public function __construct($options = [])
    {
        $this->options = $options;

        if (method_exists($this, 'parseOptions')) {
            $this->parseOptions();
        }
    }

    /**
     * Get JS driver handle from class name.
     *
     * @return string
     */
    public function handle()
    {
        $className = (new \ReflectionClass($this))->getShortName();

        return Str::snake($className);
    }

    /**
     * Add to form tag attributes.
     *
     * @param  array  $attrs
     * @param  \Statamic\Forms\Form  $form
     * @return array
     */
    public function addToFormAttributes($attrs, $form)
    {
        return [];
    }

    /**
     * Add to form view data.
     *
     * @param  array  $data
     * @param  \Statamic\Forms\Form  $form
     * @return array
     */
    public function addToFormData($data, $form)
    {
        return [];
    }

    /**
     * Add to renderable field view data.
     *
     * @param  array  $data
     * @param  \Statamic\Fields\Field  $field
     * @return array
     */
    public function addToRenderableFieldData($data, $field)
    {
        return [];
    }

    /**
     * Copy renderable `show_field` JS from each individual field to top-level form data.
     *
     * @param  array  $fields
     * @return array
     */
    public function copyShowFieldToFormData($fields)
    {
        return $this->validateShowFieldDefined(collect($fields))->pluck('show_field', 'handle')->all();
    }

    /**
     * Validate that `show_field` is defined in `addToRenderableFieldData()` output.
     *
     * @param  \Illuminate\Support\Collection  $fields
     * @return \Illuminate\Support\Collection
     */
    protected function validateShowFieldDefined($fields)
    {
        return $fields->each(function ($field) {
            if (! isset($field['show_field'])) {
                throw new \Exception('JS driver requires [show_field] to be defined in [addToRenderableFieldData()] output!');
            }
        });
    }

    /**
     * Json encode for html attribute.
     *
     * @param  mixed  $value
     * @return string
     */
    protected function jsonEncodeForHtmlAttribute($value)
    {
        return str_replace('"', '\'', json_encode($value));
    }

    /**
     * Register driver with Statamic.
     */
    public static function register()
    {
        if (! app()->has('statamic.form-js-drivers')) {
            return;
        }

        $handle = (new static)->handle();

        app('statamic.form-js-drivers')[$handle] = static::class;
    }
}
