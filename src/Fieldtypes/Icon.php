<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\CP\Icon as Icons;
use Statamic\Facades\File;
use Statamic\Facades\Folder;
use Statamic\Facades\Path;
use Statamic\Fields\Fieldtype;

class Icon extends Fieldtype
{
    protected $categories = ['media'];
    protected $icon = 'icon_picker';

    protected static $customSvgIcons = [];

    public function preload(): array
    {
        [$path, $directory] = $this->resolveParts();

        return [
            'url' => cp_route('icon-fieldtype'),
            'directory' => $directory,
        ];
    }

    public function icons()
    {
        [$path, $directory, $hasConfiguredDirectory] = $this->resolveParts();

        return collect(Folder::getFilesByType($path, 'svg'))->mapWithKeys(fn ($path) => [
            pathinfo($path)['filename'] => $hasConfiguredDirectory ? File::get($path) : null,
        ])->all();
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
                        'placeholder' => 'vendor/statamic/cms/resources/svg/icons',
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
        [$path] = $this->resolveParts();

        return File::get($path.'/'.$value.'.svg');
    }

    private function resolveParts()
    {
        $customSet = false;

        if ($set = $this->config('set')) {
            $directory = Icons::get($set)->directory();
            $customSet = true;
        } else {
            $directory = statamic_path('resources/svg/icons');
        }

        $path = Path::tidy($directory);

        return [
            $path,
            $directory,
            $customSet,
        ];
    }
}
