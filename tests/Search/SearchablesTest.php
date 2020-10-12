<?php

namespace Tests\Search;

use Illuminate\Support\Collection;
use Statamic\Assets\AssetCollection;
use Statamic\Auth\UserCollection;
use Statamic\Entries\EntryCollection;
use Statamic\Facades\Asset;
use Statamic\Facades\Entry;
use Statamic\Facades\Term;
use Statamic\Facades\User;
use Statamic\Search\Searchables;
use Statamic\Taxonomies\TermCollection;
use Tests\TestCase;

class SearchablesTest extends TestCase
{
    /** @test */
    public function all_searchables_include_entries_terms_assets_and_users()
    {
        $entries = [$entryA = Entry::make(), $entryB = Entry::make()];
        Entry::shouldReceive('all')->once()->andReturn(EntryCollection::make($entries));
        $terms = [$termA = Term::make(), $termB = Term::make()];
        Term::shouldReceive('all')->once()->andReturn(TermCollection::make($terms));
        $assets = [$assetA = Asset::make(), $assetB = Asset::make()];
        Asset::shouldReceive('all')->once()->andReturn(AssetCollection::make($assets));
        $users = [$userA = User::make(), $userB = User::make()];
        User::shouldReceive('all')->once()->andReturn(UserCollection::make($users));

        $searchables = $this->makeSearchables(['searchables' => 'all']);

        $items = $searchables->all();
        $this->assertInstanceOf(Collection::class, $items);
        $this->assertEquals([
            $entryA,
            $entryB,
            $termA,
            $termB,
            $assetA,
            $assetB,
            $userA,
            $userB,
        ], $items->all());
    }

    /** @test */
    public function it_gets_searchable_entries_in_specific_collections()
    {
        Term::shouldReceive('all')->never();
        Term::shouldReceive('whereTaxonomy')->never();
        Asset::shouldReceive('all')->never();
        Asset::shouldReceive('whereContainer')->never();
        User::shouldReceive('all')->never();

        $blog = [$entryA = Entry::make(), $entryB = Entry::make()];
        $pages = [$entryC = Entry::make(), $entryD = Entry::make()];
        Entry::shouldReceive('whereCollection')->with('blog')->once()->andReturn(EntryCollection::make($blog));
        Entry::shouldReceive('whereCollection')->with('pages')->once()->andReturn(EntryCollection::make($pages));

        $searchables = $this->makeSearchables(['searchables' => ['collection:blog', 'collection:pages']]);
        $this->assertEquals([$entryA, $entryB, $entryC, $entryD], $searchables->all()->all());
    }

    /** @test */
    public function it_gets_searchable_entries_in_all_collections()
    {
        Term::shouldReceive('all')->never();
        Term::shouldReceive('whereTaxonomy')->never();
        Asset::shouldReceive('all')->never();
        Asset::shouldReceive('whereContainer')->never();
        User::shouldReceive('all')->never();

        $entries = [$entryA = Entry::make(), $entryB = Entry::make()];
        Entry::shouldReceive('all')->once()->andReturn(EntryCollection::make($entries));

        $searchables = $this->makeSearchables(['searchables' => ['collection:*']]);
        $this->assertEquals([$entryA, $entryB], $searchables->all()->all());
    }

    /** @test */
    public function it_gets_searchable_terms_in_specific_taxonomies()
    {
        Entry::shouldReceive('all')->never();
        Entry::shouldReceive('whereTaxonomy')->never();
        Asset::shouldReceive('all')->never();
        Asset::shouldReceive('whereContainer')->never();
        User::shouldReceive('all')->never();

        $tags = [$termA = Term::make(), $termB = Term::make()];
        $categories = [$termC = Term::make(), $termD = Term::make()];
        Term::shouldReceive('whereTaxonomy')->with('tags')->once()->andReturn(TermCollection::make($tags));
        Term::shouldReceive('whereTaxonomy')->with('categories')->once()->andReturn(TermCollection::make($categories));

        $searchables = $this->makeSearchables(['searchables' => ['taxonomy:tags', 'taxonomy:categories']]);
        $this->assertEquals([$termA, $termB, $termC, $termD], $searchables->all()->all());
    }

