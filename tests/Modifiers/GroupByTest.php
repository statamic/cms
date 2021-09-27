<?php

namespace Tests\Modifiers;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Collection;
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
                    'group' => 'basketball',
                    'items' => collect([
                        ['sport' => 'basketball', 'team' => 'jazz'],
                        ['sport' => 'basketball', 'team' => 'bulls'],
                    ]),
                ],
                [
                    'group' => 'baseball',
                    'items' => collect([
                        ['sport' => 'baseball', 'team' => 'yankees'],
                    ]),
                ],
            ]),
        ]);

        $this->assertEquals($expected, $this->modify($items, ['sport']));
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
                ['group' => 'basketball', 'items' => collect([$jazz, $bulls])],
                ['group' => 'baseball', 'items' => collect([$yankees])],
            ]),
        ]);

        $this->assertEquals($expected, $this->modify($items, ['sport']));
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
                ['group' => 'Basketball', 'items' => collect([$jazz, $bulls])],
                ['group' => 'Baseball', 'items' => collect([$yankees])],
            ]),
        ]);

        // passing an array like ['collection', 'title'] translates to group_by="collection:title"
        $this->assertEquals($expected, $this->modify($items, ['collection', 'title']));
    }

    public function modify($items, array $value)
    {
        return Modify::value($items)->groupBy($value)->fetch();
    }
}
