<?php

namespace Statamic\Fieldtypes;

use Illuminate\Filesystem\Filesystem;
use Statamic\Facades\File;
use Statamic\Facades\Folder;
use Statamic\Facades\Path;
use Statamic\Fields\Fieldtype;
use Statamic\Support\Str;

class Icon extends Fieldtype
{
    public const DEFAULT_FOLDER = 'regular';

    protected $categories = ['media'];
    protected $icon = 'icon_picker';

    protected static $customSvgIcons = [];

    public function preload(): array
    {
        [$path, $directory, $folder, $hasConfiguredDirectory] = $this->resolveParts();

        return [
            'url' => cp_route('icon-fieldtype'),
            'native' => ! $hasConfiguredDirectory,
            'directory' => $directory,
            'set' => $folder,
        ];
    }

    public function icons()
    {
        [$path, $directory, $folder, $hasConfiguredDirectory] = $this->resolveParts();

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
                    'directory' => [
                        'display' => __('Directory'),
                        'instructions' => __('statamic::fieldtypes.icon.config.directory'),
                        'type' => 'text',
                        'placeholder' => 'vendor/statamic/cms/resources/svg/icons',
                    ],
                    'folder' => [
                        'display' => __('Folder'),
                        'instructions' => __('statamic::fieldtypes.icon.config.folder'),
                        'type' => 'text',
                        'placeholder' => static::DEFAULT_FOLDER,
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
            $hasConfiguredDirectory ? null : self::DEFAULT_FOLDER // Only apply a default folder if using Statamic icons.
        );

        $path = Path::tidy($directory.'/'.$folder);

        return [
            $path,
            $directory,
            $folder,
            $hasConfiguredDirectory,
        ];
    }

    /**
     * Provide custom SVG icons to script.
     *
     * @param  string  $directory
     * @param  string|null  $folder
     */
    public static function provideCustomSvgIconsToScript($directory, $folder = null)
    {
        $path = Str::removeRight(Path::tidy($directory.'/'.$folder), '/');

        static::$customSvgIcons[$path] = collect(app(Filesystem::class)->files($path))
            ->filter(fn ($file) => strtolower($file->getExtension()) === 'svg')
            ->keyBy(fn ($file) => pathinfo($file->getBasename(), PATHINFO_FILENAME))
            ->map
            ->getContents()
            ->all();
    }

    /**
     * Get custom SVG icons for script.
     *
     * @return array
     */
    public static function getCustomSvgIcons()
    {
        return static::$customSvgIcons;
    }
}
