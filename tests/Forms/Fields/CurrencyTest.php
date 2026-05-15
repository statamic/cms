<?php

namespace Tests\Forms\Fields;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Fields\Currency;
use Statamic\Forms\Fields\FormField;
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
            'default' => 100,
        ], $fieldtype->toFieldArray());
    }
}
