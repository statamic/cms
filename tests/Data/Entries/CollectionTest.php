<?php

namespace Tests\Data\Entries;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Entries\Entry;
use Statamic\Entries\Collection;
use Statamic\Exceptions\CollectionNotFoundException;
use Statamic\Facades;
use Statamic\Facades\Antlers;
use Statamic\Facades\Site;
use Statamic\Fields\Blueprint;
use Statamic\Structures\CollectionStructure;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class CollectionTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_gets_and_sets_the_handle()
    {
        $collection = new Collection;
        $this->assertNull($collection->handle());

        $return = $collection->handle('foo');

        $this->assertEquals($collection, $return);
        $this->assertEquals('foo', $collection->handle());
    }

    /** @test */
    public function it_gets_and_sets_the_routes()
    {
        Site::setConfig(['sites' => [
            'en' => ['url' => 'http://domain.com/'],
            'fr' => ['url' => 'http://domain.com/fr/'],
            'de' => ['url' => 'http://domain.com/de/'],
        ]]);

        // A collection with no sites uses the default site.
        $collection = new Collection;
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $collection->routes());
        $this->assertEquals(['en' => null], $collection->routes()->all());

        $return = $collection->routes([
            'en' => 'blog/{slug}',
            'fr' => 'le-blog/{slug}',
            'de' => 'das-blog/{slug}',
        ]);

        $this->assertEquals($collection, $return);
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $collection->routes());

        // Only routes corresponding to the collection's sites will be returned.
        $this->assertEquals(['en' => 'blog/{slug}'], $collection->routes()->all());
        $this->assertEquals('blog/{slug}', $collection->route('en'));
        $this->assertNull($collection->route('fr'));
        $this->assertNull($collection->route('de'));
        $this->assertNull($collection->route('unknown'));

        $collection->sites(['en', 'fr']);

        $this->assertEquals([
            'en' => 'blog/{slug}',
            'fr' => 'le-blog/{slug}',
        ], $collection->routes()->all());
        $this->assertEquals('blog/{slug}', $collection->route('en'));
        $this->assertEquals('le-blog/{slug}', $collection->route('fr'));
        $this->assertNull($collection->route('de'));
        $this->assertNull($collection->route('unknown'));
    }

    /** @test */
    public function it_sets_all_the_routes_identically()
    {
        Site::setConfig(['sites' => [
            'en' => ['url' => 'http://domain.com/'],
            'fr' => ['url' => 'http://domain.com/fr/'],
            'de' => ['url' => 'http://domain.com/de/'],
        ]]);

        $collection = (new Collection)->sites(['en', 'fr']);

        $return = $collection->routes('{slug}');

        $this->assertEquals($collection, $return);
        $this->assertEquals([
            'en' => '{slug}',
            'fr' => '{slug}',
        ], $collection->routes()->all());
        $this->assertEquals('{slug}', $collection->route('en'));
        $this->assertEquals('{slug}', $collection->route('fr'));
        $this->assertNull($collection->route('de'));
        $this->assertNull($collection->route('unknown'));
    }

    /** @test */
    public function it_gets_and_sets_the_template()
    {
        $collection = new Collection;
        $this->assertEquals('default', $collection->template());

        $return = $collection->template('foo');

        $this->assertEquals($collection, $return);
        $this->assertEquals('foo', $collection->template());
    }

    /** @test */
    public function it_gets_and_sets_the_layout()
    {
        $collection = new Collection;
        $this->assertEquals('layout', $collection->layout());

        $return = $collection->layout('foo');

        $this->assertEquals($collection, $return);
        $this->assertEquals('foo', $collection->layout());
    }

    /** @test */
    public function it_gets_and_sets_the_title()
    {
        $collection = (new Collection)->handle('blog');
        $this->assertEquals('Blog', $collection->title());

        $return = $collection->title('The Blog');

        $this->assertEquals($collection, $return);
        $this->assertEquals('The Blog', $collection->title());
    }

    /** @test */
    public function it_gets_and_sets_the_sites_it_can_be_used_in_when_using_multiple_sites()
    {
        Site::setConfig(['sites' => [
            'en' => ['url' => 'http://domain.com/'],
            'fr' => ['url' => 'http://domain.com/fr/'],
        ]]);

        $collection = new Collection;

        $sites = $collection->sites();
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $sites);
        $this->assertEquals(['en'], $sites->all()); // collection with no sites will resolve to the default site.

        $return = $collection->sites(['en', 'fr']);

        $this->assertEquals($collection, $return);
        $this->assertEquals(['en', 'fr'], $collection->sites()->all());
    }

    /** @test */
    public function it_gets_the_default_site_when_in_single_site_mode()
    {
        $collection = new Collection;

        $sites = $collection->sites();
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $sites);
        $this->assertEquals(['en'], $sites->all());

        $return = $collection->sites(['en', 'fr']); // has no effect

        $this->assertEquals($collection, $return);
        $this->assertEquals(['en'], $collection->sites()->all());
    }

    /** @test */
    public function it_stores_cascading_data_in_a_collection()
    {
        $collection = new Collection;
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $collection->cascade());
        $this->assertTrue($collection->cascade()->isEmpty());

        $collection->cascade()->put('foo', 'bar');

        $this->assertTrue($collection->cascade()->has('foo'));
        $this->assertEquals('bar', $collection->cascade()->get('foo'));
    }

    /** @test */
    public function it_sets_all_the_cascade_data_when_passing_an_array()
    {
        $collection = new Collection;

        $return = $collection->cascade($arr = ['foo' => 'bar', 'baz' => 'qux']);
        $this->assertEquals($collection, $return);
        $this->assertEquals($arr, $collection->cascade()->all());

        // test that passing an empty array is not treated as passing null
        $return = $collection->cascade([]);
        $this->assertEquals($collection, $return);
        $this->assertEquals([], $collection->cascade()->all());
    }

    /** @test */
    public function it_gets_values_from_the_cascade_with_fallbacks()
    {
        $collection = new Collection;
        $collection->cascade(['foo' => 'bar']);

        $this->assertEquals('bar', $collection->cascade('foo'));
        $this->assertNull($collection->cascade('baz'));
        $this->assertEquals('qux', $collection->cascade('baz', 'qux'));
    }

    /** @test */
    public function it_gets_entry_blueprints()
    {
        $collection = (new Collection)->handle('blog');

        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'one' => $blueprintOne = (new Blueprint)->setHandle('one'),
            'two' => $blueprintTwo = (new Blueprint)->setHandle('two'),
        ]));

        $blueprints = $collection->entryBlueprints();
        $this->assertCount(2, $blueprints);
        $this->assertEveryItemIsInstanceOf(Blueprint::class, $blueprints);
        $this->assertEquals([$blueprintOne, $blueprintTwo], $blueprints->all());

        $this->assertEquals($blueprintOne, $collection->entryBlueprint());
        $this->assertEquals($blueprintOne, $collection->entryBlueprint('one'));
        $this->assertEquals($blueprintTwo, $collection->entryBlueprint('two'));
        $this->assertNull($collection->entryBlueprint('three'));
    }

    /** @test */
    public function no_existing_blueprints_will_fall_back_to_a_default_named_after_the_collection()
    {
        $collection = (new Collection)->handle('blog');

        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect());
        BlueprintRepository::shouldReceive('find')->with('default')->andReturn(
            $blueprint = (new Blueprint)
                ->setHandle('thisll_change')
                ->setContents(['title' => 'This will change'])
        );

        $blueprints = $collection->entryBlueprints();
        $this->assertCount(1, $blueprints);
        $this->assertEquals([$blueprint], $blueprints->all());

        tap($collection->entryBlueprint(), function ($default) use ($blueprint) {
            $this->assertEquals($blueprint, $default);
            $this->assertEquals('blog', $default->handle());
            $this->assertEquals('Blog', $default->title());
        });

        $this->assertEquals($blueprint, $collection->entryBlueprint('blog'));
        $this->assertNull($collection->entryBlueprint('two'));
    }

    /** @test */
    public function it_gets_sort_field_and_direction()
    {
        $alpha = new Collection;
        $this->assertEquals('title', $alpha->sortField());
        $this->assertEquals('asc', $alpha->sortDirection());

        $dated = (new Collection)->dated(true);
        $this->assertEquals('date', $dated->sortField());
        $this->assertEquals('desc', $dated->sortDirection());

        $structureWithMaxDepthOfOne = $this->makeStructure()->maxDepth(1);
        $ordered = (new Collection)->structure($structureWithMaxDepthOfOne);
        $this->assertEquals('order', $ordered->sortField());
        $this->assertEquals('asc', $ordered->sortDirection());

        $datedAndOrdered = (new Collection)->dated(true)->structure($structureWithMaxDepthOfOne);
        $this->assertEquals('order', $datedAndOrdered->sortField());
        $this->assertEquals('asc', $datedAndOrdered->sortDirection());

        $structure = $this->makeStructure();
        $alpha->structure($structure);
        $this->assertEquals('title', $alpha->sortField());
        $this->assertEquals('asc', $alpha->sortDirection());
        $dated->structure($structure);
        $this->assertEquals('date', $dated->sortField());
        $this->assertEquals('desc', $dated->sortDirection());

        // TODO: Ability to control sort direction
    }

    /** @test */
    public function it_saves_the_collection_through_the_api()
    {
        $collection = (new Collection)->handle('test');

        Facades\Collection::shouldReceive('save')->with($collection)->once();
        Facades\Blink::shouldReceive('forget')->with('collection-handles')->once();
        Facades\Blink::shouldReceive('flushStartingWith')->with('collection-test')->once();

        $return = $collection->save();

        $this->assertEquals($collection, $return);
    }

    /** @test */
    public function it_sets_future_date_behavior()
    {
        $collection = (new Collection)->handle('test');
        $this->assertEquals('public', $collection->futureDateBehavior());

        $return = $collection->futureDateBehavior('private');
        $this->assertEquals($collection, $return);
        $this->assertEquals('private', $collection->futureDateBehavior());

        $return = $collection->futureDateBehavior(null);
        $this->assertEquals($collection, $return);
        $this->assertEquals('public', $collection->futureDateBehavior());
    }

    /** @test */
    public function it_sets_past_date_behavior()
    {
        $collection = (new Collection)->handle('test');
        $this->assertEquals('public', $collection->pastDateBehavior());

        $return = $collection->pastDateBehavior('private');
        $this->assertEquals($collection, $return);
        $this->assertEquals('private', $collection->pastDateBehavior());

        $return = $collection->pastDateBehavior(null);
        $this->assertEquals($collection, $return);
        $this->assertEquals('public', $collection->pastDateBehavior());
    }

    /** @test */
    public function it_gets_and_sets_the_default_publish_state()
    {
        $collection = (new Collection)->handle('test');
        $this->assertTrue($collection->defaultPublishState());

        $return = $collection->defaultPublishState(true);
        $this->assertEquals($collection, $return);
        $this->assertTrue($collection->defaultPublishState());

        $return = $collection->defaultPublishState(false);
        $this->assertEquals($collection, $return);
        $this->assertFalse($collection->defaultPublishState());
    }

    /** @test */
    public function default_publish_state_is_always_false_when_using_revisions()
    {
        config(['statamic.revisions.enabled' => true]);

        $collection = (new Collection)->handle('test');
        $this->assertTrue($collection->defaultPublishState());

        $collection->revisionsEnabled(true);
        $this->assertFalse($collection->defaultPublishState());

        $collection->defaultPublishState(true);
        $this->assertFalse($collection->defaultPublishState());

        $collection->defaultPublishState(false);
        $this->assertFalse($collection->defaultPublishState());
    }

    /** @test */
    public function it_sets_and_gets_structure()
    {
        $structure = new CollectionStructure;
        $collection = (new Collection)->handle('test');
        $this->assertFalse($collection->hasStructure());
        $this->assertNull($collection->structure());
        $this->assertNull($structure->handle());

        $collection->structure($structure);

        $this->assertTrue($collection->hasStructure());
        $this->assertSame($structure, $collection->structure());
        $this->assertEquals('collection::test', $structure->handle());
        $this->assertEquals('Test', $structure->title());
    }

    /** @test */
    public function it_sets_the_structure_inline()
    {
        // This applies to a file-based approach.

        $collection = (new Collection)->handle('test');
        $this->assertFalse($collection->hasStructure());
        $this->assertNull($collection->structure());

        EntryFactory::id('123')->collection('test')->create();
        EntryFactory::id('456')->collection('test')->create();
        EntryFactory::id('789')->collection('test')->create();

        $return = $collection->structureContents($contents = [
            'max_depth' => 2,
            'tree' => [
                ['entry' => '123', 'children' => [
                    ['entry' => '789'],
                ]],
                ['entry' => '456'],
            ],
        ]);

        $this->assertEquals($collection, $return);
        $this->assertEquals($contents, $collection->structureContents());
        $this->assertTrue($collection->hasStructure());
        $structure = $collection->structure();
        $this->assertInstanceOf(CollectionStructure::class, $structure);
        $this->assertEquals('collection::test', $structure->handle());
        $this->assertSame($collection, $structure->collection());
        $this->assertEquals(2, $structure->in('en')->pages()->all()->count());
        $this->assertEquals(3, $structure->in('en')->flattenedPages()->count());
        $this->assertEquals(2, $structure->maxDepth());
    }

    /** @test */
    public function setting_a_structure_removes_the_existing_inline_structure()
    {
        $collection = (new Collection)->handle('test');
        $collection->structureContents($contents = ['tree' => []]);
        $this->assertSame($contents, $collection->structureContents());

        $collection->structure(new CollectionStructure);

        $this->assertNull($collection->structureContents());
    }

    /** @test */
    public function setting_an_inline_structure_removes_the_existing_structure()
    {
        $collection = (new Collection)->handle('test');
        $collection->structure($structure = (new CollectionStructure)->maxDepth(2));
        $this->assertSame($structure, $collection->structure());
        $this->assertEquals(2, $collection->structure()->maxDepth());
        $this->assertNull($collection->structureContents());

        $collection->structureContents(['max_depth' => 13, 'tree' => []]);

        $this->assertNotSame($structure, $collection->structure());
        $this->assertEquals(13, $collection->structure()->maxDepth());
    }

    /** @test */
    public function it_gets_the_handle_when_casting_to_a_string()
    {
        $collection = (new Collection)->handle('test');

        $this->assertEquals('test', (string) $collection);
    }

    /** @test */
    public function it_augments()
    {
        $collection = (new Collection)->handle('test');

        $this->assertInstanceof(Augmentable::class, $collection);
        $this->assertEquals([
            'title' => 'Test',
            'handle' => 'test',
        ], $collection->toAugmentedArray());
    }

    /** @test */
    public function it_augments_in_the_parser()
    {
        $collection = (new Collection)->handle('test');

        $this->assertEquals('test', Antlers::parse('{{ collection }}', ['collection' => $collection]));

        $this->assertEquals('test Test', Antlers::parse('{{ collection }}{{ handle }} {{ title }}{{ /collection }}', ['collection' => $collection]));

        $this->assertEquals('test', Antlers::parse('{{ collection:handle }}', ['collection' => $collection]));

        try {
            Antlers::parse('{{ collection from="somewhere" }}{{ title }}{{ /collection }}', ['collection' => $collection]);
            $this->fail('Exception not thrown');
        } catch (CollectionNotFoundException $e) {
            $this->assertEquals('Collection [somewhere] not found', $e->getMessage());
        }
    }

    /** @test */
    public function it_gets_the_uri_and_url_from_the_mounted_entry()
    {
        $mount = $this->mock(Entry::class);
        $frenchMount = $this->mock(Entry::class);
        $mount->shouldReceive('in')->with('en')->andReturnSelf();
        $mount->shouldReceive('in')->with('fr')->andReturn($frenchMount);
        $mount->shouldReceive('uri')->andReturn('/blog');
        $mount->shouldReceive('url')->andReturn('/en/blog');
        $frenchMount->shouldReceive('uri')->andReturn('/le-blog');
        $frenchMount->shouldReceive('url')->andReturn('/fr/le-blog');

        Facades\Entry::shouldReceive('find')->with('mounted')->andReturn($mount);

        $collection = (new Collection)->handle('test');

        $this->assertNull($collection->uri());
        $this->assertNull($collection->url());
        $this->assertNull($collection->uri('en'));
        $this->assertNull($collection->url('en'));
        $this->assertNull($collection->uri('fr'));
        $this->assertNull($collection->url('fr'));

        $collection->mount('mounted');

        $this->assertEquals('/blog', $collection->uri());
        $this->assertEquals('/en/blog', $collection->url());
        $this->assertEquals('/blog', $collection->uri('en'));
        $this->assertEquals('/en/blog', $collection->url('en'));
        $this->assertEquals('/le-blog', $collection->uri('fr'));
        $this->assertEquals('/fr/le-blog', $collection->url('fr'));
    }

    /** @test */
    public function it_updates_entry_uris_through_the_repository()
    {
        $collection = (new Collection)->handle('test');

        Facades\Collection::shouldReceive('updateEntryUris')->with($collection, null)->once()->ordered();
        Facades\Collection::shouldReceive('updateEntryUris')->with($collection, ['one', 'two'])->once()->ordered();

        $collection->updateEntryUris();
        $collection->updateEntryUris(['one', 'two']);
    }

    private function makeStructure()
    {
        return (new CollectionStructure)->tap(function ($s) {
            $s->addTree($s->makeTree('en'));
        });
    }
}
