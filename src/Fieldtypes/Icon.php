<?php

namespace Statamic\Fieldtypes;

use Statamic\CP\Icons\IconSet;
use Statamic\Facades\CP\Icon as Icons;
use Statamic\Fields\Fieldtype;

class Icon extends Fieldtype
{
    protected $categories = ['media'];
    protected $icon = 'icon_picker';

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
        return [
            [
                'display' => __('Selection'),
                'fields' => [
                    'set' => [
                        'display' => __('Icon Set'),
                        'instructions' => __('statamic::fieldtypes.icon.config.set'),
                        'type' => 'text',
                        'width' => 50,
                    ],
                    'default' => [
                        'display' => __('Default Icon'),
                        'instructions' => __('statamic::messages.fields_default_instructions'),
                        'type' => 'text',
                        'width' => 50,
                    ],
                ],
            ],
        ];
    }

    public function augment($value)
    {
        return $this->iconSet()->get($value);
    }

    private function iconSet(): IconSet
    {
        return Icons::get($this->config('set', 'default'));
    }
}
