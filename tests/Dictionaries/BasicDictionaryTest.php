<?php

namespace Tests\Dictionaries;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Dictionaries\BasicDictionary;
use Tests\TestCase;

class BasicDictionaryTest extends TestCase
{
    #[Test]
    public function search_does_not_match_against_icon_values()
    {
        $dictionary = new IconSearchDictionary;

        $this->assertCount(0, $dictionary->optionItems('svg'));
        $this->assertCount(0, $dictionary->optionItems('map-pin'));
        $this->assertCount(1, $dictionary->optionItems('Alabama'));
    }

    #[Test]
    public function option_items_expose_the_icon()
    {
        $dictionary = new IconSearchDictionary;

        $items = collect($dictionary->optionItems());

        $this->assertEquals('map-pin', $items->get('AL')->icon());
        $this->assertEquals('<svg>map-pin</svg>', $items->get('AK')->icon());
        $this->assertNull($items->get('AZ')->icon());
    }
}

class IconSearchDictionary extends BasicDictionary
{
    protected string $valueKey = 'abbr';
    protected string $labelKey = 'name';

    protected function getItems(): array
    {
        return [
            ['name' => 'Alabama', 'abbr' => 'AL', 'icon' => 'map-pin'],
            ['name' => 'Alaska', 'abbr' => 'AK', 'icon' => '<svg>map-pin</svg>'],
            ['name' => 'Arizona', 'abbr' => 'AZ'],
        ];
    }
}
