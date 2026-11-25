<?php

namespace Statamic\Forms\Fieldtypes;

use Statamic\Facades\Dictionary;
use Statamic\Forms\Charts\VerticalBar;
use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Forms\Fields\FormValueType;
use Statamic\Forms\Insights\Average;
use Statamic\Forms\Insights\Insight;
use Statamic\Forms\Insights\MinMax;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class Currency extends FormFieldtype
{
    protected static $fieldtype = 'float';
    protected $description = 'Collects a monetary amount.';
    protected $icon = 'currency';
    protected $categories = ['number'];
    protected $keywords = ['money', 'number'];
    protected $order = 2;

    public function configFieldItems(): array
    {
        return [
            'currency' => [
                'display' => __('Currency'),
                'type' => 'dictionary',
                'dictionary' => 'currencies',
                'validate' => 'required',
                'max_items' => 1,
            ],
        ];
    }

    public function toFieldArray(): array
    {
        $extra = $this->currencyExtra();

        return [
            'type' => 'float',
            'prepend' => $symbol = Arr::get($extra, 'symbol'),
            'currency_symbol' => $symbol,
            'step' => 1 / (10 ** Arr::get($extra, 'decimals', 2)),
            ...Arr::except($this->config(), ['type', 'currency']),
        ];
    }

    private function currencyExtra(): array
    {
        if (! $currency = $this->config('currency')) {
            return [];
        }

        return Dictionary::find('currencies')?->get($currency)?->extra() ?? [];
    }

    public function valueType(): ?FormValueType
    {
        return FormValueType::Number;
    }

    public function defaultChart(): ?string
    {
        return VerticalBar::class;
    }

    public function defaultInsights(): array
    {
        return [MinMax::class, Average::class];
    }

    public function insightConfig(Insight $insight): array
    {
        $extra = $this->currencyExtra();

        return [
            'prefix' => Arr::get($extra, 'symbol'),
            'precision' => Arr::get($extra, 'decimals', 2),
        ];
    }

    public function example(): ?array
    {
        return [
            'config' => [
                'display' => 'How much do you usually spend on coffee per week?',
                'currency' => 'USD',
            ],
            'value' => 30,
        ];
    }
}
