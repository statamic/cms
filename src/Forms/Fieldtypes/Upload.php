<?php

namespace Statamic\Forms\Fieldtypes;

use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class Upload extends FormFieldtype
{
    protected static $fieldtype = 'files';
    protected $description = 'Upload files - store them permanently or use them temporarily.';
    protected $icon = 'upload-cloud';
    protected $categories = ['media'];
    protected $order = 6;

    public function configFieldItems(): array
    {
        return [
            'store' => [
                'display' => __('Store Files'),
                'instructions' => __('Store uploaded files permanently in an asset container.'),
                'type' => 'toggle',
                'default' => false,
            ],
            'container' => [
                'display' => __('Container'),
                'instructions' => __('Select which asset container to store the uploaded files.'),
                'type' => 'asset_container',
                'max_items' => 1,
                'mode' => 'select',
                'if' => ['store' => true],
            ],
            'folder' => [
                'display' => __('Folder'),
                'instructions' => __('The folder within the container to upload files to.'),
                'type' => 'asset_folder',
                'max_items' => 1,
                'if' => ['store' => true],
            ],
            'max_files' => [
                'display' => __('Max Files'),
                'instructions' => __('The maximum number of files that may be uploaded.'),
                'type' => 'integer',
                'min' => 1,
            ],
        ];
    }

    public function toFieldArray(): array
    {
        if ($this->config('store')) {
            return [
                'type' => 'assets',
                'container' => $this->config('container'),
                'folder' => $this->config('folder'),
                'max_files' => $this->config('max_files'),
                ...Arr::except($this->config(), ['type', 'store', 'container', 'folder', 'max_files']),
            ];
        }

        return [
            'type' => 'files',
            'max_files' => $this->config('max_files'),
            ...Arr::except($this->config(), ['type', 'store', 'max_files']),
        ];
    }

    public function example(): ?array
    {
        return [
            'config' => [
                'display' => __('Upload your resume'),
                'store' => false,
            ],
        ];
    }

    public function view(): string
    {
        $language = config('statamic.templates.language', 'antlers');

        // Check for user's custom assets or files views first (for backwards compatibility)
        $legacyType = $this->config('store') ? 'assets' : 'files';
        $legacyViews = [
            "statamic::forms.fields.{$legacyType}",
            "statamic::forms.{$language}.fields.{$legacyType}",
        ];

        if ($legacyView = collect($legacyViews)->first(fn (string $view): bool => view()->exists($view))) {
            return $legacyView;
        }

        return parent::view();
    }
}
