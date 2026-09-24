<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Charts\VerticalBar;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fields\FormValueType;
use Statamic\Forms\Fieldtypes\Currency;
use Statamic\Forms\Insights\Average;
use Statamic\Forms\Insights\MinMax;
use Statamic\Forms\Summary\FieldResponses;
use Tests\TestCase;

class CurrencyTest extends TestCase
{
    #[Test]
    public function it_returns_field_array_with_currency_symbol()
    {
        $fieldtype = (new Currency)->setField(new FormField('price', [
            'type' => 'currency',
            'currency' => 'USD',
        ]));

        $this->assertEquals([
            'type' => 'integer',
            'prepend' => '$',
            'currency_symbol' => '$',
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_passes_through_extra_config()
    {
        $fieldtype = (new Currency)->setField(new FormField('price', [
            'type' => 'currency',
            'currency' => 'EUR',
            'default' => 100,
        ]));

        $this->assertEquals([
            'type' => 'integer',
            'prepend' => '€',
            'currency_symbol' => '€',
            'default' => 100,
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_defaults_to_a_column_chart()
    {
        $this->assertEquals(VerticalBar::class, (new Currency)->defaultChart());
    }

    #[Test]
    public function it_stores_number_values()
    {
        $this->assertSame(FormValueType::Number, (new Currency)->valueType());
    }

    #[Test]
    public function it_defaults_to_min_max_and_average_insights()
    {
        $this->assertSame([MinMax::class, Average::class], (new Currency)->defaultInsights());
    }

    #[Test]
    public function it_formats_insights_for_the_currency()
    {
        $fieldtype = (new Currency)->setField(new FormField('price', [
            'type' => 'currency',
            'currency' => 'GBP',
        ]));

        $config = $fieldtype->insightConfig();

        $this->assertSame(['prefix' => '£', 'decimals' => 2], $config);
        $this->assertEquals(['min' => '5.00', 'max' => '15.00', 'prefix' => '£'], (new MinMax)->setConfig($config)->props($this->responses([5, 15])));
        $this->assertEquals(['average' => '10.00', 'prefix' => '£'], (new Average)->setConfig($config)->props($this->responses([5, 15])));
    }

    #[Test]
    public function it_has_no_currency_insight_config_for_an_unknown_currency()
    {
        $fieldtype = (new Currency)->setField(new FormField('price', [
            'type' => 'currency',
            'currency' => 'NOPE',
        ]));

        $this->assertSame(['prefix' => null, 'decimals' => 2], $fieldtype->insightConfig());
    }

    private function responses(iterable $values): FieldResponses
    {
        return FieldResponses::fromValues(new FormField('field', ['type' => 'currency']), $values);
    }
}
