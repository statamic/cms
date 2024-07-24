<?php

namespace Tests\Dictionaries;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Dictionaries\Currencies;
use Tests\TestCase;

class CurrenciesTest extends TestCase
{
    #[Test]
    public function it_gets_options()
    {
        $options = (new Currencies)->options();

        $this->assertCount(119, $options);
        $option = $options['USD'];
        $this->assertEquals('US Dollar (USD)', $option);
    }

    #[Test]
    #[DataProvider('searchProvider')]
    public function it_searches_options($query, $expected)
    {
        $this->assertEquals($expected, (new Currencies)->options($query));
    }

    public static function searchProvider()
    {
        return [
            'euro' => [
                'euro',
                [
                    'EUR' => 'Euro (EUR)',
                ],
            ],
            'dollar' => [
                'dollar',
                [
                    'AUD' => 'Australian Dollar (AUD)',
                    'BZD' => 'Belize Dollar (BZD)',
                    'CAD' => 'Canadian Dollar (CAD)',
                    'HKD' => 'Hong Kong Dollar (HKD)',
                    'JMD' => 'Jamaican Dollar (JMD)',
                    'NAD' => 'Namibian Dollar (NAD)',
                    'NZD' => 'New Zealand Dollar (NZD)',
                    'SGD' => 'Singapore Dollar (SGD)',
                    'TTD' => 'Trinidad and Tobago Dollar (TTD)',
                    'USD' => 'US Dollar (USD)',
                    'BND' => 'Brunei Dollar (BND)',
                    'TWD' => 'New Taiwan Dollar (TWD)',
                    'ZWL' => 'Zimbabwean Dollar (ZWL)',
                ],
            ],
            'dollar symbol' => [
                '$',
                [
                    'ARS' => 'Argentine Peso (ARS)',
                    'AUD' => 'Australian Dollar (AUD)',
                    'BND' => 'Brunei Dollar (BND)',
                    'BZD' => 'Belize Dollar (BZD)',
                    'CAD' => 'Canadian Dollar (CAD)',
                    'CLP' => 'Chilean Peso (CLP)',
                    'COP' => 'Colombian Peso (COP)',
                    'HKD' => 'Hong Kong Dollar (HKD)',
                    'JMD' => 'Jamaican Dollar (JMD)',
                    'MXN' => 'Mexican Peso (MXN)',
                    'NZD' => 'New Zealand Dollar (NZD)',
                    'SGD' => 'Singapore Dollar (SGD)',
                    'TTD' => 'Trinidad and Tobago Dollar (TTD)',
                    'USD' => 'US Dollar (USD)',
                    'UYU' => 'Uruguayan Peso (UYU)',
                ],
            ],
            'pound symbol' => [
                '£',
                [
                    'GBP' => 'British Pound Sterling (GBP)',
                ],
            ],
        ];
    }

    #[Test]
    public function it_gets_array_from_value()
    {
        $this->assertEquals([
            'name' => 'US Dollar',
            'code' => 'USD',
            'symbol' => '$',
            'decimal_digits' => 2,
        ], (new Currencies)->get('USD'));
    }
}
