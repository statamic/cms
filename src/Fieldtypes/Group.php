<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fields;
use Statamic\Fields\Fieldtype;

class Group extends Fieldtype
{
    protected $categories = ['structured'];
    protected $defaultable = false;
    protected $defaultValue = [];

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Fields'),
                'fields' => [
                    'fields' => [
                        'display' => __('Fields'),
                        'instructions' => '',
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
                ],
            ],
        ];
    }

    public function process($data)
    {
        return $this->fields()->addValues($data ?? [])->process()->values()->all();
    }

    public function preProcess($data)
    {
        return $this->fields()->addValues($data ?? [])->preProcess()->values()->all();
    }

    public function fields()
    {
        return new Fields($this->config('fields'), $this->field()->parent(), $this->field());
    }

    public function preload()
    {
        return $this->fields()->addValues($this->field->value() ?? $this->defaultGroupData())->meta()->toArray();
    }

    protected function defaultGroupData()
    {
        return $this->fields()->all()->map(function ($field) {
            return $field->fieldtype()->preProcess($field->defaultValue());
        });
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

        return $this->fields()->addValues($value ?? [])->{$method}()->values()->all();
    }
}
