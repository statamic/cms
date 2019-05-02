<?php

namespace Tests\Data\Entries;

use Statamic\API;
use Tests\TestCase;
use Statamic\API\Site;
use Statamic\Fields\Blueprint;
use Statamic\Data\Entries\Entry;
use Illuminate\Support\Facades\Event;
use Statamic\Data\Entries\Collection;
use Statamic\Data\Entries\LocalizedEntry;
use Facades\Statamic\Fields\BlueprintRepository;
use Statamic\Exceptions\InvalidLocalizationException;

class EntryTest extends TestCase
{
    /** @test */
    function it_gets_and_sets_the_collection()
    {
        $entry = new Entry;
        $collection = (new Collection)->handle('foo');
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
    function it_adds_a_localized_entry()
    {
        $entry = new Entry;
        $localized = (new LocalizedEntry)->locale('en');
        $this->assertEquals([], $entry->localizations()->all());

        $return = $entry->addLocalization($localized);

        $this->assertEquals($entry, $return);
        $this->assertEquals(['en' => $localized], $entry->localizations()->all());
        $this->assertEquals($entry, $localized->entry());
        $this->assertEquals($localized, $entry->in('en'));
    }

    /** @test */
    function it_removes_a_localized_entry()
    {
        $localization = (new LocalizedEntry)->locale('en');
        $entry = (new Entry)->addLocalization($localization);
        $this->assertCount(1, $entry->localizations());

        $return = $entry->removeLocalization($localization);

        $this->assertEquals($entry, $return);
        $this->assertCount(0, $entry->localizations());
    }

    /** @test */
    function an_exception_is_thrown_when_getting_a_localization_that_doesnt_exist()
    {
        $this->expectException(InvalidLocalizationException::class);
        $this->expectExceptionMessage('Entry is not localized into the [fr] site');

        $entry = new Entry;
        $localized = (new LocalizedEntry)->locale('en');
        $entry->addLocalization($localized);

        $entry->in('fr');
    }

    /** @test */
    function it_creates_a_localization_in_the_default_site_when_passing_a_closure()
    {
        Site::setConfig(['default' => 'en', 'sites' => [
            'en' => ['url' => 'http://domain.com'],
        ]]);

        $entry = (new Entry)->id('123');
        $this->assertCount(0, $entry->localizations());
        $callbackRan = false;

        $localized = $entry->in(function ($localized) {
            $localized->slug('test');
        });

        $this->assertCount(1, $entry->localizations());
        $this->assertEquals($entry->in('en'), $localized);
        $this->assertEquals(['en'], $entry->localizations()->keys()->all());
        $this->assertEquals('123', $localized->id());
        $this->assertEquals('test', $localized->slug());
    }

    /** @test */
    function it_creates_a_localization_in_the_specified_site_when_passing_a_closure()
    {
        $entry = (new Entry)->id('123');
        $this->assertCount(0, $entry->localizations());
        $callbackRan = false;

        $localized = $entry->in('fr', function ($localized) {
            $localized->slug('test');
        });

        $this->assertCount(1, $entry->localizations());
        $this->assertEquals($entry->in('fr'), $localized);
        $this->assertEquals(['fr'], $entry->localizations()->keys()->all());
        $this->assertEquals('123', $localized->id());
        $this->assertEquals('test', $localized->slug());
    }

    /** @test */
    function gets_a_value_using_the_current_site()
    {
        Site::setConfig(['default' => 'en', 'sites' => [
            'en' => ['url' => 'http://domain.com'],
            'fr' => ['url' => 'http://domain.fr'],
            'de' => ['url' => 'http://domain.de'],
        ]]);

        $entry = (new Entry)
            ->addLocalization((new LocalizedEntry)->locale('en')->set('test', 'english'))
            ->addLocalization((new LocalizedEntry)->locale('fr')->set('test', 'french'));

        $this->assertEquals('english', $entry->get('test'));

        Site::setCurrent('fr');
        $this->assertEquals('french', $entry->get('test'));

        try {
            Site::setCurrent('de');
            $entry->get('test');
        } catch (InvalidLocalizationException $e) {
            return $this->assertEquals('Entry is not localized into the [de] site', $e->getMessage());
        }

        $this->fail('Exception was not thrown.');
    }

    /** @test */
    function it_localized_version_or_clones_an_existing_one()
    {
        $entry = (new Entry)
            ->addLocalization((new LocalizedEntry)->locale('en')->initialPath('/path/to/en.md')->data(['foo' => 'bar']))
            ->addLocalization((new LocalizedEntry)->locale('de')->data(['foo' => 'das bar']))
            ->addLocalization((new LocalizedEntry)->locale('es')->data(['foo' => 'las bar']));

        $this->assertEquals('/path/to/en.md', $entry->in('en')->initialPath());

        // Without second argument just clones the first localization
        $clone = $entry->inOrClone('fr');
        $this->assertEquals('fr', $clone->locale());
        $this->assertEquals(['foo' => 'bar'], $clone->data());
        $this->assertNull($clone->initialPath());

        // Second argument specifies which one to clone
        $specficClone = $entry->inOrClone('fr', 'de');
        $this->assertEquals('fr', $specficClone->locale());
        $this->assertEquals(['foo' => 'das bar'], $specficClone->data());
        $this->assertNull($clone->initialPath());
    }

    /** @test */
    function it_passes_methods_onto_the_current_sites_localization()
    {
        Site::setConfig(['default' => 'en', 'sites' => [
            'en' => ['url' => 'http://domain.com'],
            'fr' => ['url' => 'http://domain.fr'],
        ]]);

        $entry = (new Entry)
            ->addLocalization((new LocalizedEntry)->locale('en')->slug('test')->data(['foo' => 'bar', 'enOnly' => 'yup']))
            ->addLocalization((new LocalizedEntry)->locale('fr')->slug('le-test')->data(['foo' => 'le bar', 'frOnly' => 'yup']));

        Site::setCurrent('en');
        $this->assertEquals('test', $entry->slug());
        $this->assertEquals('bar', $entry->get('foo'));
        $this->assertTrue($entry->has('enOnly'));
        $this->assertFalse($entry->has('frOnly'));
        $this->assertTrue($entry->published());
        $this->assertEquals(['foo' => 'bar', 'enOnly' => 'yup'], $entry->data());

        Site::setCurrent('fr');
        $this->assertEquals('le-test', $entry->slug());
        $this->assertEquals('le bar', $entry->get('foo'));
        $this->assertFalse($entry->has('enOnly'));
        $this->assertTrue($entry->has('frOnly'));
        $this->assertTrue($entry->published());
        $this->assertEquals(['foo' => 'le bar', 'frOnly' => 'yup'], $entry->data());
    }

    /** @test */
    function it_gets_the_blueprint_from_the_current_sites_localization()
    {
        $blueprint = new Blueprint;
        BlueprintRepository::shouldReceive('find')->with('test')->andReturn($blueprint);
        $entry = (new Entry)
            ->collection(new Collection)
            ->addLocalization((new LocalizedEntry)->locale('en')->set('blueprint', 'test'));

        $this->assertEquals($blueprint, $entry->blueprint());
    }

    /** @test */
    function it_gets_the_blueprint_based_on_the_collection()
    {
        BlueprintRepository::shouldReceive('find')->with('test')->andReturn($blueprint = new Blueprint);
        BlueprintRepository::shouldReceive('find')->with('another')->andReturn(new Blueprint);
        $collection = (new Collection)->entryBlueprints(['test', 'another']);
        $entry = (new Entry)->collection($collection)->addLocalization((new LocalizedEntry)->locale('en'));

        $this->assertEquals($blueprint, $entry->blueprint());
    }

    /** @test */
    function it_deletes_through_the_api()
    {
        Event::fake();
        $entry = new Entry;
        API\Entry::shouldReceive('deleteLocalizable')->with($entry);

        $return = $entry->delete();

        $this->assertTrue($return);
    }
}
