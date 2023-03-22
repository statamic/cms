<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\Folder;
use Statamic\Fields\Fieldtype;

class IconPicker extends Fieldtype
{
    protected $categories = ['media'];
    protected $selectable = false;

    public function preload(): array
    {
        $folder = $this->config('folder', 'resources/svg');

        $icons = collect(Folder::getFilesByType(statamic_path($folder), 'svg'))->map(function ($file) {
            return pathinfo($file)['filename'];
        });

        return [
            'icons' => $icons->all(),
        ];
    }

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Selection'),
                'fields' => [
                    'folder' => [
                        'display' => __('Folder'),
                        'instructions' => __('statamic::fieldtypes.icon_picker.config.folder'),
                        'type' => 'text',
                    ],
                    'default' => [
                        'display' => __('Default Value'),
                        'instructions' => __('statamic::messages.fields_default_instructions'),
                        'type' => 'text',
                    ],
                ],
            ],
        ];
    }
}
