<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\Icon as Icons;
use Statamic\Fields\Fieldtype;
use Statamic\Icons\IconSet;
use Statamic\Support\Str;

use function Statamic\trans as __;

class Icon extends Fieldtype
{
    protected $categories = ['media'];
    protected $icon = 'fieldtype-icon_picker';

    public function preload(): array
    {
        return [
            'url' => cp_route('icon-fieldtype'),
        ];
    }

    public function icons()
    {
        $set = $this->iconSet();

        return $set->name() === 'default'
            ? $set->names()->mapWithKeys(fn ($name) => [$name => null])->all()
            : $set->contents();
    }

    protected function configFieldItems(): array
    {
        $sections = [];

        if (Icons::sets()->isNotEmpty()) {
            $sections[] = [
                'display' => __('Selection'),
                'fields' => [
                    'set' => [
                        'display' => __('Icon Set'),
                        'instructions' => __('statamic::fieldtypes.icon.config.set'),
                        'type' => 'select',
                        'default' => 'default',
                        'options' => $this->iconSetOptions(),
                        'width' => 50,
                    ],
                ],
            ];
        }

        $sections[] = [
            'display' => __('Appearance'),
            'fields' => [
                'mode' => [
                    'display' => __('UI Mode'),
                    'instructions' => __('statamic::fieldtypes.icon.config.mode'),
                    'type' => 'button_group',
                    'default' => 'default',
                    'options' => [
                        'default' => __('Default'),
                        'compact' => __('Compact'),
                    ],
                    'width' => 50,
                ],
            ],
        ];

        $sections[] = [
            'display' => __('Data & Format'),
            'fields' => [
                'default' => [
                    'display' => __('Default Icon'),
                    'instructions' => __('statamic::messages.fields_default_instructions'),
                    'type' => 'text',
                    'width' => 50,
                ],
            ],
        ];

        return $sections;
    }

    public function augment($value)
    {
        if (! $value) {
            return null;
        }

        return $this->iconSet()->get($value);
    }

    private function iconSet(): IconSet
    {
        return Icons::get($this->config('set', 'default'));
    }

    private function iconSetOptions(): array
    {
        return Icons::sets()
            ->mapWithKeys(fn (IconSet $set) => [$set->name() => Str::headline($set->name())])
            ->prepend(__('Default'), 'default')
            ->all();
    }
}
