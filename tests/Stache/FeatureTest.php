<?php

namespace Tests\Stache;

use Mockery;
use Tests\TestCase;
use Statamic\API\User;
use Statamic\API\Entry;
use Statamic\API\Content;
use Statamic\API\Taxonomy;
use Statamic\API\GlobalSet;
use Statamic\API\Structure;
use Statamic\Stache\Stache;
use Statamic\API\Collection;
use Statamic\Stache\Fakes\YAML;
use Statamic\API\AssetContainer;
use Illuminate\Support\Facades\Cache;
use Statamic\Stache\Stores\BasicStore;
use Statamic\Stache\Stores\EntriesStore;
use Statamic\Stache\Stores\AggregateStore;
use Statamic\Stache\Stores\CollectionsStore;
use Statamic\Contracts\Data\Repositories\StructureRepository;

class FeatureTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->stache = $this->app->make('stache')->withoutBooting(function ($stache) {
            $dir = __DIR__.'/__fixtures__';
            $stache->store('taxonomies')->directory($dir . '/content/taxonomies');
            $stache->store('collections')->directory($dir . '/content/collections');
            $stache->store('entries')->directory($dir . '/content/collections');
            $stache->store('structures')->directory($dir . '/content/structures');
            $stache->store('globals')->directory($dir . '/content/globals');
            $stache->store('asset-containers')->directory($dir . '/content/assets');
            $stache->store('users')->directory($dir . '/users');
        });
    }

    /** @test */
    function it_gets_all_collections()
    {
        $this->assertEquals(4, Collection::all()->count());
    }

    /** @test */
    function it_gets_all_entries()
    {
        $this->assertEquals(14, Entry::all()->count());
        $this->assertEquals(3, Entry::whereCollection('alphabetical')->count());
        $this->assertEquals(2, Entry::whereCollection('blog')->count());
        $this->assertEquals(3, Entry::whereCollection('numeric')->count());
        $this->assertEquals(6, Entry::whereCollection('pages')->count());
        $this->assertEquals(5, Entry::whereCollection(['alphabetical', 'blog'])->count());
    }

    /** @test */
    function it_gets_entry()
    {
        $this->assertEquals('Christmas', Entry::find('blog-christmas')->get('title'));
    }

    /** @test */
    function it_gets_entry_by_slug()
    {
        $this->assertEquals('Christmas', Entry::findBySlug('christmas', 'blog', 'christmas')->get('title'));
    }

    /** @test */
    function it_gets_all_taxonomies()
    {
        $this->assertEquals(2, Taxonomy::all()->count());
    }

    /** @test */
    function it_gets_all_globals()
    {
        $this->assertEquals(2, GlobalSet::all()->count());
    }

    /** @test */
    function it_gets_globals()
    {
        $this->assertEquals('Bar', GlobalSet::find('globals-global')->get('foo'));
        $this->assertEquals('555-1234', GlobalSet::find('globals-contact')->get('phone'));
    }

    /** @test */
    function it_gets_asset_containers()
    {
        $this->assertEquals(2, AssetContainer::all()->count());
    }

    /** @test */
    function it_gets_an_asset_container()
    {
        $this->assertEquals('Main Assets', AssetContainer::find('main')->title());
        $this->assertEquals('Another Asset Container', AssetContainer::find('another')->title());
    }

    /** @test */
    function it_gets_users()
    {
        $this->assertEquals(2, User::all()->count());
    }

    /** @test */
    function it_gets_a_user()
    {
        $user = User::find('users-john');
        $this->assertEquals('users-john', $user->id());
        $this->assertEquals('John Smith', $user->get('name'));
        $this->assertEquals('john@example.com', $user->email());
    }

    /** @test */
    function it_gets_an_entry_by_uri()
    {
        $entry = Entry::whereUri('/numeric/two');
        $this->assertEquals('numeric-two', $entry->id());
        $this->assertEquals('Two', $entry->get('title'));

        $this->assertNull(Entry::whereUri('/unknown'));
    }

    /** @test */
    function it_gets_an_entry_in_structure_by_uri()
    {
        $entry = Entry::whereUri('/about/board/directors');
        $this->assertEquals('pages-directors', $entry->id());
        $this->assertEquals('Directors', $entry->get('title'));
    }

    /** @test */
    function it_gets_structures()
    {
        $this->assertEquals(2, Structure::all()->count());
    }

    /** @test */
    function it_gets_a_structure()
    {
        $structure = Structure::find('pages');
        $this->assertEquals('pages', $structure->handle());
        // TODO: Some more assertions
    }

    /** @test */
    function it_saves_structures()
    {
        $structure = Structure::find('pages');

        $repo = Mockery::mock(StructureRepository::class);
        $repo->shouldReceive('save')->with($structure);
        $this->app->instance(StructureRepository::class, $repo);

        $structure->save();
    }

    /** @test */
    function it_gets_content()
    {
        $this->assertEquals(
            14, // 14 entries
            Content::all()->count()
        );

        $this->assertEquals('Christmas', Content::find('blog-christmas')->get('title'));
        // TODO: terms and pages

        $this->assertNull(Content::find('unknown'));
    }

    /** @test */
    function it_gets_content_by_uri()
    {
        $this->assertEquals('One', Content::whereUri('/numeric/one')->get('title'));
        $this->assertEquals('Directors', Content::whereUri('/about/board/directors')->get('title'));
    }

    /** @test */
    function saving_a_collection_writes_it_to_file()
    {
        $collection = Collection::create('new');
        $collection->data([
            'title' => 'New Collection',
            'order' => 'date',
            'foo' => 'bar'
        ]);
        $collection->save();

        $this->assertStringEqualsFile(
            $path = __DIR__.'/__fixtures__/content/collections/new.yaml',
            "title: 'New Collection'\norder: date\nfoo: bar\n"
        );
        @unlink($path);
    }


    /** @test */
    function saving_an_asset_container_writes_it_to_file()
    {
        AssetContainer::make('new')->title('New Container')->save();

        $this->assertStringEqualsFile(
            $path = __DIR__.'/__fixtures__/content/assets/new.yaml',
            "title: 'New Container'\n"
        );
        @unlink($path);
    }

    /** @test */
    function saving_a_taxonomy_writes_it_to_file()
    {
        $taxonomy = Taxonomy::create('new');
        $taxonomy->data([
            'title' => 'New Taxonomy',
            'foo' => 'bar'
        ]);
        $taxonomy->save();

        $this->assertStringEqualsFile(
            $path = __DIR__.'/__fixtures__/content/taxonomies/new.yaml',
            "title: 'New Taxonomy'\nfoo: bar\n"
        );
        @unlink($path);
    }

    /** @test */
    function saving_a_global_set_writes_it_to_file()
    {
        $global = GlobalSet::make()
            ->id('123')
            ->handle('new')
            ->title('New Global Set')
            ->in('en', function ($loc) {
                $loc->data(['foo' => 'bar']);
            })
            ->save();

        $this->assertStringEqualsFile(
            $path = __DIR__.'/__fixtures__/content/globals/new.yaml',
            "id: '123'\ntitle: 'New Global Set'\ndata:\n  foo: bar\n"
        );
        @unlink($path);
    }

    /** @test */
    function saving_an_entry_writes_it_to_file()
    {
        Entry::make()
            ->id('123')
            ->collection(Collection::whereHandle('blog'))
            ->in('en', function ($loc) {
                $loc
                    ->slug('test-entry')
                    ->order('2017-07-04')
                    ->data(['title' => 'Test Entry', 'foo' => 'bar']);
            })
            ->save();

        $this->assertFileEqualsString(
            $path = __DIR__.'/__fixtures__/content/collections/blog/2017-07-04.test-entry.md',
            "title: 'Test Entry'\nfoo: bar\nid: '123'\n"
        );
        @unlink($path);
    }
}
