<?php

namespace Statamic\Forms\Fieldtypes;

use Illuminate\Support\Collection;
use Statamic\Forms\Charts\ChartOption;
use Statamic\Forms\Charts\HorizontalBar;
use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Forms\Insights\Checked;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class Toggle extends FormFieldtype
{
    protected static $fieldtype = 'toggle';
    protected $description = 'A simple yes or no switch.';
    protected $icon = 'fieldtype-toggle';
    protected $categories = ['choice'];
    protected $order = 5;

    public function configFieldItems(): array
    {
        return [
            'inline_label' => [
                'display' => __('Inline Label'),
                'instructions' => __('statamic::fieldtypes.toggle.config.inline_label'),
                'type' => 'text',
                'default' => '',
            ],
        ];
    }

    public function toFieldArray(): array
    {
        return [
            'type' => 'toggle',
            'inline_label' => $this->config('inline_label'),
            ...Arr::except($this->config(), ['type', 'inline_label']),
        ];
    }

    public function defaultChart(): ?string
    {
        return HorizontalBar::class;
    }

    public function chartOptions(Collection $values): ?Collection
    {
        return collect([
            new ChartOption('true', __('Yes'), icon: 'checkmark-circle-filled'),
            new ChartOption('false', __('No'), icon: 'delete-circle-filled'),
        ]);
    }

    public function insights(): array
    {
        return [new Checked];
    }

    public function example(): ?array
    {
        return [
            'config' => [
                'display' => 'Agreement',
                'inline_label' => 'I promise not to spoil the ending',
            ],
            'value' => true,
        ];
    }
}
