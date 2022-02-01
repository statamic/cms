<?php

namespace Statamic\Forms\JsDrivers;

use Statamic\Forms\Form;
use Statamic\Support\Str;

abstract class AbstractJsDriver implements JsDriver
{
    protected $form;
    protected $options;

    /**
     * Instantiate JS driver.
     *
     * @param  Form  $form
     * @param  array  $options
     */
    public function __construct(Form $form, $options = [])
    {
        $this->form = $form;
        $this->options = $options;

        if (method_exists($this, 'parseOptions')) {
            $this->parseOptions($options);
        }

        $this->validateRenderMethodReturnsHtml();
    }

    /**
     * Add to form view data.
     *
     * @param  array  $data
     * @return array
     */
    public function addToFormData($data)
    {
        return [];
    }

    /**
     * Add to form html tag attributes.
     *
     * @return array
     */
    public function addToFormAttributes()
    {
        return [];
    }

    /**
     * Add to renderable field view data.
     *
     * @param  \Statamic\Fields\Field  $field
     * @param  array  $data
     * @return array
     */
    public function addToRenderableFieldData($field, $data)
    {
        return [];
    }

    /**
     * Add to renderable field html tag attributes.
     *
     * @param  \Statamic\Fields\Field  $field
     * @return array
     */
    public function addToRenderableFieldAttributes($field)
    {
        return [];
    }

    /**
     * Render form html.
     *
     * @param  string  $html
     * @return string
     */
    public function render($html)
    {
        return $html;
    }

    /**
     * Copy renderable `show_field` JS from each individual field for hardcoding field html using top-level form data.
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
     *
     * @throws \Exception
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
     * Validate that render method returns `$html` var.
     *
     * @throws \Exception
     */
    protected function validateRenderMethodReturnsHtml()
    {
        if (! Str::contains($this->render('<VALIDATING-HTML />'), '<VALIDATING-HTML />')) {
            throw new \Exception('JS driver requires [$html] to be returned in [render()] output!');
        }
    }

    /**
     * Get initial form data.
     *
     * @return array
     */
    protected function getInitialFormData()
    {
        $oldValues = collect(old());

        return $this->form
            ->blueprint()
            ->fields()
            ->preProcess()
            ->values()
            ->map(function ($defaultProcessedValue, $handle) use ($oldValues) {
                return $oldValues->has($handle)
                    ? $oldValues->get($handle)
                    : $defaultProcessedValue;
            })
            ->all();
    }

    /**
     * Get JS driver handle from class name.
     *
     * @return string
     */
    public static function handle()
    {
        $className = collect(explode('\\', static::class))->last();

        return Str::snake($className);
    }

    /**
     * Register driver with Statamic.
     */
    public static function register()
    {
        if (! app()->has('statamic.form-js-drivers')) {
            return;
        }

        $handle = static::handle();

        app('statamic.form-js-drivers')[$handle] = static::class;
    }
}
