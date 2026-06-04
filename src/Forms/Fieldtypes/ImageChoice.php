<?php

namespace Statamic\Forms\Fieldtypes;

use Statamic\Facades\Asset;
use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class ImageChoice extends FormFieldtype
{
    protected static $fieldtype = 'image_choice';
    protected $description = 'An image-based choice selector for visual options.';
    protected $icon = 'image-select';
    protected $categories = ['choice'];
    protected $order = 5;

    public function configFieldItems(): array
    {
        return [
            'options' => [
                'display' => __('Options'),
                'instructions' => __('Add choices with a unique key, optional label, and image.'),
                'type' => 'grid',
                'mode' => 'stacked',
                'add_row' => __('Add Option'),
                'fields' => [
                    [
                        'handle' => 'key',
                        'field' => [
                            'type' => 'text',
                            'display' => __('Key'),
                            'validate' => 'required',
                            'width' => 33,
                        ],
                    ],
                    [
                        'handle' => 'label',
                        'field' => [
                            'type' => 'text',
                            'display' => __('Label'),
                            'width' => 33,
                        ],
                    ],
                    [
                        'handle' => 'image',
                        'field' => [
                            'type' => 'assets',
                            'display' => __('Image'),
                            'max_files' => 1,
                            'mode' => 'grid',
                            'width' => 33,
                        ],
                    ],
                ],
            ],
            'multiple' => [
                'display' => __('Allow Multiple'),
                'instructions' => __('Let respondents select more than one option.'),
                'type' => 'toggle',
                'default' => false,
                'width' => 50,
            ],
            'columns' => [
                'display' => __('Columns'),
                'instructions' => __('How many options appear per row.'),
                'type' => 'integer',
                'default' => 3,
                'min' => 1,
                'max' => 4,
                'width' => 50,
            ],
        ];
    }

    public function preload(): array
    {
        return [
            'options' => $this->normalizedOptions(),
        ];
    }

    public function toFieldArray(): array
    {
        $configured = $this->config('columns');
        $columns = ($configured === null || $configured === '') ? 3 : (int) $configured;
        $columns = max(1, min(4, $columns));

        return [
            'type' => 'image_choice',
            'image_options' => $this->normalizedOptions(),
            'multiple' => (bool) $this->config('multiple'),
            'columns' => $columns,
            ...Arr::except($this->config(), ['type', 'options', 'multiple', 'columns']),
        ];
    }

    public function example(): ?array
    {
        return [
            'config' => [
                'display' => __('Pick your favorite season'),
                'columns' => 3,
                'options' => [
                    [
                        'key' => 'spring',
                        'label' => __('Spring'),
                        'image' => 'https://picsum.photos/seed/spring/320/240',
                    ],
                    [
                        'key' => 'summer',
                        'label' => __('Summer'),
                        'image' => 'https://picsum.photos/seed/summer/320/240',
                    ],
                    [
                        'key' => 'autumn',
                        'label' => __('Autumn'),
                        'image' => 'https://picsum.photos/seed/autumn/320/240',
                    ],
                    [
                        'key' => 'winter',
                        'label' => __('Winter'),
                        'image' => 'https://picsum.photos/seed/winter/320/240',
                    ],
                ],
            ],
            'value' => null,
        ];
    }

    private function normalizedOptions(): array
    {
        return collect($this->config('options'))
            ->filter(fn ($option) => is_array($option))
            ->reject(fn (array $option): bool => ($option['hidden'] ?? false) === true)
            ->map(function (array $option): ?array {
                $key = $option['key'] ?? null;

                if (blank($key)) {
                    return null;
                }

                return [
                    'key' => $key,
                    'label' => $option['label'] ?? $key,
                    'image' => $this->resolveImageUrl($option['image'] ?? null),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function resolveImageUrl(mixed $image): ?string
    {
        if (blank($image)) {
            return null;
        }

        if (is_array($image)) {
            $image = Arr::first($image);
        }

        if (! is_string($image)) {
            return null;
        }

        if ($asset = Asset::find($image) ?? Asset::findByUrl($image)) {
            return $asset->url();
        }

        if (filter_var($image, FILTER_VALIDATE_URL) || str_starts_with($image, '/')) {
            return $image;
        }

        if ($asset = Asset::findByPath($image)) {
            return $asset->url();
        }

        return null;
    }
}
