<?php

namespace Tests\Data\Entries;

use Statamic\API;
use Tests\TestCase;
use Statamic\Sites\Site;
use Statamic\Fields\Blueprint;
use Statamic\Data\Entries\Entry;
use Statamic\Events\Data\EntrySaved;
use Illuminate\Support\Facades\Event;
use Statamic\Data\Entries\Collection;
use Statamic\Data\Entries\LocalizedEntry;
use Tests\PreventSavingStacheItemsToDisk;
use Facades\Statamic\Fields\BlueprintRepository;

class LocalizedEntryTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    function it_sets_and_gets_the_locale()
    {
        $entry = new LocalizedEntry;
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

        $entry = (new LocalizedEntry)->locale('en');

        $site = $entry->site();
        $this->assertInstanceOf(Site::class, $site);
        $this->assertEquals('en_US', $site->locale());
    }

    /** @test */
    function it_sets_and_gets_the_slug()
    {
        $entry = new LocalizedEntry;
        $this->assertNull($entry->slug());

        $return = $entry->slug('foo');

        $this->assertEquals($entry, $return);
        $this->assertEquals('foo', $entry->slug());
    }

    /** @test */
    function it_sets_and_gets_data_values()
    {
        $entry = new LocalizedEntry;
        $this->assertNull($entry->get('foo'));

        $return = $entry->set('foo', 'bar');

        $this->assertEquals($entry, $return);
        $this->assertTrue($entry->has('foo'));
        $this->assertEquals('bar', $entry->get('foo'));
        $this->assertEquals('fallback', $entry->get('unknown', 'fallback'));
    }

    /** @test */
    function it_gets_and_sets_data_values_using_magic_properties()
    {
        $entry = new LocalizedEntry;
        $this->assertNull($entry->foo);

        $entry->foo = 'bar';

        $this->assertTrue($entry->has('foo'));
        $this->assertEquals('bar', $entry->foo);
    }

    /** @test */
    function it_gets_and_sets_all_data()
    {
        $entry = new LocalizedEntry;
        $this->assertEquals([], $entry->data());

        $return = $entry->data(['foo' => 'bar']);

        $this->assertEquals($entry, $return);
        $this->assertEquals(['foo' => 'bar'], $entry->data());
    }

    /** @test */
    function it_merges_in_additional_data()
    {
        $entry = (new LocalizedEntry)->data([
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
        ], $entry->data());
    }

    /** @test */
    function it_gets_and_sets_the_id()
    {
        $entry = new LocalizedEntry;
        $this->assertNull($entry->id());

        $return = $entry->id('123');

        $this->assertEquals($entry, $return);
        $this->assertEquals('123', $entry->id());
    }

    /** @test */
    function it_gets_the_entry()
    {
        $entry = new LocalizedEntry;
        $this->assertNull($entry->entry());

        $return = $entry->entry($parent = new Entry);

        $this->assertEquals($entry, $return);
        $this->assertEquals($parent, $entry->entry());
    }

    /** @test */
    function it_gets_the_collection_from_the_parent()
    {
        $parent = new Entry;
        $entry = (new LocalizedEntry)->entry($parent);
        $this->assertNull($entry->collection());

        $parent->collection($collection = new Collection);

        $this->assertEquals($collection, $entry->collection());
    }

    /** @test */
    function it_gets_the_url_from_the_collection()
    {
        API\Site::setConfig(['default' => 'en', 'sites' => [
            'en' => ['url' => 'http://domain.com/'],
            'fr' => ['url' => 'http://domain.com/fr/'],
            'de' => ['url' => 'http://domain.de/'],
        ]]);

        $collection = (new Collection)->route([
            'en' => 'blog/{slug}',
            'fr' => 'le-blog/{slug}',
            'de' => 'das-blog/{slug}',
        ]);
        $parent = (new Entry)->collection($collection);
        $entryEn = (new LocalizedEntry)->entry($parent)->locale('en')->slug('foo');
        $entryFr = (new LocalizedEntry)->entry($parent)->locale('fr')->slug('le-foo');
        $entryDe = (new LocalizedEntry)->entry($parent)->locale('de')->slug('das-foo');

        $this->assertEquals('/blog/foo', $entryEn->uri());
        $this->assertEquals('/blog/foo', $entryEn->url());
        $this->assertEquals('http://domain.com/blog/foo', $entryEn->absoluteUrl());

        $this->assertEquals('/le-blog/le-foo', $entryFr->uri());
        $this->assertEquals('/fr/le-blog/le-foo', $entryFr->url());
        $this->assertEquals('http://domain.com/fr/le-blog/le-foo', $entryFr->absoluteUrl());

        $this->assertEquals('/das-blog/das-foo', $entryDe->uri());
        $this->assertEquals('/das-blog/das-foo', $entryDe->url());
        $this->assertEquals('http://domain.de/das-blog/das-foo', $entryDe->absoluteUrl());
    }

    /** @test */
    function it_gets_and_sets_supplemental_data()
    {
        $entry = new LocalizedEntry;
        $this->assertEquals([], $entry->supplements());

        $return = $entry->setSupplement('foo', 'bar');

        $this->assertEquals($entry, $return);
        $this->assertEquals('bar', $entry->getSupplement('foo'));
        $this->assertEquals(['foo' => 'bar'], $entry->supplements());
    }

    /** @test */
    function it_converts_to_array()
    {
        $entry = (new LocalizedEntry)
            ->locale('en')
            ->entry((new Entry)->collection(new Collection))
            ->data(['foo' => 'bar', 'bar' => 'baz'])
            ->setSupplement('baz', 'qux')
            ->setSupplement('foo', 'overridden');

        $this->assertArraySubset([
            'foo' => 'overridden',
            'bar' => 'baz',
            'baz' => 'qux',
        ], $entry->toArray());
    }

    /** @test */
    function it_gets_and_sets_initial_path()
    {
        $entry = new LocalizedEntry;
        $this->assertNull($entry->initialPath());

        $return = $entry->initialPath('123');

        $this->assertEquals($entry, $return);
        $this->assertEquals('123', $entry->initialPath());
    }

    /** @test */
    function it_gets_the_path()
    {
        $collection = (new Collection)->handle('blog');
        $parent = (new Entry)->collection($collection);
        $entry = (new LocalizedEntry)->entry($parent)->locale('en')->slug('post');

        $this->assertEquals('blog/en/post.md', $entry->path());
        $this->assertEquals('blog/en/2018-01-02.post.md', $entry->order('2018-01-02')->path());
        $this->assertEquals('blog/en/2.post.md', $entry->order('2')->path());
    }

    /** @test */
    function it_gets_and_sets_the_order()
    {
        $entry = new LocalizedEntry;
        $this->assertNull($entry->order());

        $return = $entry->order('123');

        $this->assertEquals($entry, $return);
        $this->assertEquals('123', $entry->order());
    }

    /** @test */
    function it_gets_and_sets_the_published_state()
    {
        $entry = new LocalizedEntry;
        $this->assertTrue($entry->published());

        $return = $entry->published(false);

        $this->assertEquals($entry, $return);
        $this->assertFalse($entry->published());
    }

    /** @test */
    function it_can_be_published()
    {
        $entry = (new LocalizedEntry)->published(false);

        $return = $entry->publish();

        $this->assertEquals($entry, $return);
        $this->assertTrue($entry->published());
    }

    /** @test */
    function it_can_be_unpublished()
    {
        $entry = (new LocalizedEntry)->published(true);

        $return = $entry->unpublish();

        $this->assertEquals($entry, $return);
        $this->assertFalse($entry->published());
    }

    /** @test */
    function it_gets_the_blueprint_based_on_the_data()
    {
        $blueprint = new Blueprint;
        BlueprintRepository::shouldReceive('find')->with('test')->andReturn($blueprint);
        $entry = (new LocalizedEntry)->set('blueprint', 'test');

        $this->assertEquals($blueprint, $entry->blueprint());
    }

    /** @test */
    function it_gets_the_blueprint_based_on_the_collection()
    {
        BlueprintRepository::shouldReceive('find')->with('test')->andReturn($blueprint = new Blueprint);
        BlueprintRepository::shouldReceive('find')->with('another')->andReturn(new Blueprint);

        (new Entry)
            ->collection((new Collection)->entryBlueprints(['test', 'another']))
            ->addLocalization($localized = new LocalizedEntry);

        $this->assertEquals($blueprint, $localized->blueprint());
    }

    /** @test */
    function it_saves_through_the_api()
    {
        Event::fake();
        API\Entry::spy();

        $entry = (new LocalizedEntry)
            ->entry((new Entry)->collection(new Collection));

        $return = $entry->save();

        $this->assertEquals($entry, $return);
        API\Entry::shouldHaveReceived('save')->with($entry);
        Event::assertDispatched(EntrySaved::class); // TODO: Assert about payload.
    }

    /** @test */
    function it_gets_file_contents_for_saving()
    {
        $entry = (new LocalizedEntry)
            ->id('123')
            ->slug('test')
            ->order('2018-01-01')
            ->published(false)
            ->data([
                'title' => 'The title',
                'array' => ['first one', 'second one'],
                'content' => 'The content'
            ]);

        $expected = <<<'EOT'
---
title: 'The title'
array:
  - 'first one'
  - 'second one'
id: '123'
---
The content
EOT;

        $this->assertEquals($expected, $entry->fileContents());
    }

    /** @test */
    function it_propagates_to_sites_defined_on_the_collection()
    {
        API\Site::setConfig(['default' => 'en', 'sites' => [
            'en' => ['url' => '/'],
            'fr' => ['url' => '/'],
            'de' => ['url' => '/'],
            'es' => ['url' => '/'],
        ]]);

        Event::fake();

        BlueprintRepository::shouldReceive('find')
            ->with('test')
            ->andReturn((new Blueprint)->setContents(['fields' => [
                ['handle' => 'title', 'field' => ['localizable' => true]],
                ['handle' => 'content', 'field' => ['localizable' => true]],
                ['handle' => 'image', 'field' => ['localizable' => false]],
                ['handle' => 'price', 'field' => ['localizable' => false]],
            ]]));

        $collection = (new Collection)
            ->handle('blog')
            ->sites([
                'en', // the one we'll save from
                'fr', // a version that already exists
                'de', // a version that doesn't exist yet
                // intentionally left off "es" to show it only propagates to selected sites.
            ])
            ->entryBlueprints(['test']);

        $en = (new LocalizedEntry)->locale('en')->data($englishData = [
            'title' => 'Title',
            'content' => 'Content',
            'image' => 'image.jpg',
            'price' => '666',
        ]);

        $fr = (new LocalizedEntry)->locale('fr')->data([
            'title' => 'French Title',
            'content' => 'French Content',
            'image' => 'france.jpg',
            'price' => '10',
        ]);

        $entry = (new Entry)
            ->collection($collection)
            ->addLocalization($en)
            ->addLocalization($fr);

        $this->assertEquals(['en', 'fr'], $entry->localizations()->keys()->all());

        $return = $en->propagate();

        $this->assertEquals($en, $return);
        $this->assertEquals(['en', 'fr', 'de'], $entry->localizations()->keys()->all());

        // A little extra proof that it only propagates to the sites defined in the collection.
        $this->assertCount(4, API\Site::all());

        $this->assertEquals([
            // Remain the same because fields are localizable
            'title' => 'French Title',
            'content' => 'French Content',
            // Changed because fields are not localizable
            'image' => 'image.jpg',
            'price' => '666',
        ], $entry->in('fr')->data());

        // Uses english data because that's where it propagated from
        $this->assertEquals($englishData, $entry->in('de')->data());

        // Uses english data because... its the english one.
        $this->assertEquals($englishData, $entry->in('en')->data());
    }

    /** @test */
    function it_gets_and_sets_the_template()
    {
        config(['statamic.theming.views.entry' => 'post']);

        $collection = new Collection;
        $parent = (new Entry)->collection($collection);
        $entry = (new LocalizedEntry)->entry($parent);

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

        $collection = new Collection;
        $parent = (new Entry)->collection($collection);
        $entry = (new LocalizedEntry)->entry($parent);

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
}
