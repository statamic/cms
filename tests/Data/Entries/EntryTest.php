<?php

namespace Tests\Data\Entries;

use Facades\Statamic\Fields\BlueprintRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Statamic\Entries\Collection;
use Statamic\Entries\Entry;
use Statamic\Events\Data\EntrySaved;
use Statamic\Events\Data\EntrySaving;
use Statamic\Exceptions\InvalidLocalizationException;
use Statamic\Facades;
use Statamic\Facades\User;
use Statamic\Fields\Blueprint;
use Statamic\Sites\Site;
use Statamic\Support\Arr;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class EntryTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    function it_sets_and_gets_the_locale()
    {
        $entry = new Entry;
        $this->assertNull($entry->locale());

        $return = $entry->locale('en');

        $this->assertEquals($entry, $return);
        $this->assertEquals('en', $entry->locale());
    }

    /** @test */
    function it_gets_the_site()
    {
        config(['statamic.sites.sites' => [
            'en' => ['locale' => 'en_US'],
        ]]);

        $entry = (new Entry)->locale('en');

        $site = $entry->site();
        $this->assertInstanceOf(Site::class, $site);
        $this->assertEquals('en_US', $site->locale());
    }

    /** @test */
    function it_sets_and_gets_the_slug()
    {
        $entry = new Entry;
        $this->assertNull($entry->slug());

        $return = $entry->slug('foo');

        $this->assertEquals($entry, $return);
        $this->assertEquals('foo', $entry->slug());
    }

    /** @test */
    function it_sets_gets_and_removes_data_values()
    {
        $entry = new Entry;
        $this->assertNull($entry->get('foo'));

        $return = $entry->set('foo', 'bar');

        $this->assertEquals($entry, $return);
        $this->assertTrue($entry->has('foo'));
        $this->assertEquals('bar', $entry->get('foo'));
        $this->assertEquals('bar', $entry->value('foo'));
        $this->assertEquals('fallback', $entry->get('unknown', 'fallback'));

        $return = $entry->remove('foo');

        $this->assertEquals($entry, $return);
        $this->assertFalse($entry->has('foo'));
    }

    /** @test */
    function it_gets_and_sets_data_values_using_magic_properties()
    {
        $entry = new Entry;
        $this->assertNull($entry->foo);

        $entry->foo = 'bar';

        $this->assertTrue($entry->has('foo'));
        $this->assertEquals('bar', $entry->foo);
    }

    /** @test */
    function it_gets_sets_and_removes_data_values_using_array_access()
    {
        Collection::make('test')->save();
        $entry = (new Entry)->collection('test');
        $this->assertNull($entry['foo']);
        $this->assertFalse(isset($entry['foo']));

        $entry['foo'] = 'bar';

        $this->assertTrue($entry->has('foo'));
        $this->assertTrue(isset($entry['foo']));
        $this->assertEquals('bar', $entry['foo']);

        unset($entry['foo']);

        $this->assertFalse($entry->has('foo'));
        $this->assertFalse(isset($entry['foo']));
        $this->assertNull($entry['foo']);
    }

    /** @test */
    function it_gets_and_sets_all_data()
    {
        $entry = new Entry;
        $this->assertEquals([], $entry->data()->all());

        $return = $entry->data(['foo' => 'bar']);

        $this->assertEquals($entry, $return);
        $this->assertEquals(['foo' => 'bar'], $entry->data()->all());
    }

    /** @test */
    function it_merges_in_additional_data()
    {
        $entry = (new Entry)->data([
            'foo' => 'bar',
            'bar' => 'baz',
            'baz' => 'qux',
        ]);

        $return = $entry->merge([
            'bar' => 'merged bar',
            'qux' => 'merged qux',
        ]);

        $this->assertEquals($entry, $return);
        $this->assertEquals([
            'foo' => 'bar',
            'bar' => 'merged bar',
            'baz' => 'qux',
            'qux' => 'merged qux',
        ], $entry->data()->all());
    }

    /** @test */
    function values_fall_back_to_the_origin_then_the_collection()
    {
        $collection = tap(Collection::make('test'))->save();
        $origin = (new Entry)->collection('test');
        $entry = (new Entry)->origin($origin)->collection('test');

        $this->assertNull($entry->value('test'));

        $collection->cascade(['test' => 'from collection']);
        $this->assertEquals('from collection', $entry->value('test'));

        $origin->set('test', 'from origin');
        $this->assertEquals('from origin', $entry->value('test'));
    }

    /** @test */
    function it_gets_values_from_origin_and_collection()
    {
        tap(Collection::make('test')->cascade([
            'one' => 'one in collection',
            'two' => 'two in collection',
            'three' => 'three in collection',
        ]))->save();

        $origin = (new Entry)->collection('test')->data([
            'two' => 'two in origin',
            'three' => 'three in origin',
        ]);

        $entry = (new Entry)->origin($origin)->collection('test')->data([
            'three' => 'three in entry',
        ]);

        $this->assertEquals([
            'one' => 'one in collection',
            'two' => 'two in origin',
            'three' => 'three in entry',
        ], $entry->values()->all());
    }

    /** @test */
    function it_gets_the_url_from_the_collection()
    {
        config(['statamic.amp.enabled' => true]);

        Facades\Site::setConfig(['default' => 'en', 'sites' => [
            'en' => ['url' => 'http://domain.com/'],
            'fr' => ['url' => 'http://domain.com/fr/'],
            'de' => ['url' => 'http://domain.de/'],
        ]]);

        $collection = (new Collection)->handle('blog')->ampable(true)->route([
            'en' => 'blog/{slug}',
            'fr' => 'le-blog/{slug}',
            'de' => 'das-blog/{slug}',
        ]);
        $collection->save();
        $entryEn = (new Entry)->collection($collection)->locale('en')->slug('foo');
        $entryFr = (new Entry)->collection($collection)->locale('fr')->slug('le-foo');
        $entryDe = (new Entry)->collection($collection)->locale('de')->slug('das-foo');

        $this->assertEquals('/blog/foo', $entryEn->uri());
        $this->assertEquals('/blog/foo', $entryEn->url());
        $this->assertEquals('http://domain.com/blog/foo', $entryEn->absoluteUrl());
        $this->assertEquals('http://domain.com/amp/blog/foo', $entryEn->ampUrl());

        $this->assertEquals('/le-blog/le-foo', $entryFr->uri());
        $this->assertEquals('/fr/le-blog/le-foo', $entryFr->url());
        $this->assertEquals('http://domain.com/fr/le-blog/le-foo', $entryFr->absoluteUrl());
        $this->assertEquals('http://domain.com/fr/amp/le-blog/le-foo', $entryFr->ampUrl());

        $this->assertEquals('/das-blog/das-foo', $entryDe->uri());
        $this->assertEquals('/das-blog/das-foo', $entryDe->url());
        $this->assertEquals('http://domain.de/das-blog/das-foo', $entryDe->absoluteUrl());
        $this->assertEquals('http://domain.de/amp/das-blog/das-foo', $entryDe->ampUrl());
    }

    /** @test */
    function it_gets_and_sets_supplemental_data()
    {
        $entry = new Entry;
        $this->assertEquals([], $entry->supplements()->all());

        $return = $entry->setSupplement('foo', 'bar');

        $this->assertEquals($entry, $return);
        $this->assertEquals('bar', $entry->getSupplement('foo'));
        $this->assertEquals(['foo' => 'bar'], $entry->supplements()->all());
    }

    /** @test */
    function it_compiles_augmented_array_data()
    {
        $user = tap(User::make()->id('user-1'))->save();

        $entry = (new Entry)
            ->locale('en')
            ->slug('test')
            ->collection(Collection::make('blog')->save())
            ->data([
                'foo' => 'bar',
                'bar' => 'baz',
                'updated_at' => $lastModified = now()->subDays(1)->timestamp,
                'updated_by' => $user->id(),
            ])
            ->setSupplement('baz', 'qux')
            ->setSupplement('foo', 'overridden');

        $this->assertArraySubset([
            'foo' => 'overridden',
            'bar' => 'baz',
            'baz' => 'qux',
            'last_modified' => $carbon = Carbon::createFromTimestamp($lastModified),
            'updated_at' => $carbon,
            'updated_by' => $user->toArray(),
        ], $entry->augmentedArrayData());
    }

    /** @test */
    function it_gets_and_sets_initial_path()
    {
        $entry = new Entry;
        $this->assertNull($entry->initialPath());

        $return = $entry->initialPath('123');

        $this->assertEquals($entry, $return);
        $this->assertEquals('123', $entry->initialPath());
    }

    /** @test */
    function it_gets_the_path_and_excludes_locale_when_theres_a_single_site()
    {
        Facades\Site::setConfig(['default' => 'en', 'sites' => [
            'en' => ['url' => '/'],
        ]]);

        $collection = (new Collection)->handle('blog');
        $entry = (new Entry)->collection($collection)->locale('en')->slug('post');

        $this->assertEquals($this->fakeStacheDirectory.'/content/collections/blog/post.md', $entry->path());
        $this->assertEquals($this->fakeStacheDirectory.'/content/collections/blog/2018-01-02.post.md', $entry->date('2018-01-02')->path());
    }

    /** @test */
    function it_gets_the_path_and_includes_locale_when_theres_multiple_sites()
    {
        Facades\Site::setConfig(['default' => 'en', 'sites' => [
            'en' => ['url' => '/'],
            'fr' => ['url' => '/'],
        ]]);

        $collection = (new Collection)->handle('blog');
        $entry = (new Entry)->collection($collection)->locale('en')->slug('post');

        $this->assertEquals($this->fakeStacheDirectory.'/content/collections/blog/en/post.md', $entry->path());
        $this->assertEquals($this->fakeStacheDirectory.'/content/collections/blog/en/2018-01-02.post.md', $entry->date('2018-01-02')->path());
    }

    /** @test */
    function it_gets_and_sets_the_date()
    {
        $entry = new Entry;
        $this->assertNull($entry->date());

        // Date can be provided as string without time
        $return = $entry->date('2015-03-05');
        $this->assertEquals($entry, $return);
        $this->assertInstanceOf(Carbon::class, $entry->date());
        $this->assertTrue(Carbon::createFromFormat('Y-m-d H:i', '2015-03-05 00:00')->eq($entry->date()));

        // Date can be provided as string with time
        $entry->date('2015-03-05-1325');
        $this->assertInstanceOf(Carbon::class, $entry->date());
        $this->assertTrue(Carbon::createFromFormat('Y-m-d H:i', '2015-03-05 13:25')->eq($entry->date()));

        // Date can be provided as carbon instance
        $carbon = Carbon::createFromFormat('Y-m-d H:i', '2018-05-02 17:32');
        $entry->date($carbon);
        $this->assertInstanceOf(Carbon::class, $entry->date());
        $this->assertTrue($carbon->eq($entry->date()));
    }

    /** @test */
    function it_gets_and_sets_the_order()
    {
        $collection = tap(Collection::make('ordered'))->save();
        $one = (new Entry)->id('one')->collection($collection);
        $this->assertNull($one->order());

        $return = $one->order(5);
        $this->assertEquals($one, $return);
        $this->assertEquals(1, $one->order());

        $two = (new Entry)->id('two')->collection($collection);
        $two->order(10);
        $this->assertEquals(1, $one->order());
        $this->assertEquals(2, $two->order());

        $three = (new Entry)->id('three')->collection($collection);
        $three->order(2);
        $this->assertEquals(2, $one->order());
        $this->assertEquals(3, $two->order());
        $this->assertEquals(1, $three->order());
    }

    /** @test */
    function it_sets_the_order_on_the_collection_when_dealing_with_numeric_collections()
    {
        $collection = tap(Collection::make('ordered')->orderable(true))->save();
        $one = (new Entry)->id('one')->collection($collection);
        $two = (new Entry)->id('two')->collection($collection);

        $one->order('3');
        $two->order('2');

        $this->assertEquals([2 => 'two', 3 => 'one'], $collection->getEntryPositions()->all());
        $this->assertEquals(['two', 'one'], $collection->getEntryOrder()->all());

        $this->assertEquals(2, $one->order());
        $this->assertEquals(1, $two->order());
    }

    /** @test */
    function it_gets_and_sets_the_date_for_date_collections()
    {
        $dateEntry = with('', function() {
            $collection = tap(Collection::make('dated')->dated(true))->save();
            return (new Entry)->collection($collection);
        });
        $numberEntry = with('', function() {
            $collection = tap(Collection::make('ordered')->orderable(true))->save();
            return (new Entry)->collection($collection);
        });
        $this->assertNull($dateEntry->order());
        $this->assertNull($numberEntry->order());

        $dateEntry->date('2017-01-02');
        $numberEntry->order('2017-01-02');

        $this->assertEquals('2017-01-02 12:00am', $dateEntry->date()->format('Y-m-d h:ia'));
        $this->assertFalse($dateEntry->hasTime());
        $this->assertNull($numberEntry->date());

        $dateEntry->date('2017-01-02-1523');
        $this->assertEquals('2017-01-02 03:23pm', $dateEntry->date()->format('Y-m-d h:ia'));
        $this->assertTrue($dateEntry->hasTime());
    }

    /** @test */
    function future_dated_entries_are_private_when_configured_in_the_collection()
    {
        Carbon::setTestNow('2019-01-01');
        $collection = tap(Collection::make('dated')->dated(true)->futureDateBehavior('private'))->save();
        $entry = (new Entry)->collection($collection);

        $entry->date('2018-01-01');
        $this->assertFalse($entry->private());

        $entry->date('2019-01-02');
        $this->assertTrue($entry->private());
    }

    /** @test */
    function past_dated_entries_are_private_when_configured_in_the_collection()
    {
        Carbon::setTestNow('2019-01-01');
        $collection = tap(Collection::make('dated')->dated(true)->pastDateBehavior('private'))->save();
        $entry = (new Entry)->collection($collection);

        $entry->date('2019-01-02');
        $this->assertFalse($entry->private());

        $entry->date('2018-01-02');
        $this->assertTrue($entry->private());
    }

    /** @test */
    function it_gets_and_sets_the_published_state()
    {
        $entry = new Entry;
        $this->assertTrue($entry->published());

        $return = $entry->published(false);

        $this->assertEquals($entry, $return);
        $this->assertFalse($entry->published());
    }

    /** @test */
    function it_gets_the_blueprint_when_defined_on_itself()
    {
        Collection::make('blog')->save();
        BlueprintRepository::shouldReceive('find')->with('default')->andReturn($default = new Blueprint);
        BlueprintRepository::shouldReceive('find')->with('test')->andReturn($blueprint = new Blueprint);
        $entry = (new Entry)
            ->collection('blog')
            ->blueprint('test');

        $this->assertSame($blueprint, $entry->blueprint());
        $this->assertNotSame($default, $entry->blueprint());
    }

    /** @test */
    function it_gets_the_blueprint_based_on_the_collection()
    {
        BlueprintRepository::shouldReceive('find')->with('test')->andReturn($blueprint = new Blueprint);
        BlueprintRepository::shouldReceive('find')->with('another')->andReturn(new Blueprint);

        $collection = tap(Collection::make('test')->entryBlueprints(['test', 'another']))->save();
        $entry = (new Entry)->collection($collection);

        $this->assertEquals($blueprint, $entry->blueprint());
    }

    /** @test */
    function it_saves_through_the_api()
    {
        Event::fake();
        $entry = (new Entry)->collection(new Collection);
        Facades\Entry::shouldReceive('save')->with($entry);
        Facades\Entry::shouldReceive('taxonomize')->with($entry);

        $return = $entry->save();

        $this->assertTrue($return);
        Event::assertDispatched(EntrySaving::class, function ($event) use ($entry) {
            return $event->data === $entry;
        });
        Event::assertDispatched(EntrySaved::class, function ($event) use ($entry) {
            return $event->data === $entry;
        });
    }

    /** @test */
    function if_saving_event_returns_false_the_entry_doesnt_save()
    {
        Facades\Entry::spy();
        Event::fake([EntrySaved::class]);

        Event::listen(EntrySaving::class, function () {
            return false;
        });

        $entry = (new Entry)->collection(new Collection);

        $return = $entry->save();

        $this->assertFalse($return);
        Facades\Entry::shouldNotHaveReceived('save');
        Event::assertNotDispatched(EntrySaved::class);
    }

    /** @test */
    function it_gets_file_contents_for_saving()
    {
        $entry = (new Entry)
            ->id('123')
            ->slug('test')
            ->date('2018-01-01')
            ->published(false)
            ->data([
                'title' => 'The title',
                'array' => ['first one', 'second one'],
                'content' => 'The content'
            ]);

        $this->assertEquals([
            'title' => 'The title',
            'array' => [
                'first one',
                'second one',
            ],
            'id' => '123',
            'published' => false,
            'content' => 'The content',
        ], Arr::removeNullValues($entry->fileData()));
    }

    /** @test */
    function it_gets_and_sets_the_template()
    {
        config(['statamic.theming.views.entry' => 'post']);

        $collection = tap(Collection::make('test'))->save();
        $entry = (new Entry)->collection($collection);

        // defaults to the configured
        $this->assertEquals('post', $entry->template());

        // collection level overrides the configured
        $collection->template('foo');
        $this->assertEquals('foo', $entry->template());

        // entry level overrides the collection
        $return = $entry->template('bar');
        $this->assertEquals($entry, $return);
        $this->assertEquals('bar', $entry->template());
    }

    /** @test */
    function it_gets_and_sets_the_layout()
    {
        config(['statamic.theming.views.layout' => 'default']);

        $collection = tap(Collection::make('test'))->save();
        $entry = (new Entry)->collection($collection);

        // defaults to the configured
        $this->assertEquals('default', $entry->layout());

        // collection level overrides the configured
        $collection->layout('foo');
        $this->assertEquals('foo', $entry->layout());

        // entry level overrides the collection
        $return = $entry->layout('bar');
        $this->assertEquals($entry, $return);
        $this->assertEquals('bar', $entry->layout());
    }

    /** @test */
    function it_gets_the_last_modified_time()
    {
        $collection = tap(Collection::make('test'))->save();
        $entry = (new Entry)->collection($collection)->slug('bar');
        $path = $entry->path();
        $date = Carbon::parse('2017-01-02');
        mkdir(dirname($path));
        touch($path, $date->timestamp);

        $this->assertTrue($date->eq($entry->lastModified()));

        $valueBasedDate = Carbon::parse('2017-01-03');
        $entry->set('updated_at', $valueBasedDate->timestamp);
        $this->assertFalse($date->eq($entry->lastModified()));
        $this->assertTrue($valueBasedDate->eq($entry->lastModified()));

        @unlink($path);
    }

    /** @test */
    function it_gets_and_sets_the_collection()
    {
        $entry = new Entry;
        $collection = tap(Collection::make('foo'))->save();
        $this->assertNull($entry->collection());

        $return = $entry->collection($collection);

        $this->assertEquals($entry, $return);
        $this->assertEquals($collection, $entry->collection());
        $this->assertEquals('foo', $entry->collectionHandle());
    }

    /** @test */
    function it_gets_and_sets_the_id()
    {
        $entry = new Entry;
        $this->assertNull($entry->id());

        $return = $entry->id('123');

        $this->assertEquals($entry, $return);
        $this->assertEquals('123', $entry->id());
        // $this->assertEquals('entry::123', $entry->reference()); // TODO, implementation works but test needs to be adjusted
    }

    /** @test */
    function it_deletes_through_the_api()
    {
        Event::fake();
        $entry = new Entry;
        Facades\Entry::shouldReceive('delete')->with($entry);

        $return = $entry->delete();

        $this->assertTrue($return);
    }

    // todo: add tests for localization things. in(), descendants(), addLocalization(), etc
}
