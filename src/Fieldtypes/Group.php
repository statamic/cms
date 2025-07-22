<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\GraphQL;
use Statamic\Fields\Fields;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\Values;
use Statamic\GraphQL\Types\GroupType;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class Group extends Fieldtype
{
    protected $categories = ['structured'];
    protected $defaultable = false;
    protected $selectableInForms = true;

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Fields'),
                'fields' => [
                    'fields' => [
                        'display' => __('Fields'),
                        'instructions' => __('statamic::fieldtypes.group.config.fields'),
                        'type' => 'fields',
                        'full_width_setting' => true,
                    ],
                ],
            ],
            [
                'display' => __('Appearance & Behavior'),
                'fields' => [
                    'fullscreen' => [
                        'display' => __('Allow Fullscreen Mode'),
                        'instructions' => __('statamic::fieldtypes.grid.config.fullscreen'),
                        'type' => 'toggle',
                        'default' => true,
                    ],
                    'border' => [
                        'display' => __('Border'),
                        'instructions' => __('statamic::fieldtypes.grid.config.border'),
                        'type' => 'toggle',
                        'default' => true,
                    ],
                ],
            ],
        ];
    }

    public function process($data)
    {
        $values = $this->fields()->addValues($data ?? [])->process()->values()->all();

        return Arr::removeNullValues($values);
    }

    public function preProcess($data)
    {
        return $this->fields()->addValues($data ?? [])->preProcess()->values()->all();
    }

    public function fields()
    {
        return new Fields($this->config('fields'), $this->field()->parent(), $this->field());
    }

    public function rules(): array
    {
        return ['array'];
    }

    public function extraRules(): array
    {
        $rules = $this
            ->fields()
            ->addValues((array) $this->field->value())
            ->validator()
            ->withContext([
                'prefix' => $this->field->validationContext('prefix'),
            ])
            ->rules();

        return collect($rules)->mapWithKeys(function ($rules, $handle) {
            return [$this->field->handle().'.'.$handle => $rules];
        })->all();
    }

    public function extraValidationAttributes(): array
    {
        return collect($this->fields()->validator()->attributes())->mapWithKeys(function ($attribute, $handle) {
            return [$this->field->handle().'.'.$handle => $attribute];
        })->all();
    }

    public function preload()
    {
        return $this->fields()->addValues($this->field->value() ?? $this->defaultGroupData())->meta()->toArray();
    }

    protected function defaultGroupData()
    {
        return $this->fields()->all()->map(function ($field) {
            return $field->fieldtype()->preProcess($field->defaultValue());
        })->all();
    }

    public function augment($value)
    {
        return $this->performAugmentation($value, false);
    }

    public function shallowAugment($value)
    {
        return $this->performAugmentation($value, true);
    }

    private function performAugmentation($value, $shallow)
    {
        $method = $shallow ? 'shallowAugment' : 'augment';

        return new Values($this->fields()->addValues($value ?? [])->{$method}()->values()->all());
    }

    public function preProcessValidatable($value)
    {
        return array_merge(
            $value ?? [],
            $this->fields()
                ->addValues($value ?? [])
                ->preProcessValidatables()
                ->values()
                ->all(),
        );
    }

    public function preProcessTagRenderable($data, $recursiveCallback)
    {
        $field = $this->field();

        $data['fields'] = collect($this->fields()->all())
            ->map(fn ($child) => $child->setForm($field->form())->setHandle($field->handle().'.'.$child->handle()))
            ->map(fn ($child) => $recursiveCallback($child))
            ->values()
            ->all();

        return $data;
    }

    public function toGqlType()
    {
        return GraphQL::type($this->gqlItemTypeName());
    }

    public function addGqlTypes()
    {
        GraphQL::addType(new GroupType($this, $this->gqlItemTypeName()));

        $this->fields()->all()->each(function ($field) {
            $field->fieldtype()->addGqlTypes();
        });
    }

    private function gqlItemTypeName()
    {
        return 'Group_'.collect($this->field->handlePath())->map(function ($part) {
            return Str::studly($part);
        })->join('_');
    }

    public function hasJsDriverDataBinding(): bool
    {
        return false;
    }
}
