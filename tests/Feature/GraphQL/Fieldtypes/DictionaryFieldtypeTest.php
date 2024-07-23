<?php

namespace Feature\GraphQL\Fieldtypes;

use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\GraphQL\Fieldtypes\FieldtypeTestCase;

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
                'field' => ['type' => 'dictionary', 'dictionary' => ['type' => 'countries']],
            ],
            'countries' => [
                'value' => ['AUS', 'USA'],
                'field' => ['type' => 'dictionary', 'dictionary' => ['type' => 'countries'], 'multiple' => true],
            ],
            'timezone' => [
                'value' => 'America/New_York',
                'field' => ['type' => 'dictionary', 'dictionary' => ['type' => 'timezones']],
            ],
            'timezones' => [
                'value' => ['Australia/Sydney', 'America/New_York'],
                'field' => ['type' => 'dictionary', 'dictionary' => ['type' => 'timezones'], 'multiple' => true],
            ],
            'currency' => [
                'value' => 'USD',
                'field' => ['type' => 'dictionary', 'dictionary' => ['type' => 'currencies']],
            ],
            'currencies' => [
                'value' => ['GBP', 'USD'],
                'field' => ['type' => 'dictionary', 'dictionary' => ['type' => 'currencies'], 'multiple' => true],
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
}
