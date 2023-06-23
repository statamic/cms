<?php

namespace Statamic\Fields;

use Facades\Statamic\Fields\FieldRepository;
use Facades\Statamic\Fields\Validator;
use Illuminate\Support\Collection;
use Statamic\Facades\Blink;
use Statamic\Facades\Fieldset as FieldsetRepository;
use Statamic\Support\Arr;

class Fields
{
    protected $items;
    protected $fields;
    protected $parent;
    protected $parentField;
    protected $filled = [];
    protected $withValidatableValues = false;
    protected $withComputedValues = false;

    public function __construct($items = [], $parent = null, $parentField = null)
    {
        $this
            ->setParent($parent)
            ->setParentField($parentField)
            ->setItems($items);
    }

    public function setItems($items)
    {
        if ($items instanceof Collection) {
            $items = $items->all();
        }

        $this->items = collect($items);

        $this->fields = $this->resolveFields()->keyBy->handle();

        return $this;
    }

    public function setFields($fields)
    {
        $this->fields = $fields;

        return $this;
    }

    public function setParent($parent)
    {
        $this->parent = $parent;

        if ($this->fields) {
            $this->fields->each(fn ($f) => $f->setParent($parent));
        }

        return $this;
    }

    public function setParentField($field)
    {
        $this->parentField = $field;

        if ($this->fields) {
            $this->fields->each(fn ($f) => $f->setParentField($field));
        }

        return $this;
    }

    public function setFilled($dottedKeys)
    {
        $this->filled = $dottedKeys;

        return $this;
    }

    public function withValidatableValues()
    {
        $this->withValidatableValues = true;

        return $this;
    }

    public function withComputedValues()
    {
        $this->withComputedValues = true;

        return $this;
    }

    public function items()
    {
        return $this->items;
    }

    public function all(): Collection
    {
        return $this->fields;
    }

    public function except(...$keys): self
    {
        return tap(new static)->setFields($this->fields->except(...$keys));
    }

    public function only(...$keys): self
    {
        return tap(new static)->setFields($this->fields->only(...$keys));
    }

    public function newInstance()
    {
        return (new static)
            ->setParent($this->parent)
            ->setParentField($this->parentField)
            ->setItems($this->items)
            ->setFields($this->fields)
            ->setFilled($this->filled);
    }

    public function localizable()
    {
        return $this->newInstance()->setFields(
            $this->fields->filter->isLocalizable()
        );
    }

    public function unlocalizable()
    {
        return $this->newInstance()->setFields(
            $this->fields->reject->isLocalizable()
        );
    }

    public function has($field)
    {
        return $this->fields->has($field);
    }

    public function get($field)
    {
        return $this->fields->get($field);
    }

    public function toPublishArray()
    {
        return $this->fields->values()->map->toPublishArray()->all();
    }

    public function addValues(array $values)
    {
        $filled = array_keys($values);

        $fields = $this->fields->map(function ($field) use ($values) {
            return $field->newInstance()->setValue(Arr::get($values, $field->handle()));
        });

        return $this->newInstance()->setFilled($filled)->setFields($fields);
    }

    public function values()
    {
        return $this->fields
            ->reject(function ($field) {
                return $this->withComputedValues === false
                    ? $field->visibility() === 'computed'
                    : false;
            })
            ->mapWithKeys(function ($field) {
                return [$field->handle() => $field->value()];
            })
            ->filter(function ($field, $handle) {
                return $this->withValidatableValues
                    ? in_array($handle, $this->filled)
                    : true;
            });
    }

    public function process()
    {
        return $this->newInstance()->setFields(
            $this->fields->map->process()
        );
    }

    public function preProcess()
    {
        return $this->newInstance()->setFields(
            $this->fields->map->preProcess()
        )->withComputedValues();
    }

    public function preProcessValidatables()
    {
        return $this->newInstance()->setFields(
            $this->fields->map->preProcessValidatable()
        )->withValidatableValues();
    }

    public function augment()
    {
        return $this->newInstance()->setFields(
            $this->fields->map->augment()
        );
    }

    public function shallowAugment()
    {
        return $this->newInstance()->setFields(
            $this->fields->map->shallowAugment()
        );
    }

    public function resolveFields()
    {
        return $this->items->flatMap(function ($config) {
            return $this->createFields($config);
        })->values();
    }

    public function createFields(array $config): array
    {
        if (isset($config['import'])) {
            return $this->getImportedFields($config);
        }

        return [$this->createField($config)];
    }

    private function createField(array $config)
    {
        // If "field" is a string, it's a reference to a field in a fieldset.
        if (is_string($config['field'])) {
            return $this->getReferencedField($config);
        }

        // Otherwise, the field has been configured inline.
        return $this->newField($config['handle'], $config['field']);
    }

    protected function newField($handle, $config)
    {
        return (new Field($handle, $config))
            ->setParent($this->parent)
            ->setParentField($this->parentField);
    }

    private function getReferencedField(array $config): Field
    {
        if (! $field = FieldRepository::find($config['field'])) {
            throw new \Exception("Field {$config['field']} not found.");
        }

        if ($overrides = array_get($config, 'config')) {
            $field->setConfig(array_merge($field->config(), $overrides));
        }

        return $field->setParent($this->parent)->setHandle($config['handle']);
    }

    private function getImportedFields(array $config): array
    {
        $blink = 'blueprint-imported-fields-'.md5(json_encode($config));

        return Blink::once($blink, function () use ($config) {
            if (! $fieldset = FieldsetRepository::find($config['import'])) {
                throw new \Exception("Fieldset {$config['import']} not found.");
            }

            $fields = $fieldset->fields()->all();

            if ($overrides = $config['config'] ?? null) {
                $fields = $fields->map(function ($field, $handle) use ($overrides) {
                    return $field->setConfig(array_merge($field->config(), $overrides[$handle] ?? []));
                });
            }

            if ($prefix = array_get($config, 'prefix')) {
                $fields = $fields->mapWithKeys(function ($field) use ($prefix) {
                    $field = clone $field;
                    $handle = $prefix.$field->handle();
                    $prefix = $prefix.$field->prefix();

                    return [$handle => $field->setHandle($handle)->setPrefix($prefix)];
                });
            }

            return $fields;
        })->each->setParent($this->parent)->all();
    }

    public function meta()
    {
        return $this->fields->map->meta();
    }

    public function validator()
    {
        return Validator::make()->fields($this);
    }

    public function validate($extraRules = [], $customMessages = [])
    {
        return $this->validator()->withRules($extraRules)->withMessages($customMessages)->validate();
    }

    public function toGql()
    {
        return $this->fields->map->toGql();
    }
}
