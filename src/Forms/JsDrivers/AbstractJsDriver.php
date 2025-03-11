<?php

namespace Statamic\Forms\JsDrivers;

use Illuminate\Support\Collection;
use Statamic\Forms\Form;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\Tags\Parameters;

abstract class AbstractJsDriver implements JsDriver
{
    protected $form;
    protected $options;
    protected $params;

    /**
     * Instantiate JS driver.
     *
     * @param  array  $options
     */
    public function __construct(Form $form, $options = [], ?Parameters $params = null)
    {
        $this->form = $form;
        $this->options = $options;
        $this->params = $params;

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
     */
    public function copyShowFieldToFormData(array $fields): array
    {
        return $this
            ->flattenFields($fields)
            ->each(fn ($field) => $this->validateShowFieldDefined($field))
            ->pluck('show_field', 'handle')
            ->all();
    }

    /**
     * Validate that `show_field` is defined in `addToRenderableFieldData()` output.
     *
     * @throws \Exception
     */
    protected function validateShowFieldDefined(array $field): void
    {
        if (! isset($field['show_field'])) {
            throw new \Exception('JS driver requires [show_field] to be defined in [addToRenderableFieldData()] output!');
        }
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
     */
    protected function getInitialFormData(): array
    {
        $oldValues = collect(old());

        return $this->form
            ->blueprint()
            ->fields()
            ->preProcess()
            ->values()
            ->when($this->form->honeypot(), fn ($fields, $honeypot) => $fields->merge([$honeypot => null]))
            ->map(fn ($default, $handle) => $oldValues->has($handle) ? $oldValues->get($handle) : $default)
            ->all();
    }

    /**
     * Recursively get flattened fields collection.
     */
    protected function flattenFields(array|Collection $fields): Collection
    {
        return collect($fields)->flatMap(fn ($field) => [
            $field,
            ...$this->flattenFields(Arr::get($field, 'fields') ?? [])->all(),
        ]);
    }

    /**
     * Get JS driver handle from class name.
     */
    public static function handle(): string
    {
        $className = collect(explode('\\', static::class))->last();

        return Str::snake($className);
    }

    /**
     * Register driver with Statamic.
     */
    public static function register(): void
    {
        if (! app()->has('statamic.form-js-drivers')) {
            return;
        }

        $handle = static::handle();

        app('statamic.form-js-drivers')[$handle] = static::class;
    }
}
