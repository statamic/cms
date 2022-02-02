<?php

namespace Tests\Modifiers;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Carbon;
use Statamic\Facades\Collection;
use Statamic\Fields\Value;
use Statamic\Modifiers\Modify;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class GroupByTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_groups_an_array()
    {
        $items = [
            ['sport' => 'basketball', 'team' => 'jazz'],
            ['sport' => 'baseball', 'team' => 'yankees'],
            ['sport' => 'basketball', 'team' => 'bulls'],
        ];

        $expected = collect([
            'basketball' => collect([
                ['sport' => 'basketball', 'team' => 'jazz'],
                ['sport' => 'basketball', 'team' => 'bulls'],
            ]),
            'baseball' => collect([
                ['sport' => 'baseball', 'team' => 'yankees'],
            ]),
            'groups' => collect([
                [
                    'key' => 'basketball',
                    'group' => 'basketball',
                    'items' => collect([
                        ['sport' => 'basketball', 'team' => 'jazz'],
                        ['sport' => 'basketball', 'team' => 'bulls'],
                    ]),
                ],
                [
                    'key' => 'baseball',
                    'group' => 'baseball',
                    'items' => collect([
                        ['sport' => 'baseball', 'team' => 'yankees'],
                    ]),
                ],
            ]),
        ]);

        $this->assertEquals($expected, $this->modify($items, 'sport'));
    }

    /** @test */
    public function it_groups_an_array_with_value_objects()
    {
        // eg. replicator sets

        $items = [
            ['sport' => new Value('basketball', 'sport'), 'team' => new Value('jazz', 'team')],
            ['sport' => new Value('baseball', 'sport'), 'team' => new Value('yankees', 'team')],
            ['sport' => new Value('basketball', 'sport'), 'team' => new Value('bulls', 'team')],
        ];

        $expected = collect([
            'basketball' => collect([
                ['sport' => 'basketball', 'team' => 'jazz'],
                ['sport' => 'basketball', 'team' => 'bulls'],
            ]),
            'baseball' => collect([
                ['sport' => 'baseball', 'team' => 'yankees'],
            ]),
            'groups' => collect([
                [
                    'key' => 'basketball',
                    'group' => 'basketball',
                    'items' => collect([
                        ['sport' => 'basketball', 'team' => 'jazz'],
                        ['sport' => 'basketball', 'team' => 'bulls'],
                    ]),
                ],
                [
                    'key' => 'baseball',
                    'group' => 'baseball',
                    'items' => collect([
                        ['sport' => 'baseball', 'team' => 'yankees'],
                    ]),
                ],
            ]),
        ]);

        $this->assertEquals($expected, $this->modify($items, 'sport'));
    }

    /** @test */
    public function it_can_get_keys_from_objects()
    {
        $items = collect([
            $jazz = EntryFactory::collection('sports')->data(['sport' => 'basketball', 'team' => 'jazz'])->create(),
            $yankees = EntryFactory::collection('sports')->data(['sport' => 'baseball', 'team' => 'yankees'])->create(),
            $bulls = EntryFactory::collection('sports')->data(['sport' => 'basketball', 'team' => 'bulls'])->create(),
        ]);

        $expected = collect([
            'basketball' => collect([$jazz, $bulls]),
            'baseball' => collect([$yankees]),
            'groups' => collect([
                ['key' => 'basketball', 'group' => 'basketball', 'items' => collect([$jazz, $bulls])],
                ['key' => 'baseball', 'group' => 'baseball', 'items' => collect([$yankees])],
            ]),
        ]);

        $this->assertEquals($expected, $this->modify($items, 'sport'));
    }

    /** @test */
    public function it_can_get_nested_keys_from_objects()
    {
        Collection::make('basketball')->title('Basketball')->save();
        Collection::make('baseball')->title('Baseball')->save();

        $items = collect([
            $jazz = EntryFactory::collection('basketball')->data(['team' => 'jazz'])->create(),
            $yankees = EntryFactory::collection('baseball')->data(['team' => 'yankees'])->create(),
            $bulls = EntryFactory::collection('basketball')->data(['team' => 'bulls'])->create(),
        ]);

        $expected = collect([
            'Basketball' => collect([$jazz, $bulls]),
            'Baseball' => collect([$yankees]),
            'groups' => collect([
                ['key' => 'Basketball', 'group' => 'Basketball', 'items' => collect([$jazz, $bulls])],
                ['key' => 'Baseball', 'group' => 'Baseball', 'items' => collect([$yankees])],
            ]),
        ]);

        $this->assertEquals($expected, $this->modify($items, 'collection:title'));
    }

    /** @test */
    public function if_the_grouped_keys_are_objects_itll_convert_them_to_strings()
    {
        $items = collect([
            $jazz = EntryFactory::collection('sports')->data(['sport' => new TestGroupByClass('basketball'), 'team' => 'jazz'])->create(),
            $yankees = EntryFactory::collection('sports')->data(['sport' => new TestGroupByClass('baseball'), 'team' => 'yankees'])->create(),
            $bulls = EntryFactory::collection('sports')->data(['sport' => new TestGroupByClass('basketball'), 'team' => 'bulls'])->create(),
        ]);

        $expected = collect([
            'basketball' => collect([$jazz, $bulls]),
            'baseball' => collect([$yankees]),
            'groups' => collect([
                ['key' => 'basketball', 'group' => 'basketball', 'items' => collect([$jazz, $bulls])],
                ['key' => 'baseball', 'group' => 'baseball', 'items' => collect([$yankees])],
            ]),
        ]);

        $this->assertEquals($expected, $this->modify($items, 'sport'));
    }

    /** @test */
    public function it_groups_by_date()
    {
        Carbon::setTestNow(now()->startOfDay());

        $items = [
            ['when' => now()->setHour(14), 'title' => '2pm'],
            ['when' => now()->setHour(3), 'title' => '3am'],
            ['when' => now()->setHour(10), 'title' => '10am'],
            ['when' => now()->setHour(23), 'title' => '11pm'],
        ];

        $expected = collect([
            'pm' => collect([
                ['when' => now()->setHour(14), 'title' => '2pm'],
                ['when' => now()->setHour(23), 'title' => '11pm'],
            ]),
            'am' => collect([
                ['when' => now()->setHour(3), 'title' => '3am'],
                ['when' => now()->setHour(10), 'title' => '10am'],
            ]),
            'groups' => collect([
                [
                    'key' => 'pm',
                    'group' => 'pm',
                    'items' => collect([
                        ['when' => now()->setHour(14), 'title' => '2pm'],
                        ['when' => now()->setHour(23), 'title' => '11pm'],
                    ]),
                ],
                [
                    'key' => 'am',
                    'group' => 'am',
                    'items' => collect([
                        ['when' => now()->setHour(3), 'title' => '3am'],
                        ['when' => now()->setHour(10), 'title' => '10am'],
                    ]),
                ],
            ]),
        ]);

        $this->assertEquals($expected, $this->modify($items, 'when|a'));
    }

    /** @test */
    public function it_groups_by_date_with_custom_group_format()
    {
        Carbon::setTestNow(Carbon::parse('2022-09-01'));

        $items = [
            ['when' => now()->setHour(14), 'title' => '2pm'],
            ['when' => now()->setHour(3), 'title' => '3am'],
            ['when' => now()->setHour(10), 'title' => '10am'],
            ['when' => now()->setHour(23), 'title' => '11pm'],
        ];

        $expected = collect([
            'pm' => collect([
                ['when' => now()->setHour(14), 'title' => '2pm'],
                ['when' => now()->setHour(23), 'title' => '11pm'],
            ]),
            'am' => collect([
                ['when' => now()->setHour(3), 'title' => '3am'],
                ['when' => now()->setHour(10), 'title' => '10am'],
            ]),
            'groups' => collect([
                [
                    'key' => 'pm',
                    'group' => 'September PM',
                    'items' => collect([
                        ['when' => now()->setHour(14), 'title' => '2pm'],
                        ['when' => now()->setHour(23), 'title' => '11pm'],
                    ]),
                ],
                [
                    'key' => 'am',
                    'group' => 'September AM',
                    'items' => collect([
                        ['when' => now()->setHour(3), 'title' => '3am'],
                        ['when' => now()->setHour(10), 'title' => '10am'],
                    ]),
                ],
            ]),
        ]);

        $this->assertEquals($expected, $this->modify($items, 'when|a|F A'));
    }

    public function modify($items, $value)
    {
        return Modify::value($items)->groupBy($value)->fetch();
    }
}

class TestGroupByClass
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function __toString()
    {
        return (string) $this->value;
    }
}
