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

        $this->assertGreaterThan(150, count($options));
        $option = $options['USD'];
        $this->assertEquals('US Dollar (USD)', $option);
    }

    #[Test]
    #[DataProvider('searchProvider')]
    public function it_searches_options($query, $expectedCodes)
    {
        $dictionary = new Currencies;
        $allOptions = $dictionary->options();
        $results = $dictionary->options($query);

        $this->assertLessThan(count($allOptions), count($results));
        $this->assertNotEmpty(array_diff_key($allOptions, $results));
        $this->assertEmpty(array_diff_key($results, $allOptions));

        foreach ($expectedCodes as $code) {
            $this->assertArrayHasKey($code, $results);
            $this->assertTrue(str_ends_with($results[$code], "({$code})"));
        }
    }

    public static function searchProvider()
    {
        return [
            'dollar' => ['dollar', ['USD', 'CAD', 'AUD']],
            'dollar symbol' => ['$', ['USD', 'CAD', 'AUD']],
            'euro' => ['euro', ['EUR']],
            'euro symbol' => ['€', ['EUR']],
            'yen' => ['yen', ['JPY']],
            'pound symbol' => ['£', ['GBP']],
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

    #[Test]
    public function it_localizes_labels_using_app_locale()
    {
        app()->setLocale('de');

        $usd = (new Currencies)->get('USD')->data();

        $this->assertEquals('US-Dollar', $usd['name']);
    }
}
