<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\File;
use Statamic\Facades\Folder;
use Statamic\Facades\Path;
use Statamic\Fields\Fieldtype;

class Icon extends Fieldtype
{
    protected $categories = ['media'];
    protected $icon = 'icon_picker';

    public function preload(): array
    {
        [$path, $directory, $folder, $hasConfiguredDirectory] = $this->resolveParts();

        $icons = collect(Folder::getFilesByType($path, 'svg'))->mapWithKeys(fn ($path) => [
            pathinfo($path)['filename'] => $hasConfiguredDirectory ? File::get($path) : null,
        ]);

        return [
            'native' => ! $hasConfiguredDirectory,
            'set' => $folder,
            'icons' => $icons->all(),
        ];
    }

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Selection'),
                'fields' => [
                    'directory' => [
                        'display' => __('Directory'),
                        'instructions' => __('statamic::fieldtypes.icon.config.directory'),
                        'type' => 'text',
                    ],
                    'folder' => [
                        'display' => __('Folder'),
                        'instructions' => __('statamic::fieldtypes.icon.config.folder'),
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

    public function augment($value)
    {
        // If a directory has not been configured, it's a Statamic Control Panel icon.
        // We don't want to allow them on the front-end due to licensing restrictions.
        if (! $this->config('directory')) {
            return $value;
        }

        [$path] = $this->resolveParts();

        return File::get($path.'/'.$value.'.svg');
    }

    private function resolveParts()
    {
        $hasConfiguredDirectory = true;

        if (! $directory = $this->config('directory')) {
            $hasConfiguredDirectory = false;
            $directory = statamic_path('resources/svg/icons');
        }

        $folder = $this->config(
            'folder',
            $hasConfiguredDirectory ? null : 'default' // Only apply a default folder if using Statamic icons.
        );

        $path = Path::tidy($directory.'/'.$folder);

        return [
            $path,
            $directory,
            $folder,
            $hasConfiguredDirectory,
        ];
    }
}
