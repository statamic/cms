<?php

namespace Tests\Dictionaries;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Dictionaries\Currencies;
use Statamic\Dictionaries\Item;
use Tests\TestCase;

class CurrenciesTest extends TestCase
{
    #[Test]
    public function it_gets_options()
    {
        $options = (new Currencies)->options();

        $this->assertCount(154, $options);
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
                    'BBD' => 'Barbadian Dollar (BBD)',
                    'BMD' => 'Bermudian Dollar (BMD)',
                    'BSD' => 'Bahamian Dollar (BSD)',
                    'FJD' => 'Fijian Dollar (FJD)',
                    'GYD' => 'Guyanese Dollar (GYD)',
                    'KYD' => 'Cayman Islands Dollar (KYD)',
                    'LRD' => 'Liberian Dollar (LRD)',
                    'SBD' => 'Solomon Islands Dollar (SBD)',
                    'SRD' => 'Surinamese Dollar (SRD)',
                    'XCD' => 'Eastern Caribbean Dollar (XCD)',
                ],
            ],
            'dollar symbol' => [
                '$',
                [
                    'ARS' => 'Argentine Peso (ARS)',
                    'AUD' => 'Australian Dollar (AUD)',
                    'BND' => 'Brunei Dollar (BND)',
                    'BRL' => 'Brazilian Real (BRL)',
                    'BZD' => 'Belize Dollar (BZD)',
                    'CAD' => 'Canadian Dollar (CAD)',
                    'CLP' => 'Chilean Peso (CLP)',
                    'COP' => 'Colombian Peso (COP)',
                    'CVE' => 'Cape Verdean Escudo (CVE)',
                    'DOP' => 'Dominican Peso (DOP)',
                    'HKD' => 'Hong Kong Dollar (HKD)',
                    'JMD' => 'Jamaican Dollar (JMD)',
                    'MOP' => 'Macanese Pataca (MOP)',
                    'MXN' => 'Mexican Peso (MXN)',
                    'NAD' => 'Namibian Dollar (NAD)',
                    'NIO' => 'Nicaraguan Córdoba (NIO)',
                    'NZD' => 'New Zealand Dollar (NZD)',
                    'SGD' => 'Singapore Dollar (SGD)',
                    'TOP' => 'Tongan Paʻanga (TOP)',
                    'TTD' => 'Trinidad and Tobago Dollar (TTD)',
                    'TWD' => 'New Taiwan Dollar (TWD)',
                    'USD' => 'US Dollar (USD)',
                    'UYU' => 'Uruguayan Peso (UYU)',
                    'ZWG' => 'Zimbabwe Gold (ZWG)',
                    'BBD' => 'Barbadian Dollar (BBD)',
                    'BMD' => 'Bermudian Dollar (BMD)',
                    'BSD' => 'Bahamian Dollar (BSD)',
                    'CUP' => 'Cuban Peso (CUP)',
                    'FJD' => 'Fijian Dollar (FJD)',
                    'GYD' => 'Guyanese Dollar (GYD)',
                    'KYD' => 'Cayman Islands Dollar (KYD)',
                    'LRD' => 'Liberian Dollar (LRD)',
                    'SBD' => 'Solomon Islands Dollar (SBD)',
                    'SRD' => 'Surinamese Dollar (SRD)',
                    'WST' => 'Samoan Tala (WST)',
                    'XCD' => 'Eastern Caribbean Dollar (XCD)',
                ],
            ],
            'pound symbol' => [
                '£',
                [
                    'GBP' => 'British Pound Sterling (GBP)',
                    'FKP' => 'Falkland Islands Pound (FKP)',
                    'GIP' => 'Gibraltar Pound (GIP)',
                    'SHP' => 'Saint Helena Pound (SHP)',
                    'SSP' => 'South Sudanese Pound (SSP)',
                ],
            ],
        ];
    }

    #[Test]
    public function it_gets_array_from_value()
    {
        $item = (new Currencies)->get('USD');
        $this->assertInstanceOf(Item::class, $item);
        $this->assertEquals([
            'name' => 'US Dollar',
            'code' => 'USD',
            'symbol' => '$',
            'decimals' => 2,
        ], $item->data());
    }
}
