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

        $this->assertGreaterThan(240, count($options));
        $this->assertEquals([
            'AFG' => '🇦🇫 Afghanistan',
            'ALA' => '🇦🇽 Åland Islands',
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
    public function it_searches_options($query, $expectedCodes)
    {
        $dictionary = new Countries;
        $allOptions = $dictionary->options();
        $results = $dictionary->options($query);

        $this->assertLessThan(count($allOptions), count($results));
        $this->assertNotEmpty(array_diff_key($allOptions, $results));
        $this->assertEmpty(array_diff_key($results, $allOptions));

        foreach ($expectedCodes as $code) {
            $this->assertArrayHasKey($code, $results);
        }
    }

    public static function searchProvider()
    {
        return [
            'au' => ['au', ['AUS', 'AUT']],
            'united' => ['united', ['USA', 'GBR', 'ARE']],
            'island' => ['island', ['MHL', 'SLB', 'CYM']],
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

    #[Test]
    public function it_localizes_names_using_app_locale()
    {
        app()->setLocale('de');

        $item = (new Countries)->get('DEU')->data();

        $this->assertEquals('Deutschland', $item['name']);
    }
}
