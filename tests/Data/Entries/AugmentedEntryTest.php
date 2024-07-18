<?php

namespace Tests\Data\Entries;

use Carbon\Carbon;
use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Contracts\Entries\Collection as CollectionContract;
use Statamic\Contracts\Query\Builder;
use Statamic\Entries\AugmentedEntry;
use Statamic\Entries\Entry;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\User;
use Statamic\Fields\Blueprint as FieldsBlueprint;
use Tests\Data\AugmentedTestCase;

class AugmentedEntryTest extends AugmentedTestCase
{
    #[Test]
    public function it_has_a_parent_method()
    {
        $entry = Mockery::mock(Entry::class);
        $entry->shouldReceive('blueprint')->zeroOrMoreTimes();
        $entry->shouldReceive('parent')->andReturn('the parent');

        $augmented = new AugmentedEntry($entry);

        $this->assertEquals('the parent', $augmented->get('parent'));
    }

    #[Test]
    public function it_gets_values()
    {
        Carbon::setTestNow('2020-04-15 13:00:00');
        config(['statamic.amp.enabled' => true]);

        $blueprint = Blueprint::makeFromFields([
            'date' => ['type' => 'date', 'time_enabled' => true, 'time_seconds_enabled' => true],
            'two' => ['type' => 'text'],
            'four' => ['type' => 'text'],
            'six' => ['type' => 'text'],
            'unused_in_bp' => ['type' => 'text'],
        ])->setHandle('test');
        Blueprint::shouldReceive('in')->with('collections/test')->andReturn(collect(['test' => $blueprint]));

        $collection = tap(Collection::make('test')
            ->dated(true)
            ->routes('/test/{slug}')
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
                'eight' => 'should be immediately overridden by the supplement',
                'updated_by' => 'test-user',
                'updated_at' => '1486131000',
            ])
            ->create();

        $entry
            ->origin('origin-id')
            ->date('2018-01-03-170512')
            ->blueprint('test')
            ->setSupplement('three', 'the "three" value supplemented on the entry')
            ->setSupplement('four', 'the "four" value supplemented on the entry and in the blueprint')
            ->setSupplement('eight', null);

        $mount = tap(Collection::make('mountable')->mount($entry->id()))->save();

        $augmented = new AugmentedEntry($entry);

        $expectations = [
            'id' => ['type' => 'string', 'value' => 'entry-id'],
            'origin_id' => ['type' => 'string', 'value' => 'origin-id'],
            'slug' => ['type' => 'string', 'value' => 'entry-slug'],
            'uri' => ['type' => 'string', 'value' => '/test/entry-slug'],
            'url' => ['type' => 'string', 'value' => '/test/entry-slug'],
            'edit_url' => ['type' => 'string', 'value' => 'http://localhost/cp/collections/test/entries/entry-id'],
            'permalink' => ['type' => 'string', 'value' => 'http://localhost/test/entry-slug'],
            'api_url' => ['type' => 'string', 'value' => 'http://localhost/api/collections/test/entries/entry-id'],
            'status' => ['type' => 'string', 'value' => 'published'],
            'published' => ['type' => 'bool', 'value' => true],
            'private' => ['type' => 'bool', 'value' => false],
            'date' => ['type' => Carbon::class, 'value' => '2018-01-03 17:05:12'],
            'order' => ['type' => 'null', 'value' => null], // todo: test for when this is an int
            'is_entry' => ['type' => 'bool', 'value' => true],
            'collection' => ['type' => CollectionContract::class, 'value' => $collection],
            'blueprint' => ['type' => FieldsBlueprint::class, 'value' => $blueprint],
            'mount' => ['type' => CollectionContract::class, 'value' => $mount],
            'locale' => ['type' => 'string', 'value' => 'en'],
            'last_modified' => ['type' => Carbon::class, 'value' => '2017-02-03 14:10'],
            'updated_at' => ['type' => Carbon::class, 'value' => '2017-02-03 14:10'],
            'updated_by' => ['type' => UserContract::class, 'value' => 'test-user'],
            'one' => ['type' => 'string', 'value' => 'the "one" value on the entry'],
            'two' => ['type' => 'string', 'value' => 'the "two" value on the entry and in the blueprint'],
            'three' => ['type' => 'string', 'value' => 'the "three" value supplemented on the entry'],
            'four' => ['type' => 'string', 'value' => 'the "four" value supplemented on the entry and in the blueprint'],
            'five' => ['type' => 'string', 'value' => 'the "five" value from the origin'],
            'six' => ['type' => 'string', 'value' => 'the "six" value from the origin and in the blueprint'],
            'seven' => ['type' => 'string', 'value' => 'the "seven" value from the collection'],
            'eight' => ['type' => 'null', 'value' => null], // explicitly supplemented null
            'title' => ['type' => 'string', 'value' => null],
            'unused_in_bp' => ['type' => 'string', 'value' => null],
        ];

