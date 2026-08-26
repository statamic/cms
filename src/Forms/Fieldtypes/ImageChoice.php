<?php

namespace Statamic\Forms\Fieldtypes;

use Illuminate\Support\Collection;
use Statamic\Forms\Charts\ChartOption;
use Statamic\Forms\Charts\HorizontalBar;
use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class ImageChoice extends FormFieldtype
{
    protected static $fieldtype = 'image_choice';
    protected $description = 'An image-based choice selector for visual options.';
    protected $icon = 'image-select';
    protected $categories = ['choice', 'media'];
    protected $order = 5;

    public function configFieldItems(): array
    {
        return [
            'options' => [
                'display' => __('Options'),
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
                            'validate' => 'required',
                            'container' => config('statamic.assets.image_choice_images.container'),
                            'folder' => config('statamic.assets.image_choice_images.folder'),
                        ],
                    ],
                ],
            ],
            'multiple' => [
                'display' => __('Allow Multiple'),
                'instructions' => __('Allow multiple selections.'),
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
            'aspect_ratio' => [
                'display' => __('Aspect Ratio'),
                'instructions' => __('Shape of the image area for each option.'),
                'type' => 'select',
                'default' => '16/9',
                'options' => [
                    '1/1' => __('Square'),
                    '4/3' => __('4:3'),
                    '3/2' => __('3:2'),
                    '16/9' => __('16:9'),
                    '3/4' => __('3:4'),
                    '2/3' => __('2:3'),
                ],
                'width' => 50,
            ],
        ];
    }

    public function toFieldArray(): array
    {
        return [
            'type' => 'image_choice',
            'multiple' => (bool) $this->config('multiple'),
            'columns' => max(1, min(4, (int) ($this->config('columns') ?? 3))),
            'aspect_ratio' => $this->normalizedAspectRatio(),
            'gap' => $this->normalizedGap(),
            ...Arr::except($this->config(), ['type', 'multiple', 'columns', 'aspect_ratio', 'gap']),
        ];
    }

    public function defaultChart(): ?string
    {
        return HorizontalBar::class;
    }

    public function chartOptions(Collection $values): ?Collection
    {
        return collect($this->toField()->fieldtype()->preload()['options'])
            ->map(fn ($option) => new ChartOption($option['key'], $option['label'], image: $option['image'], badge: $option['letter']));
    }

    public function example(): ?array
    {
        return [
            'config' => [
                'display' => __('Pick your favorite season'),
                'columns' => 2,
                'gap' => 2,
                'aspect_ratio' => '4/3',
                'options' => [
                    [
                        'key' => 'spring',
                        'label' => __('Spring'),
                        'image' => 'https://picsum.photos/seed/image-choice-spring/640/480',
                    ],
                    [
                        'key' => 'summer',
                        'label' => __('Summer'),
                        'image' => 'https://picsum.photos/seed/image-choice-summer/640/480',
                    ],
                    [
                        'key' => 'autumn',
                        'label' => __('Autumn'),
                        'image' => 'https://picsum.photos/seed/image-choice-autumn/640/480',
                    ],
                    [
                        'key' => 'winter',
                        'label' => __('Winter'),
                        'image' => 'https://picsum.photos/seed/image-choice-winter/640/480',
                    ],
                ],
            ],
        ];
    }

    private function normalizedGap(): int
    {
        $gap = (int) ($this->config('gap') ?? 3);

        return in_array($gap, [2, 3, 4], true) ? $gap : 3;
    }

    private function normalizedAspectRatio(): string
    {
        $allowed = ['1/1', '4/3', '3/2', '16/9', '3/4', '2/3'];
        $ratio = $this->config('aspect_ratio') ?? '16/9';

        return in_array($ratio, $allowed, true) ? $ratio : '16/9';
    }
}
