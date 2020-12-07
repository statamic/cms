<?php

namespace Statamic\Fields;

use Illuminate\Contracts\Support\Arrayable;
use Statamic\Extend\HasHandle;
use Statamic\Extend\HasTitle;
use Statamic\Extend\RegistersItself;
use Statamic\Query\Scopes\Filters\Fields\FieldtypeFilter;
use Statamic\Statamic;
use Statamic\Support\Str;

abstract class Fieldtype implements Arrayable
{
    use RegistersItself, HasTitle, HasHandle {
        handle as protected traitHandle;
    }

    protected static $binding = 'fieldtypes';

    protected $field;
    protected $localizable = true;
    protected $validatable = true;
    protected $defaultable = true;
    protected $selectable = true;
    protected $categories = ['text'];
    protected $rules = [];
    protected $extraRules = [];
    protected $defaultValue;
    protected $configFields = [];
    protected $icon;

    public function setField(Field $field)
    {
        $this->field = $field;

        return $this;
    }

    public function field(): ?Field
    {
        return $this->field;
    }

    public static function handle()
    {
        return Str::removeRight(static::traitHandle(), '_fieldtype');
    }

    public function component(): string
    {
        return $this->component ?? static::handle();
    }

    public function indexComponent(): string
    {
        return $this->indexComponent ?? static::handle();
    }

    public function localizable(): bool
    {
        return $this->localizable;
    }

    public function validatable(): bool
    {
        return $this->validatable;
    }

    public function defaultable(): bool
    {
        return $this->defaultable;
    }

    public function selectable(): bool
    {
        return $this->selectable;
    }

    public function categories(): array
    {
        return $this->categories;
    }

    public function filter()
    {
        return new FieldtypeFilter($this);
    }

    public function rules(): array
    {
        return Validator::explodeRules($this->rules);
    }

    public function extraRules(): array
    {
        return array_map([Validator::class, 'explodeRules'], $this->extraRules);
    }

    public function preProcessValidatable($value)
    {
        return $value;
    }

    public function defaultValue()
    {
        return $this->defaultValue;
    }

    public function augment($value)
    {
        return $value;
    }

    public function shallowAugment($value)
    {
        return $this->augment($value);
    }

    public function toArray(): array
    {
        return [
            'handle' => $this->handle(),
            'title' => $this->title(),
            'localizable' => $this->localizable(),
            'validatable' => $this->validatable(),
            'defaultable' => $this->defaultable(),
            'selectable'  => $this->selectable(),
            'categories' => $this->categories(),
            'icon' => $this->icon(),
            'config' => $this->configFields()->toPublishArray(),
        ];
    }

    public function configBlueprint(): Blueprint
    {
        return (new Blueprint)->setContents([
            'fields' => $this->configFields()->items()->all(),
        ]);
    }

    public function configFields(): Fields
    {
        $fields = collect($this->configFieldItems())->map(function ($field, $handle) {
            return compact('handle', 'field');
        });

        return new ConfigFields($fields);
    }

    protected function configFieldItems(): array
    {
        return $this->configFields;
    }

    public function icon()
    {
        return $this->icon ?? $this->handle();
    }

    public function process($data)
    {
        return $data;
    }

    public function preProcess($data)
    {
        return $data;
    }

    public function preProcessConfig($data)
    {
        return $this->preProcess($data);
    }

    public function preProcessIndex($data)
    {
        return $data;
    }

    public function view()
    {
        $default = 'statamic::forms.fields.'.$this->handle();

        return view()->exists($default)
            ? $default
            : 'statamic::forms.fields.default';
    }

    public function config(string $key = null, $fallback = null)
    {
        if (! $this->field) {
            return $fallback;
        }

        return $key
            ? $this->field->get($key, $fallback)
            : $this->field->config();
    }

    public function preload()
    {
        //
    }

    public static function preloadable()
    {
        return static::$preloadable ?? (new \ReflectionClass(static::class))->getMethod('preload')->class === static::class;
    }

    public static function docsUrl()
    {
        return Statamic::docsUrl('fieldtypes/'.static::handle());
    }

    public function graphQLType(): \GraphQL\Type\Definition\Type
    {
        return \GraphQL\Type\Definition\Type::string();
    }

    public function addGqlTypes()
    {
        //
    }
}
