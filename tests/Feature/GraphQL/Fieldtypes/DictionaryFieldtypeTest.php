<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('graphql')]
class DictionaryFieldtypeTest extends FieldtypeTestCase
{
    #[Test]
    public function it_gets_dictionary()
    {
        // UTC offsets can change during daylight saving time, so we need to freeze time.
        Carbon::setTestNow('2024-07-23');

        $this->createEntryWithFields([
            'undefined' => [
                'value' => null,
                'field' => ['type' => 'dictionary', 'dictionary' => ['type' => 'countries']],
            ],
            'country' => [
                'value' => 'USA',
                'field' => ['type' => 'dictionary', 'dictionary' => ['type' => 'countries'], 'max_items' => 1],
            ],
            'countries' => [
                'value' => ['AUS', 'USA'],
                'field' => ['type' => 'dictionary', 'dictionary' => ['type' => 'countries']],
            ],
            'timezone' => [
                'value' => 'America/New_York',
                'field' => ['type' => 'dictionary', 'dictionary' => ['type' => 'timezones'], 'max_items' => 1],
            ],
            'timezones' => [
                'value' => ['Australia/Sydney', 'America/New_York'],
                'field' => ['type' => 'dictionary', 'dictionary' => ['type' => 'timezones']],
            ],
            'currency' => [
                'value' => 'USD',
                'field' => ['type' => 'dictionary', 'dictionary' => ['type' => 'currencies'], 'max_items' => 1],
            ],
            'currencies' => [
                'value' => ['GBP', 'USD'],
                'field' => ['type' => 'dictionary', 'dictionary' => ['type' => 'currencies']],
            ],
        ]);

        $this->assertGqlEntryHas('
            undefined { name, iso2 }
            country { name, iso2 }
            countries { name, iso2 }
            timezone { name, offset }
            timezones { name, offset }
            currency { name, code, symbol }
            currencies { name, code, symbol }
        ', [
            'undefined' => null,
            'country' => ['name' => 'United States', 'iso2' => 'US'],
            'countries' => [['name' => 'Australia', 'iso2' => 'AU'], ['name' => 'United States', 'iso2' => 'US']],
            'timezone' => ['name' => 'America/New_York', 'offset' => '-04:00'],
            'timezones' => [['name' => 'Australia/Sydney', 'offset' => '+10:00'], ['name' => 'America/New_York', 'offset' => '-04:00']],
            'currency' => ['name' => 'US Dollar', 'code' => 'USD', 'symbol' => '$'],
            'currencies' => [['name' => 'British Pound Sterling', 'code' => 'GBP', 'symbol' => '£'], ['name' => 'US Dollar', 'code' => 'USD', 'symbol' => '$']],
        ]);
    }

    #[Test]
    public function it_filters_out_invalid_values()
    {
        $this->createEntryWithFields([
            'timezone' => [
                'value' => 'Somewhere/Nowhere',
                'field' => ['type' => 'dictionary', 'dictionary' => ['type' => 'timezones'], 'max_items' => 1],
            ],
            'timezones' => [
                'value' => ['Somewhere/Nowhere', 'America/New_York'],
                'field' => ['type' => 'dictionary', 'dictionary' => ['type' => 'timezones']],
            ],
        ]);

        $this->assertGqlEntryHas('
            timezone { name }
            timezones { name }
        ', [
            'timezone' => null,
            'timezones' => [['name' => 'America/New_York']],
        ]);
    }
}