        $this->assertAugmentedCorrectly($expectations, $augmented);
    }

    #[Test]
    public function it_gets_the_mount_from_the_value_first_if_it_exists()
    {
        $mount = tap(Collection::make('a'))->save();

        $entry = EntryFactory::id('entry-id')
            ->collection('test')
            ->slug('entry-slug')
            ->create();

        $augmented = new AugmentedEntry($entry);

        $this->assertNull($augmented->get('mount')->value());

        $mount->mount($entry->id())->save();
        $this->assertEquals($mount, $augmented->get('mount')->value());

        $entry->set('mount', 'b');
        $this->assertEquals('b', $augmented->get('mount')->value());
    }

    #[Test]
    public function authors_is_just_the_value_if_its_not_in_the_blueprint()
    {
        $entry = EntryFactory::id('entry-id')
            ->collection('test')
            ->slug('entry-slug')
            ->create();

        // Make sure there are authors on the entry.
        // The singular "author" field is what's read using $entry->authors()
        // But, this test is ensuring that method isn't being called during augmentation of the plural "authors" field.
        $entry->set('author', ['user-1', 'user-2']);

        $augmented = new AugmentedEntry($entry);

        $this->assertNull($augmented->get('authors')->value());

        $entry->set('authors', 'joe and bob');
        $this->assertEquals('joe and bob', $augmented->get('authors')->value());
    }

    #[Test]
    public function it_gets_the_authors_from_the_value_if_its_in_the_blueprint()
    {
        $blueprint = Blueprint::makeFromFields(['authors' => ['type' => 'users']]);
        $userBlueprint = Blueprint::makeFromFields([]);
        BlueprintRepository::shouldReceive('in')->with('collections/test')->andReturn(collect([
            'test' => $blueprint,
        ]));
        BlueprintRepository::shouldReceive('find')->with('user')->andReturn($userBlueprint);

        User::make()->id('user-1')->save();
        User::make()->id('user-2')->save();

        $entry = EntryFactory::id('entry-id')
            ->collection('test')
            ->slug('entry-slug')
            ->create();

        $augmented = new AugmentedEntry($entry);

        // Since it's in the blueprint, and is using a "users" fieldtype, it gets augmented to a querybuilder.
        $authors = $augmented->get('authors')->value();
        $this->assertInstanceOf(Builder::class, $authors);
        $this->assertEquals([], $authors->get()->all());

        $entry->set('authors', ['user-1', 'unknown-user', 'user-2']);
        $authors = $augmented->get('authors')->value();
        $this->assertInstanceOf(Builder::class, $authors);
        $this->assertEveryItemIsInstanceOf(\Statamic\Contracts\Auth\User::class, $authors->get());
        $this->assertEquals(['user-1', 'user-2'], $authors->get()->map->id()->all());
    }

    #[Test]
    public function it_doesnt_evaluated_computed_callbacks_when_getting_keys()
    {
        $computedCallbackCount = 0;
        Collection::computed('test', 'computed', function () use (&$computedCallbackCount) {
            $computedCallbackCount++;

            return 'computed value';
        });

        $entry = EntryFactory::id('entry-id')
            ->collection('test')
            ->slug('entry-slug')
            ->data(['foo' => 'bar'])
            ->create();

        $augmented = new AugmentedEntry($entry);

        $this->assertEquals(0, $computedCallbackCount);
        $augmented->keys();
        $this->assertEquals(0, $computedCallbackCount);
        $augmented->get('computed');
        $this->assertEquals(1, $computedCallbackCount);
        $augmented->get('computed');
        $this->assertEquals(2, $computedCallbackCount);
    }
}
