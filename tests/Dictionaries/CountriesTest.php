<?php

namespace Tests\Dictionaries;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Dictionaries\Countries;
use Statamic\Dictionaries\Item;
use Tests\TestCase;

class CountriesTest extends TestCase
{
    #[Test]
    public function it_gets_options()
    {
        $options = (new Countries)->options();

        $this->assertCount(250, $options);
        $this->assertEquals([
            'AFG' => '🇦🇫 Afghanistan',
            'ALA' => '🇦🇽 Aland Islands',
            'ALB' => '🇦🇱 Albania',
            'DZA' => '🇩🇿 Algeria',
            'ASM' => '🇦🇸 American Samoa',
        ], array_slice($options, 0, 5));
    }

    #[Test]
    public function it_filters_options_by_region()
    {
        $options = (new Countries)->setConfig(['region' => 'oceania'])->options();

        $this->assertCount(27, $options);
        $this->assertEquals([
            'ASM' => '🇦🇸 American Samoa',
            'AUS' => '🇦🇺 Australia',
            'CXR' => '🇨🇽 Christmas Island',
            'CCK' => '🇨🇨 Cocos (Keeling) Islands',
            'COK' => '🇨🇰 Cook Islands',
        ], array_slice($options, 0, 5));
    }

    #[Test]
    #[DataProvider('searchProvider')]
    public function it_searches_options($query, $expected)
    {
        $this->assertEquals($expected, (new Countries)->options($query));
    }

    public static function searchProvider()
    {
        return [
            'au' => [
                'au',
                [
                    'AUS' => '🇦🇺 Australia',
                    'AUT' => '🇦🇹 Austria',
                    'GNB' => '🇬🇼 Guinea-Bissau',
                    'MAC' => '🇲🇴 Macau S.A.R.',
                    'MRT' => '🇲🇷 Mauritania',
                    'MUS' => '🇲🇺 Mauritius',
                    'NRU' => '🇳🇷 Nauru',
                    'PLW' => '🇵🇼 Palau',
                    'SAU' => '🇸🇦 Saudi Arabia',
                    'TKL' => '🇹🇰 Tokelau',
                ],
            ],
            'us' => [
                'us',
                [
                    'AUS' => '🇦🇺 Australia',
                    'AUT' => '🇦🇹 Austria',
                    'BLR' => '🇧🇾 Belarus',
                    'BES' => '🇧🇶 Bonaire, Sint Eustatius and Saba',
                    'CYP' => '🇨🇾 Cyprus',
                    'MUS' => '🇲🇺 Mauritius',
                    'RUS' => '🇷🇺 Russia',
                    'USA' => '🇺🇸 United States',
                    'VIR' => '🇻🇮 Virgin Islands (US)',
                ],
            ],
        ];
    }

    #[Test]
    public function it_gets_array_from_value()
    {
        $item = (new Countries)->get('AUS');
        $this->assertInstanceOf(Item::class, $item);
        $this->assertEquals([
            'name' => 'Australia',
            'iso3' => 'AUS',
            'iso2' => 'AU',
            'region' => 'Oceania',
            'subregion' => 'Australia and New Zealand',
            'emoji' => '🇦🇺',
        ], $item->data());
    }
}
