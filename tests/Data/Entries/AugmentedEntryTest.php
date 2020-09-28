<?php

namespace Tests\Data\Entries;

use Carbon\Carbon;
use Facades\Tests\Factories\EntryFactory;
use Mockery;
use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Contracts\Entries\Collection as CollectionContract;
use Statamic\Entries\AugmentedEntry;
use Statamic\Entries\Entry;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\User;
use Statamic\Fields\Value;
use Tests\Data\AugmentedTestCase;

class AugmentedEntryTest extends AugmentedTestCase
{
    /** @test */
    public function it_has_a_parent_method()
    {
        $entry = Mockery::mock(Entry::class);
        $entry->shouldReceive('parent')->andReturn('the parent');

        $augmented = new AugmentedEntry($entry);

        $this->assertEquals('the parent', $augmented->get('parent'));
    }

    /** @test */
    public function it_gets_values()
    {
        Carbon::setTestNow('2020-04-15 13:00:00');
        config(['statamic.amp.enabled' => true]);

        $blueprint = Blueprint::makeFromFields([
            'two' => ['type' => 'text'],
            'four' => ['type' => 'text'],
            'six' => ['type' => 'text'],
            'unused_in_bp' => ['type' => 'text'],
        ])->setHandle('test');
        Blueprint::shouldReceive('in')->with('collections/test')->andReturn(collect(['test' => $blueprint]));

        $collection = tap(Collection::make('test')
            ->routes('/test/{slug}')
            ->ampable(true)
            ->cascade(['seven' => 'the "seven" value from the collection']))
            ->save();

        EntryFactory::id('origin-id')
            ->data([
                'five' => 'the "five" value from the origin',
                'six' => 'the "six" value from the origin and in the blueprint',
            ])
            ->collection('test')
            ->create();

        User::make()->id('test-user')->save();

        $entry = EntryFactory::id('entry-id')
            ->collection('test')
            ->slug('entry-slug')
            ->data([
                'one' => 'the "one" value on the entry',
                'two' => 'the "two" value on the entry and in the blueprint',
                'updated_by' => 'test-user',
                'updated_at' => '1486131000',
            ])
            ->create();

        $entry
            ->origin('origin-id')
            ->date('2018-01-03-1705')
            ->blueprint('test')
            ->setSupplement('three', 'the "three" value supplemented on the entry')
            ->setSupplement('four', 'the "four" value supplemented on the entry and in the blueprint');

        $augmented = new AugmentedEntry($entry);

        $expectations = [
            'id'            => ['type' => 'string', 'value' => 'entry-id'],
            'slug'          => ['type' => Value::class, 'value' => 'entry-slug'],
            'uri'           => ['type' => 'string', 'value' => '/test/entry-slug'],
            'url'           => ['type' => 'string', 'value' => '/test/entry-slug'],
            'edit_url'      => ['type' => 'string', 'value' => 'http://localhost/cp/collections/test/entries/entry-id/entry-slug'],
            'permalink'     => ['type' => 'string', 'value' => 'http://localhost/test/entry-slug'],
            'amp_url'       => ['type' => 'string', 'value' => 'http://localhost/amp/test/entry-slug'],
            'api_url'       => ['type' => 'string', 'value' => 'http://localhost/api/collections/test/entries/entry-id'],
            'published'     => ['type' => 'bool', 'value' => true],
            'private'       => ['type' => 'bool', 'value' => false],
            'date'          => ['type' => Carbon::class, 'value' => '2018-01-03 17:05'],
            'order'         => ['type' => 'null', 'value' => null], // todo: test for when this is an int
            'is_entry'      => ['type' => 'bool', 'value' => true],
            'collection'    => ['type' => CollectionContract::class, 'value' => $collection],
            'last_modified' => ['type' => Carbon::class, 'value' => '2017-02-03 14:10'],
            'updated_at'    => ['type' => Carbon::class, 'value' => '2017-02-03 14:10'],
            'updated_by'    => ['type' => UserContract::class, 'value' => 'test-user'],
            'one'           => ['type' => 'string', 'value' => 'the "one" value on the entry'],
            'two'           => ['type' => Value::class, 'value' => 'the "two" value on the entry and in the blueprint'],
            'three'         => ['type' => 'string', 'value' => 'the "three" value supplemented on the entry'],
            'four'          => ['type' => Value::class, 'value' => 'the "four" value supplemented on the entry and in the blueprint'],
            'five'          => ['type' => 'string', 'value' => 'the "five" value from the origin'],
            'six'           => ['type' => Value::class, 'value' => 'the "six" value from the origin and in the blueprint'],
            'seven'         => ['type' => 'string', 'value' => 'the "seven" value from the collection'],
            'title'         => ['type' => Value::class, 'value' => null],
            'unused_in_bp'  => ['type' => Value::class, 'value' => null],
        ];

        $this->assertAugmentedCorrectly($expectations, $augmented);
    }
}