    /** @test */
    public function it_gets_searchable_terms_in_all_taxonomies()
    {
        Entry::shouldReceive('all')->never();
        Entry::shouldReceive('whereCollection')->never();
        Asset::shouldReceive('all')->never();
        Asset::shouldReceive('whereContainer')->never();
        User::shouldReceive('all')->never();

        $terms = [$termA = Term::make(), $termB = Term::make()];
        Term::shouldReceive('all')->once()->andReturn(TermCollection::make($terms));

        $searchables = $this->makeSearchables(['searchables' => ['taxonomy:*']]);
        $this->assertEquals([$termA, $termB], $searchables->all()->all());
    }

    /** @test */
    public function it_gets_searchable_assets_in_specific_containers()
    {
        Entry::shouldReceive('all')->never();
        Entry::shouldReceive('whereCollection')->never();
        Term::shouldReceive('all')->never();
        Term::shouldReceive('whereTaxonomy')->never();
        User::shouldReceive('all')->never();

        $images = [$assetA = Asset::make(), $assetB = Asset::make()];
        $documents = [$assetC = Asset::make(), $assetD = Asset::make()];
        Asset::shouldReceive('whereContainer')->with('images')->once()->andReturn(AssetCollection::make($images));
        Asset::shouldReceive('whereContainer')->with('documents')->once()->andReturn(AssetCollection::make($documents));

        $searchables = $this->makeSearchables(['searchables' => ['assets:images', 'assets:documents']]);
        $this->assertEquals([$assetA, $assetB, $assetC, $assetD], $searchables->all()->all());
    }

    /** @test */
    public function it_gets_searchable_assets_in_all_containers()
    {
        Entry::shouldReceive('all')->never();
        Entry::shouldReceive('whereCollection')->never();
        Term::shouldReceive('all')->never();
        Term::shouldReceive('whereTaxonomy')->never();
        User::shouldReceive('all')->never();

        $assets = [$assetA = Asset::make(), $assetB = Asset::make()];
        Asset::shouldReceive('all')->once()->andReturn(AssetCollection::make($assets));

        $searchables = $this->makeSearchables(['searchables' => ['assets:*']]);
        $this->assertEquals([$assetA, $assetB], $searchables->all()->all());
    }

    /** @test */
    public function it_transforms_values_set_in_the_config_file()
    {
        config()->set('statamic.search.indexes.default', [
            'fields' => [
                'title',
            ],
            'transformers' => [
                'title' => function ($value) {
                    return strtoupper($value);
                },
            ],
        ]);

        $index = app(\Statamic\Search\Comb\Index::class, [
            'name' => 'default',
            'config' => config('statamic.search.indexes.default'),
        ]);

        $searchable = Entry::make()->data(['title' => 'Hello']);
        $searchables = new Searchables($index);

        $this->assertEquals([
            'title' => 'HELLO',
        ], $searchables->fields($searchable));
    }

    /** @test */
    public function if_a_transformer_returns_an_array_it_gets_combined_into_the_results()
    {
        config()->set('statamic.search.indexes.default', [
            'fields' => [
                'title',
            ],
            'transformers' => [
                'title' => function ($value) {
                    return [
                        'title' => $value,
                        'title_upper' => strtoupper($value),
                    ];
                },
            ],
        ]);

        $index = app(\Statamic\Search\Comb\Index::class, [
            'name' => 'default',
            'config' => config('statamic.search.indexes.default'),
        ]);

        $searchable = Entry::make()->data(['title' => 'Hello']);
        $searchables = new Searchables($index);

        $this->assertEquals([
            'title' => 'Hello',
            'title_upper' => 'HELLO',
        ], $searchables->fields($searchable));
    }

    private function makeSearchables($config)
    {
        $index = $this->mock(\Statamic\Search\Index::class);

        $index->shouldReceive('config')->andReturn($config);

        return new Searchables($index);
    }
}
