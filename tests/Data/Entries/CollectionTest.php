<?php

namespace Tests\Data\Entries;

use Facades\Statamic\Fields\BlueprintRepository;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Entries\Entry;
use Statamic\Entries\Collection;
use Statamic\Events\CollectionCreated;
use Statamic\Events\CollectionCreating;
use Statamic\Events\CollectionDeleted;
use Statamic\Events\CollectionDeleting;
use Statamic\Events\CollectionSaved;
use Statamic\Events\CollectionSaving;
use Statamic\Events\EntryBlueprintFound;
use Statamic\Exceptions\CollectionNotFoundException;
use Statamic\Facades;
use Statamic\Facades\Antlers;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Fields\Blueprint;
use Statamic\Structures\CollectionStructure;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class CollectionTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_gets_and_sets_the_handle()
    {
        $collection = new Collection;
        $this->assertNull($collection->handle());

        $return = $collection->handle('foo');

        $this->assertEquals($collection, $return);
        $this->assertEquals('foo', $collection->handle());
    }

    #[Test]
    public function it_gets_and_sets_the_routes()
    {
        $this->setSites([
            'en' => ['url' => 'http://domain.com/'],
            'fr' => ['url' => 'http://domain.com/fr/'],
            'de' => ['url' => 'http://domain.com/de/'],
        ]);

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

    #[Test]
    public function it_sets_all_the_routes_identically()
    {
        $this->setSites([
            'en' => ['url' => 'http://domain.com/'],
            'fr' => ['url' => 'http://domain.com/fr/'],
            'de' => ['url' => 'http://domain.com/de/'],
        ]);

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

    #[Test]
    public function it_gets_and_sets_the_title_formats()
    {
        $this->setSites([
            'en' => ['url' => 'http://domain.com/'],
            'fr' => ['url' => 'http://domain.com/fr/'],
            'de' => ['url' => 'http://domain.com/de/'],
        ]);

        // A collection with no sites uses the default site.
        $collection = new Collection;
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $collection->titleFormats());
        $this->assertEquals(['en' => null], $collection->titleFormats()->all());
        $this->assertFalse($collection->autoGeneratesTitles());

        $collection->titleFormats(null);
        $this->assertFalse($collection->autoGeneratesTitles());

        $return = $collection->titleFormats([
            'en' => 'Quote by {author}',
            'fr' => 'Citation de {author}',
            'de' => 'Zitat vom {author}',
        ]);

        $this->assertEquals($collection, $return);
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $collection->titleFormats());
        $this->assertTrue($collection->autoGeneratesTitles());

        // Only titleFormats corresponding to the collection's sites will be returned.
        $this->assertEquals(['en' => 'Quote by {author}'], $collection->titleFormats()->all());
        $this->assertEquals('Quote by {author}', $collection->titleFormat('en'));
        $this->assertNull($collection->titleFormat('fr'));
        $this->assertNull($collection->titleFormat('de'));
        $this->assertNull($collection->titleFormat('unknown'));

        $collection->sites(['en', 'fr']);

        $this->assertEquals([
            'en' => 'Quote by {author}',
            'fr' => 'Citation de {author}',
        ], $collection->titleFormats()->all());
        $this->assertEquals('Quote by {author}', $collection->titleFormat('en'));
        $this->assertEquals('Citation de {author}', $collection->titleFormat('fr'));
        $this->assertNull($collection->titleFormat('de'));
        $this->assertNull($collection->titleFormat('unknown'));
    }

    #[Test]
    public function it_sets_all_the_title_formats_identically()
    {
        $this->setSites([
            'en' => ['url' => 'http://domain.com/'],
            'fr' => ['url' => 'http://domain.com/fr/'],
            'de' => ['url' => 'http://domain.com/de/'],
        ]);

        $collection = (new Collection)->sites(['en', 'fr']);

        $return = $collection->titleFormats('Quote by {author}');

        $this->assertEquals($collection, $return);
        $this->assertEquals([
            'en' => 'Quote by {author}',
            'fr' => 'Quote by {author}',
        ], $collection->titleFormats()->all());
        $this->assertEquals('Quote by {author}', $collection->titleFormat('en'));
        $this->assertEquals('Quote by {author}', $collection->titleFormat('fr'));
        $this->assertNull($collection->titleFormat('de'));
        $this->assertNull($collection->titleFormat('unknown'));
    }

    #[Test]
    public function it_gets_and_sets_the_template()
    {
        $collection = new Collection;
        $this->assertEquals('default', $collection->template());

        $return = $collection->template('foo');

        $this->assertEquals($collection, $return);
        $this->assertEquals('foo', $collection->template());
    }

    #[Test]
    public function it_gets_and_sets_the_layout()
    {
        $collection = new Collection;
        $this->assertEquals('layout', $collection->layout());

        $return = $collection->layout('foo');

        $this->assertEquals($collection, $return);
        $this->assertEquals('foo', $collection->layout());
    }

    #[Test]
    public function it_gets_and_sets_the_create_label()
    {
        $collection = new Collection;
        $this->assertEquals('Create Entry', $collection->createLabel());
    }

    #[Test]
    public function it_gets_and_sets_the_title()
    {
        $collection = (new Collection)->handle('blog');
        $this->assertEquals('Blog', $collection->title());

        $return = $collection->title('The Blog');

        $this->assertEquals($collection, $return);
        $this->assertEquals('The Blog', $collection->title());
    }

    #[Test]
    public function it_gets_and_sets_the_sites_it_can_be_used_in_when_using_multiple_sites()
    {
        $this->setSites([
            'en' => ['url' => 'http://domain.com/'],
            'fr' => ['url' => 'http://domain.com/fr/'],
        ]);

        $collection = new Collection;

        $sites = $collection->sites();
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $sites);
        $this->assertEquals(['en'], $sites->all()); // collection with no sites will resolve to the default site.

        $return = $collection->sites(['en', 'fr']);

        $this->assertEquals($collection, $return);
        $this->assertEquals(['en', 'fr'], $collection->sites()->all());
    }

    #[Test]
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

    #[Test]
    public function it_gets_and_sets_propagation_setting()
    {
        $collection = new Collection;

        $this->assertFalse($collection->propagate());

        $return = $collection->propagate(true);

        $this->assertEquals($collection, $return);
        $this->assertTrue($collection->propagate());
    }

    #[Test]
    public function it_stores_cascading_data_in_a_collection()
    {
        $collection = new Collection;
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $collection->cascade());
        $this->assertTrue($collection->cascade()->isEmpty());

        $collection->cascade()->put('foo', 'bar');

        $this->assertTrue($collection->cascade()->has('foo'));
        $this->assertEquals('bar', $collection->cascade()->get('foo'));
    }

    #[Test]
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

    #[Test]
    public function it_gets_values_from_the_cascade_with_fallbacks()
    {
        $collection = new Collection;
        $collection->cascade(['foo' => 'bar']);

        $this->assertEquals('bar', $collection->cascade('foo'));
        $this->assertNull($collection->cascade('baz'));
        $this->assertEquals('qux', $collection->cascade('baz', 'qux'));
    }

    #[Test]
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

        $this->assertTrue($collection->hasVisibleEntryBlueprint());
    }

    #[Test]
    public function it_gets_first_non_hidden_entry_blueprint()
    {
        $collection = (new Collection)->handle('blog');

        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'apple' => $blueprintOne = (new Blueprint)->setHandle('apple')->setHidden(true),
            'berry' => $blueprintTwo = (new Blueprint)->setHandle('berry'),
            'cherry' => $blueprintThree = (new Blueprint)->setHandle('cherry')->setHidden(true),
        ]));

        $blueprints = $collection->entryBlueprints();

        $this->assertCount(3, $blueprints);

        // Assert that it ignores hidden blueprints by default.
        $this->assertEquals($blueprintTwo, $collection->entryBlueprint());

        // But assert that it can still get a specific blueprint for editing the blueprint, etc.
        $this->assertEquals($blueprintThree, $collection->entryBlueprint('cherry'));

        $this->assertTrue($collection->hasVisibleEntryBlueprint());
    }

    #[Test]
    public function it_gets_first_entry_blueprint_when_they_are_all_hidden()
    {
        $collection = (new Collection)->handle('blog');

        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'apple' => $blueprintOne = (new Blueprint)->setHandle('apple')->setHidden(true),
            'berry' => $blueprintTwo = (new Blueprint)->setHandle('berry')->setHidden(true),
            'cherry' => $blueprintThree = (new Blueprint)->setHandle('cherry')->setHidden(true),
        ]));

        $blueprints = $collection->entryBlueprints();

        $this->assertCount(3, $blueprints);
        $this->assertEquals($blueprintOne, $collection->entryBlueprint());
        $this->assertEquals($blueprintThree, $collection->entryBlueprint('cherry'));
        $this->assertFalse($collection->hasVisibleEntryBlueprint());
    }

    #[Test]
    public function no_existing_blueprints_will_fall_back_to_a_default_named_after_the_singular_collection()
    {
        $collection = (new Collection)->handle('articles');

        BlueprintRepository::shouldReceive('in')->with('collections/articles')->andReturn(collect());
        BlueprintRepository::shouldReceive('find')->with('default')->andReturn(
            $default = (new Blueprint)
                ->setInitialPath('this/wont/change')
                ->setHandle('thisll_change')
                ->setNamespace('this.will.change')
                ->setContents(['title' => 'This will change'])
        );
        BlueprintRepository::shouldReceive('getAdditionalNamespaces')->andReturn(collect());

        $blueprint = $collection->entryBlueprint();
        $this->assertNotEquals($default, $blueprint);

        $blueprints = $collection->entryBlueprints();
        $this->assertCount(1, $blueprints);
        $this->assertEquals($blueprint, $blueprints->get(0)->setParent($collection));

        $this->assertEquals('this/wont/change', $blueprint->initialPath());
        $this->assertEquals('article', $blueprint->handle());
        $this->assertEquals('collections.articles', $blueprint->namespace());
        $this->assertEquals('Article', $blueprint->title());

        $this->assertEquals($blueprint, $collection->entryBlueprint('article'));
        $this->assertNull($collection->entryBlueprint('two'));
    }

    #[Test]
    public function it_dispatches_an_event_when_getting_entry_blueprint()
    {
        Event::fake();

        $collection = (new Collection)->handle('blog');

        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'blueprint' => $blueprint = (new Blueprint)->setHandle('blueprint'),
        ]));

        // Do it twice so we can check the event is only dispatched once.
        $collection->entryBlueprint();
        $collection->entryBlueprint();

        Event::assertDispatchedTimes(EntryBlueprintFound::class, 1);
        Event::assertDispatched(EntryBlueprintFound::class, function ($event) use ($blueprint) {
            return $event->blueprint === $blueprint
                && $event->entry === null;
        });
    }

    #[Test]
    public function it_gets_sort_field_and_direction()
    {
        $alpha = new Collection;
        $this->assertEquals('title', $alpha->sortField());
        $this->assertEquals('asc', $alpha->sortDirection());
        $this->assertNull($alpha->customSortField());
        $this->assertNull($alpha->customSortDirection());

        $dated = (new Collection)->dated(true);
        $this->assertEquals('date', $dated->sortField());
        $this->assertEquals('desc', $dated->sortDirection());
        $this->assertNull($dated->customSortField());
        $this->assertNull($dated->customSortDirection());

        $ordered = (new Collection)->structureContents(['max_depth' => 1]);
        $this->assertEquals('order', $ordered->sortField());
        $this->assertEquals('asc', $ordered->sortDirection());
        $this->assertNull($ordered->customSortField());
        $this->assertNull($ordered->customSortDirection());

        $datedAndOrdered = (new Collection)->dated(true)->structureContents(['max_depth' => 1]);
        $this->assertEquals('order', $datedAndOrdered->sortField());
        $this->assertEquals('asc', $datedAndOrdered->sortDirection());
        $this->assertNull($datedAndOrdered->customSortField());
        $this->assertNull($datedAndOrdered->customSortDirection());

        $alpha->structureContents(['max_depth' => 99]);
        $this->assertEquals('order', $alpha->sortField());
        $this->assertEquals('asc', $alpha->sortDirection());
        $dated->structureContents(['max_depth' => 99]);
        $this->assertEquals('order', $dated->sortField());
        $this->assertEquals('desc', $dated->sortDirection());

        // Custom sort field and direction should override any other logic.
        foreach ([$alpha, $dated, $ordered, $datedAndOrdered] as $collection) {
            $collection->sortField('foo');
            $this->assertEquals('foo', $collection->sortField());
            $this->assertEquals('asc', $collection->sortDirection());
            $this->assertEquals('foo', $collection->customSortField());
            $this->assertNull($collection->customSortDirection());
            $collection->sortDirection('desc');
            $this->assertEquals('desc', $collection->sortDirection());
            $this->assertEquals('desc', $collection->customSortDirection());
        }
    }

    #[Test]
    public function setting_custom_sort_field_will_set_the_sort_direction_to_asc_when_not_explicitly_set()
    {
        // Use a date collection to test this because its default sort direction is desc.

        $dated = (new Collection)->dated(true);
        $this->assertEquals('date', $dated->sortField());
        $this->assertEquals('desc', $dated->sortDirection());
        $this->assertNull($dated->customSortField());

        $dated->sortField('foo');
        $this->assertEquals('foo', $dated->sortField());
        $this->assertEquals('asc', $dated->sortDirection());
        $this->assertEquals('foo', $dated->customSortField());
    }

    #[Test]
    public function it_saves_the_collection_through_the_api()
    {
        $collection = (new Collection)->handle('test');

        Facades\Collection::shouldReceive('save')->with($collection)->once();
        Facades\Collection::shouldReceive('handleExists')->with('test')->once();
        Facades\Blink::shouldReceive('forget')->with('collection-handles')->once();
        Facades\Blink::shouldReceive('forget')->with('mounted-collections')->once();
        Facades\Blink::shouldReceive('flushStartingWith')->with('collection-test')->once();
        Facades\Blink::shouldReceive('once')->with('collection-test-structure', \Mockery::any())->andReturnNull();

        $return = $collection->save();

        $this->assertEquals($collection, $return);
    }

    #[Test]
    public function it_saves_quietly()
    {
        Event::fake();

        $collection = (new Collection)->handle('test');
        $collection->saveQuietly();

        Event::assertNotDispatched(CollectionSaved::class);
        Event::assertNotDispatched(CollectionSaving::class);
    }

    #[Test]
    public function it_dispatches_collection_saved()
    {
        Event::fake();

        $collection = (new Collection)->handle('test');
        $collection->save();

        Event::assertDispatched(CollectionSaved::class, function ($event) use ($collection) {
            return $event->collection === $collection;
        });
    }

    #[Test]
    public function it_dispatches_collection_saving()
    {
        Event::fake();

        $collection = (new Collection)->handle('test');
        $collection->save();

        Event::assertDispatched(CollectionSaving::class, function ($event) use ($collection) {
            return $event->collection === $collection;
        });
    }

    #[Test]
    public function it_dispatches_collection_creating()
    {
        Event::fake();

        $collection = (new Collection)->handle('test');
        $collection->save();

        Event::assertDispatched(CollectionCreating::class, function ($event) use ($collection) {
            return $event->collection === $collection;
        });
    }

    #[Test]
    public function it_dispatches_collection_created_only_once()
    {
        Event::fake();

        $collection = (new Collection)->handle('test');
        $collection->save();
        $collection->save();

        Event::assertDispatched(CollectionCreated::class, function ($event) use ($collection) {
            return $event->collection === $collection;
        });
        Event::assertDispatched(CollectionSaved::class, 2);
        Event::assertDispatched(CollectionCreated::class, 1);
    }

    #[Test]
    public function if_creating_event_returns_false_the_collection_doesnt_save()
    {
        Event::fake([CollectionCreated::class]);
        Facades\Collection::spy();

        Event::listen(CollectionCreating::class, function () {
            return false;
        });

        $container = (new Collection)->handle('test');
        $return = $container->save();

        $this->assertFalse($return);

        Event::assertNotDispatched(CollectionCreated::class);
    }

    #[Test]
    public function if_saving_event_returns_false_the_collection_doesnt_save()
    {
        Event::fake([CollectionSaved::class]);
        Facades\Collection::spy();

        Event::listen(CollectionSaving::class, function () {
            return false;
        });

        $container = (new Collection)->handle('test');
        $return = $container->save();

        $this->assertFalse($return);

        Event::assertNotDispatched(CollectionSaved::class);
    }

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
    public function it_gets_and_sets_the_origin_behavior()
    {
        $collection = (new Collection)->handle('test');
        $this->assertEquals('select', $collection->originBehavior());

        $return = $collection->originBehavior('active');
        $this->assertEquals($collection, $return);
        $this->assertEquals('active', $collection->originBehavior());

        $return = $collection->originBehavior('root');
        $this->assertEquals($collection, $return);
        $this->assertEquals('root', $collection->originBehavior());

        $return = $collection->originBehavior(null);
        $this->assertEquals($collection, $return);
        $this->assertEquals('select', $collection->originBehavior());
    }

    #[Test]
    public function it_throw_exception_when_setting_invalid_origin_behavior()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid origin behavior [nope]. Must be "select", "root", or "active".');

        $collection = (new Collection)->handle('test');
        $collection->originBehavior('nope');
    }

    #[Test]
    public function it_sets_and_gets_structure()
    {
        $structure = new CollectionStructure;
        $collection = (new Collection)->handle('test');
        Facades\Collection::shouldReceive('findByHandle')->with('test')->andReturn($collection);

        $this->assertFalse($collection->hasStructure());
        $this->assertNull($collection->structure());
        $this->assertNull($structure->handle());

        $collection->structure($structure);

        $this->assertTrue($collection->hasStructure());
        $this->assertSame($structure, $collection->structure());
        $this->assertEquals('test', $structure->handle());
        $this->assertEquals('Test', $structure->title());
    }

    #[Test]
    public function setting_a_structure_overrides_the_existing_inline_structure()
    {
        $collection = (new Collection)->handle('test');
        $collection->structureContents($contents = ['root' => true, 'max_depth' => 3]);
        $this->assertSame($contents, $collection->structureContents());

        $collection->structure($structure = (new CollectionStructure)->expectsRoot(false)->maxDepth(13));

        $this->assertEquals(['root' => false, 'max_depth' => 13], $collection->structureContents());
        $this->assertSame($structure, $collection->structure());
    }

    #[Test]
    public function setting_an_inline_structure_removes_the_existing_structure()
    {
        $collection = (new Collection)->handle('test');
        $collection->structure($structure = (new CollectionStructure)->maxDepth(2));
        $this->assertSame($structure, $collection->structure());
        $this->assertEquals(2, $collection->structure()->maxDepth());
        $this->assertEquals(['root' => false, 'max_depth' => 2], $collection->structureContents());

        $collection->structureContents(['max_depth' => 13, 'tree' => []]);

        $this->assertNotSame($structure, $collection->structure());
        $this->assertEquals(13, $collection->structure()->maxDepth());
    }

    #[Test]
    public function it_gets_the_handle_when_casting_to_a_string()
    {
        $collection = (new Collection)->handle('test');

        $this->assertEquals('test', (string) $collection);
    }

    #[Test]
    public function it_augments()
    {
        $collection = (new Collection)->handle('test');

        $this->assertInstanceof(Augmentable::class, $collection);
        $this->assertEquals([
            'title' => 'Test',
            'handle' => 'test',
        ], $collection->toAugmentedArray());
    }

    #[Test]
    public function it_gets_evaluated_augmented_value_using_magic_property()
    {
        $collection = (new Collection)->handle('test');

        $collection
            ->toAugmentedCollection()
            ->each(fn ($value, $key) => $this->assertEquals($value->value(), $collection->{$key}))
            ->each(fn ($value, $key) => $this->assertEquals($value->value(), $collection[$key]));
    }

    #[Test]
    public function it_is_arrayable()
    {
        $collection = (new Collection)->handle('tags');

        $this->assertInstanceOf(Arrayable::class, $collection);

        $expectedAugmented = $collection->toAugmentedCollection();

        $array = $collection->toArray();

        $this->assertCount($expectedAugmented->count(), $array);

        collect($array)
            ->each(function ($value, $key) use ($collection) {
                $expected = $collection->{$key};
                $expected = $expected instanceof Arrayable ? $expected->toArray() : $expected;
                $this->assertEquals($expected, $value);
            });
    }

    #[Test]
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

    #[Test]
    public function it_gets_the_uri_and_url_from_the_mounted_entry()
    {
        $mount = $this->mock(Entry::class);
        $frenchMount = $this->mock(Entry::class);
        $mount->shouldReceive('in')->with('en')->andReturnSelf();
        $mount->shouldReceive('in')->with('fr')->andReturn($frenchMount);
        $mount->shouldReceive('uri')->andReturn('/blog');
        $mount->shouldReceive('url')->andReturn('/en/blog');
        $mount->shouldReceive('absoluteUrl')->andReturn('http://site1.com/en/blog');
        $frenchMount->shouldReceive('uri')->andReturn('/le-blog');
        $frenchMount->shouldReceive('url')->andReturn('/fr/le-blog');
        $frenchMount->shouldReceive('absoluteUrl')->andReturn('http://site2.com/fr/le-blog');

        Facades\Entry::shouldReceive('find')->with('mounted')->andReturn($mount);

        $collection = (new Collection)->handle('test');

        $this->assertNull($collection->uri());
        $this->assertNull($collection->url());
        $this->assertNull($collection->absoluteUrl());
        $this->assertNull($collection->uri('en'));
        $this->assertNull($collection->url('en'));
        $this->assertNull($collection->absoluteUrl('en'));
        $this->assertNull($collection->uri('fr'));
        $this->assertNull($collection->url('fr'));
        $this->assertNull($collection->absoluteUrl('fr'));

        $collection->mount('mounted');

        $this->assertEquals('/blog', $collection->uri());
        $this->assertEquals('/en/blog', $collection->url());
        $this->assertEquals('http://site1.com/en/blog', $collection->absoluteUrl());
        $this->assertEquals('/blog', $collection->uri('en'));
        $this->assertEquals('/en/blog', $collection->url('en'));
        $this->assertEquals('http://site1.com/en/blog', $collection->absoluteUrl('en'));
        $this->assertEquals('/le-blog', $collection->uri('fr'));
        $this->assertEquals('/fr/le-blog', $collection->url('fr'));
        $this->assertEquals('http://site2.com/fr/le-blog', $collection->absoluteUrl('fr'));
    }

    #[Test]
    public function it_updates_entry_uris_through_the_entry_repository()
    {
        $collection = (new Collection)->handle('test');

        Facades\Entry::shouldReceive('updateUris')->with($collection, null)->once()->ordered();
        Facades\Entry::shouldReceive('updateUris')->with($collection, ['one', 'two'])->once()->ordered();

        $collection->updateEntryUris();
        $collection->updateEntryUris(['one', 'two']);
    }

    #[Test]
    public function it_updates_entry_orders_through_the_entry_repository()
    {
        $collection = (new Collection)->handle('test');

        Facades\Entry::shouldReceive('updateOrders')->with($collection, null)->once()->ordered();
        Facades\Entry::shouldReceive('updateOrders')->with($collection, ['one', 'two'])->once()->ordered();

        $collection->updateEntryOrder();
        $collection->updateEntryOrder(['one', 'two']);
    }

    #[Test]
    public function it_updates_entry_parents_through_the_entry_repository()
    {
        $collection = (new Collection)->handle('test');

        Facades\Entry::shouldReceive('updateParents')->with($collection, null)->once()->ordered();
        Facades\Entry::shouldReceive('updateParents')->with($collection, ['one', 'two'])->once()->ordered();

        $collection->updateEntryParent();
        $collection->updateEntryParent(['one', 'two']);
    }

    #[Test]
    #[DataProvider('additionalPreviewTargetProvider')]
    public function it_gets_and_sets_preview_targets($throughFacade)
    {
        $collection = (new Collection)->handle('test');

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $collection->previewTargets());
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $collection->basePreviewTargets());
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $collection->additionalPreviewTargets());

        $this->assertEquals([
            ['label' => 'Entry', 'format' => '{permalink}', 'refresh' => true],
        ], $collection->basePreviewTargets()->all());

        $return = $collection->previewTargets([
            ['label' => 'Foo', 'format' => '{foo}', 'refresh' => true],
            ['label' => 'Bar', 'format' => '{bar}', 'refresh' => false],
            ['label' => 'Baz', 'format' => '{baz}'], // no explicit refresh should imply its enabled
        ]);

        $this->assertSame($collection, $return);

        $this->assertEquals([
            ['label' => 'Foo', 'format' => '{foo}', 'refresh' => true],
            ['label' => 'Bar', 'format' => '{bar}', 'refresh' => false],
            ['label' => 'Baz', 'format' => '{baz}', 'refresh' => true],
        ], $collection->previewTargets()->all());

        $this->assertEquals([
            ['label' => 'Foo', 'format' => '{foo}', 'refresh' => true],
            ['label' => 'Bar', 'format' => '{bar}', 'refresh' => false],
            ['label' => 'Baz', 'format' => '{baz}', 'refresh' => true],
        ], $collection->basePreviewTargets()->all());

        $this->assertEquals([], $collection->additionalPreviewTargets()->all());

        $extra = [
            ['label' => 'Qux', 'format' => '{qux}', 'refresh' => true],
            ['label' => 'Quux', 'format' => '{quux}', 'refresh' => false],
            ['label' => 'Flux', 'format' => '{flux}'], // no explicit refresh should imply its enabled
        ];

        if ($throughFacade) {
            \Statamic\Facades\Collection::addPreviewTargets('test', $extra);
        } else {
            $collection->addPreviewTargets($extra);
        }

        $this->assertEquals([
            ['label' => 'Foo', 'format' => '{foo}', 'refresh' => true],
            ['label' => 'Bar', 'format' => '{bar}', 'refresh' => false],
            ['label' => 'Baz', 'format' => '{baz}', 'refresh' => true],
            ['label' => 'Qux', 'format' => '{qux}', 'refresh' => true],
            ['label' => 'Quux', 'format' => '{quux}', 'refresh' => false],
            ['label' => 'Flux', 'format' => '{flux}', 'refresh' => true],
        ], $collection->previewTargets()->all());

        $this->assertEquals([
            ['label' => 'Foo', 'format' => '{foo}', 'refresh' => true],
            ['label' => 'Bar', 'format' => '{bar}', 'refresh' => false],
            ['label' => 'Baz', 'format' => '{baz}', 'refresh' => true],
        ], $collection->basePreviewTargets()->all());

        $this->assertEquals([
            ['label' => 'Qux', 'format' => '{qux}', 'refresh' => true],
            ['label' => 'Quux', 'format' => '{quux}', 'refresh' => false],
            ['label' => 'Flux', 'format' => '{flux}', 'refresh' => true],
        ], $collection->additionalPreviewTargets()->all());
    }

    #[Test]
    public function it_trucates_entries()
    {
        $collection = Facades\Collection::make('test')->save();
        Facades\Entry::make()->collection('test')->id('1')->slug('one')->save();
        Facades\Entry::make()->collection('test')->id('2')->slug('two')->save();
        Facades\Entry::make()->collection('test')->id('3')->slug('three')->save();

        $this->assertCount(3, $collection->queryEntries()->get());

        $collection->truncate();

        $this->assertCount(0, $collection->queryEntries()->get());
    }

    public static function additionalPreviewTargetProvider()
    {
        return [
            'through object' => [false],
            'through facade' => [true],
        ];
    }

    #[Test]
    public function it_cannot_view_collections_from_sites_that_the_user_is_not_authorized_to_see()
    {
        $this->setSites([
            'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://test.com/'],
            'fr' => ['name' => 'French', 'locale' => 'fr_FR', 'url' => 'http://fr.test.com/'],
            'de' => ['name' => 'German', 'locale' => 'de_DE', 'url' => 'http://test.com/de/'],
        ]);

        $collection1 = tap(Facades\Collection::make('has_some_french')->sites(['en', 'fr', 'de']))->save();
        $collection2 = tap(Facades\Collection::make('has_no_french')->sites(['en', 'de']))->save();
        $collection3 = tap(Facades\Collection::make('has_only_french')->sites(['fr']))->save();

        $this->setTestRoles(['test' => [
            'access cp',
            'view has_some_french entries',
            'view has_no_french entries',
            'view has_only_french entries',
            'access en site',
            // 'access fr site', // Give them access to all data, but not all sites
            'access de site',
        ]]);

        $user = tap(User::make()->assignRole('test'))->save();
        $this->assertTrue($user->can('view', $collection1));
        $this->assertTrue($user->can('view', $collection2));
        $this->assertFalse($user->can('view', $collection3));
    }

    #[Test]
    public function it_fires_a_deleting_event()
    {
        Event::fake();

        $collection = Facades\Collection::make('test')->save();

        $collection->delete();

        Event::assertDispatched(CollectionDeleting::class, function ($event) use ($collection) {
            return $event->collection === $collection;
        });
    }

    #[Test]
    public function it_does_not_delete_when_a_deleting_event_returns_false()
    {
        Facades\Collection::spy();
        Event::fake([CollectionDeleted::class]);

        Event::listen(CollectionDeleting::class, function () {
            return false;
        });

        $collection = new Collection('test');

        $return = $collection->delete();

        $this->assertFalse($return);
        Facades\Collection::shouldNotHaveReceived('delete');
        Event::assertNotDispatched(CollectionDeleted::class);
    }

    #[Test]
    public function it_deletes_quietly()
    {
        Event::fake();

        $collection = Facades\Collection::make('test')->save();

        $return = $collection->deleteQuietly();

        Event::assertNotDispatched(CollectionDeleting::class);
        Event::assertNotDispatched(CollectionDeleted::class);

        $this->assertTrue($return);
    }
}
