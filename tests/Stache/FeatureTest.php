<?php

namespace Tests\Stache;

use Tests\TestCase;
use Statamic\API\User;
use Statamic\API\Entry;
use Statamic\API\Content;
use Statamic\API\GlobalSet;
use Statamic\Stache\Stache;
use Statamic\API\Collection;
use Statamic\Stache\Fakes\YAML;
use Statamic\API\AssetContainer;
use Illuminate\Support\Facades\Cache;
use Statamic\Stache\Stores\BasicStore;
use Statamic\Stache\Stores\EntriesStore;
use Statamic\Stache\Stores\AggregateStore;
use Statamic\Stache\Stores\CollectionsStore;

class FeatureTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->stache = $this->app->make('stache')->withoutBooting(function ($stache) {
            $dir = __DIR__.'/__fixtures__';
            $stache->store('collections')->directory($dir . '/content/collections');
            $stache->store('entries')->directory($dir . '/content/collections');
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
        $this->assertEquals('Main Assets', AssetContainer::find('main')->data()['title']);
        $this->assertEquals('Another Asset Container', AssetContainer::find('another')->data()['title']);
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
    function it_gets_content()
    {
        $this->assertEquals(
            14, // 14 entries
            Content::all()->count()
        );

        $this->assertEquals('Christmas', Content::find('blog-christmas')->get('title'));
        // @TODO: terms and pages

        $this->assertNull(Content::find('unknown'));
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
}
