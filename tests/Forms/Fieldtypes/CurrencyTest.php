<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Charts\VerticalBar;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fieldtypes\Currency;
use Statamic\Forms\Insights\Average;
use Statamic\Forms\Insights\MinMax;
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
    public function it_returns_insights_formatted_for_the_currency()
    {
        $fieldtype = (new Currency)->setField(new FormField('price', [
            'type' => 'currency',
            'currency' => 'GBP',
        ]));

        $insights = $fieldtype->insights();

        $this->assertCount(2, $insights);
        $this->assertInstanceOf(MinMax::class, $insights[0]);
        $this->assertInstanceOf(Average::class, $insights[1]);
        $this->assertEquals(['min' => '5.00', 'max' => '15.00', 'prefix' => '£'], $insights[0]->props(collect([5, 15])));
        $this->assertEquals(['average' => '10.00', 'prefix' => '£'], $insights[1]->props(collect([5, 15])));
    }
}
