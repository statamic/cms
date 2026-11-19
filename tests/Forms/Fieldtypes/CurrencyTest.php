<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\DataProvider;
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
            'type' => 'float',
            'prepend' => '$',
            'currency_symbol' => '$',
            'step' => 0.01,
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
            'type' => 'float',
            'prepend' => '€',
            'currency_symbol' => '€',
            'step' => 0.01,
            'default' => 100,
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    #[DataProvider('stepProvider')]
    public function it_sets_the_step_from_the_currency_decimals($currency, $step)
    {
        $fieldtype = (new Currency)->setField(new FormField('price', [
            'type' => 'currency',
            'currency' => $currency,
        ]));

        $this->assertSame($step, $fieldtype->toFieldArray()['step']);
    }

    public static function stepProvider()
    {
        return [
            'two decimals' => ['USD', 0.01],
            'no decimals' => ['JPY', 1],
            'three decimals' => ['BHD', 0.001],
        ];
    }

    #[Test]
    public function a_configured_step_takes_precedence()
    {
        $fieldtype = (new Currency)->setField(new FormField('price', [
            'type' => 'currency',
            'currency' => 'USD',
            'step' => 5,
        ]));

        $this->assertSame(5, $fieldtype->toFieldArray()['step']);
    }

    #[Test]
    #[DataProvider('missingCurrencyProvider')]
    public function it_handles_a_missing_or_unknown_currency($currency)
    {
        $fieldtype = (new Currency)->setField(new FormField('price', [
            'type' => 'currency',
            'currency' => $currency,
        ]));

        $this->assertEquals([
            'type' => 'float',
            'prepend' => null,
            'currency_symbol' => null,
            'step' => 0.01,
        ], $fieldtype->toFieldArray());
    }

    public static function missingCurrencyProvider()
    {
        return [
            'null' => [null],
            'unknown' => ['NOPE'],
        ];
    }

    #[Test]
    public function it_accepts_and_stores_decimal_amounts()
    {
        $field = (new Currency)->setField(new FormField('price', [
            'type' => 'currency',
            'currency' => 'USD',
        ]))->toField();

        $this->assertSame(12.5, $field->setValue('12.50')->process()->value());
        $this->assertContains('numeric', $field->rules()[$field->handle()]);
        $this->assertNotContains('integer', $field->rules()[$field->handle()]);
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

        $minMax = new MinMax;
        $average = new Average;

        $this->assertSame(['prefix' => '£', 'precision' => 2], $fieldtype->insightConfig($minMax));
        $this->assertEquals(['min' => '5.00', 'max' => '15.00', 'prefix' => '£'], $minMax->setConfig($fieldtype->insightConfig($minMax))->props($this->responses([5, 15])));
        $this->assertEquals(['average' => '10.00', 'prefix' => '£'], $average->setConfig($fieldtype->insightConfig($average))->props($this->responses([5, 15])));
    }

    #[Test]
    public function it_has_no_currency_insight_config_for_an_unknown_currency()
    {
        $fieldtype = (new Currency)->setField(new FormField('price', [
            'type' => 'currency',
            'currency' => 'NOPE',
        ]));

        $this->assertSame(['prefix' => null, 'precision' => 2], $fieldtype->insightConfig(new MinMax));
    }

    private function responses(iterable $values): FieldResponses
    {
        return FieldResponses::fromValues(new FormField('field', ['type' => 'currency']), $values);
    }
}
