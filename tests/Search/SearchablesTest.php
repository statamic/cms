<?php

namespace Tests\Search;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Assert;
use Statamic\Assets\AssetCollection;
use Statamic\Auth\UserCollection;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Entries\EntryCollection;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Entry;
use Statamic\Facades\Term;
use Statamic\Facades\User;
use Statamic\Search\Searchables;
use Statamic\Taxonomies\TermCollection;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class SearchablesTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        AssetContainer::make('images')->save();
        AssetContainer::make('documents')->save();
        AssetContainer::make('audio')->save();
    }

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

        $everything = [
            $entryA,
            $entryB,
            $termA,
            $termB,
            $assetA,
            $assetB,
            $userA,
            $userB,
        ];

        $items = $searchables->all();
        $this->assertInstanceOf(Collection::class, $items);
        $this->assertEquals($everything, $items->all());

        foreach ($everything as $searchable) {
            $this->assertTrue($searchables->contains($searchable));
        }

        $this->assertFalse($searchables->contains(new NotSearchable));
    }

    /** @test */
    public function it_gets_searchable_entries_in_specific_collections()
    {
        $blog = [$entryA = Entry::make()->collection('blog'), $entryB = Entry::make()->collection('blog')];
        $pages = [$entryC = Entry::make()->collection('pages'), $entryD = Entry::make()->collection('pages')];
        $entry = Entry::make()->collection('events');
        $term = Term::make();
        $asset = Asset::make();
        $user = User::make();
        Term::shouldReceive('all')->never();
        Term::shouldReceive('whereTaxonomy')->never();
        Asset::shouldReceive('all')->never();
        Asset::shouldReceive('whereContainer')->never();
        User::shouldReceive('all')->never();
        Entry::shouldReceive('whereCollection')->with('blog')->once()->andReturn(EntryCollection::make($blog));
        Entry::shouldReceive('whereCollection')->with('pages')->once()->andReturn(EntryCollection::make($pages));

        $searchables = $this->makeSearchables(['searchables' => ['collection:blog', 'collection:pages']]);

        $expected = [$entryA, $entryB, $entryC, $entryD];
        $this->assertEquals($expected, $searchables->all()->all());
        foreach ($expected as $item) {
            $this->assertTrue($searchables->contains($item));
        }
        $this->assertFalse($searchables->contains($term));
        $this->assertFalse($searchables->contains($asset));
        $this->assertFalse($searchables->contains($user));
        $this->assertFalse($searchables->contains($entry));
        $this->assertFalse($searchables->contains(new NotSearchable));
    }

    /** @test */
    public function it_gets_searchable_entries_in_all_collections()
    {
        $entries = [Entry::make()->collection('blog'), Entry::make()->collection('pages')];
        $term = Term::make();
        $asset = Asset::make();
        $user = User::make();
        Term::shouldReceive('all')->never();
        Term::shouldReceive('whereTaxonomy')->never();
        Asset::shouldReceive('all')->never();
        Asset::shouldReceive('whereContainer')->never();
        User::shouldReceive('all')->never();
        Entry::shouldReceive('all')->once()->andReturn(EntryCollection::make($entries));

        $searchables = $this->makeSearchables(['searchables' => ['collection:*']]);

        $this->assertEquals($entries, $searchables->all()->all());
        foreach ($entries as $item) {
            $this->assertTrue($searchables->contains($item));
        }
        $this->assertFalse($searchables->contains($term));
        $this->assertFalse($searchables->contains($asset));
        $this->assertFalse($searchables->contains($user));
        $this->assertFalse($searchables->contains(new NotSearchable));
    }

    /** @test */
    public function it_gets_searchable_terms_in_specific_taxonomies()
    {
        $tags = [$termA = Term::make()->taxonomy('tags'), $termB = Term::make()->taxonomy('tags')];
        $categories = [$termC = Term::make()->taxonomy('categories'), $termD = Term::make()->taxonomy('categories')];
        $entry = Entry::make();
        $term = Term::make();
        $asset = Asset::make();
        $user = User::make();
        Entry::shouldReceive('all')->never();
        Entry::shouldReceive('whereTaxonomy')->never();
        Asset::shouldReceive('all')->never();
        Asset::shouldReceive('whereContainer')->never();
        User::shouldReceive('all')->never();
        Term::shouldReceive('whereTaxonomy')->with('tags')->once()->andReturn(TermCollection::make($tags));
        Term::shouldReceive('whereTaxonomy')->with('categories')->once()->andReturn(TermCollection::make($categories));

        $searchables = $this->makeSearchables(['searchables' => ['taxonomy:tags', 'taxonomy:categories']]);

        $expected = [$termA, $termB, $termC, $termD];
        $this->assertEquals($expected, $searchables->all()->all());
        foreach ($expected as $item) {
            $this->assertTrue($searchables->contains($item));
        }
        $this->assertFalse($searchables->contains($entry));
        $this->assertFalse($searchables->contains($asset));
        $this->assertFalse($searchables->contains($user));
        $this->assertFalse($searchables->contains($term));
        $this->assertFalse($searchables->contains(new NotSearchable));
    }

    /** @test */
    public function it_gets_searchable_terms_in_all_taxonomies()
    {
        $terms = [Term::make(), Term::make()];
        $entry = Entry::make();
        $asset = Asset::make();
        $user = User::make();
        Entry::shouldReceive('all')->never();
        Entry::shouldReceive('whereCollection')->never();
        Asset::shouldReceive('all')->never();
        Asset::shouldReceive('whereContainer')->never();
        User::shouldReceive('all')->never();
        Term::shouldReceive('all')->once()->andReturn(TermCollection::make($terms));

        $searchables = $this->makeSearchables(['searchables' => ['taxonomy:*']]);

        $this->assertEquals($terms, $searchables->all()->all());
        foreach ($terms as $item) {
            $this->assertTrue($searchables->contains($item));
        }
        $this->assertFalse($searchables->contains($entry));
        $this->assertFalse($searchables->contains($asset));
        $this->assertFalse($searchables->contains($user));
        $this->assertFalse($searchables->contains(new NotSearchable));
    }

    /** @test */
    public function it_gets_searchable_assets_in_specific_containers()
    {
        $images = [
            $assetA = Asset::make()->container('images')->path('a.jpg'),
            $assetB = Asset::make()->container('images')->path('b.jpg'),
        ];
        $documents = [
            $assetC = Asset::make()->container('documents')->path('c.jpg'),
            $assetD = Asset::make()->container('documents')->path('d.jpg'),
        ];
        $entry = Entry::make();
        $term = Term::make();
        $asset = Asset::make()->container('audio');
        $user = User::make();
        Entry::shouldReceive('all')->never();
        Entry::shouldReceive('whereCollection')->never();
        Term::shouldReceive('all')->never();
        Term::shouldReceive('whereTaxonomy')->never();
        User::shouldReceive('all')->never();
        Asset::shouldReceive('whereContainer')->with('images')->once()->andReturn(AssetCollection::make($images));
        Asset::shouldReceive('whereContainer')->with('documents')->once()->andReturn(AssetCollection::make($documents));

        $searchables = $this->makeSearchables(['searchables' => ['assets:images', 'assets:documents']]);

        $expected = [$assetA, $assetB, $assetC, $assetD];
        $this->assertEquals($expected, $searchables->all()->all());
        foreach ($expected as $item) {
            $this->assertTrue($searchables->contains($item));
        }
        $this->assertFalse($searchables->contains($entry));
        $this->assertFalse($searchables->contains($asset));
        $this->assertFalse($searchables->contains($user));
        $this->assertFalse($searchables->contains($term));
        $this->assertFalse($searchables->contains(new NotSearchable));
    }

    /** @test */
    public function it_gets_searchable_assets_in_all_containers()
    {
        $assets = [Asset::make(), Asset::make()];
        $entry = Entry::make();
        $term = Term::make();
        $user = User::make();
        Entry::shouldReceive('all')->never();
        Entry::shouldReceive('whereCollection')->never();
        Term::shouldReceive('all')->never();
        Term::shouldReceive('whereTaxonomy')->never();
        User::shouldReceive('all')->never();

        Asset::shouldReceive('all')->once()->andReturn(AssetCollection::make($assets));

        $searchables = $this->makeSearchables(['searchables' => ['assets:*']]);
        $this->assertEquals($assets, $searchables->all()->all());
        foreach ($assets as $item) {
            $this->assertTrue($searchables->contains($item));
        }
        $this->assertFalse($searchables->contains($entry));
        $this->assertFalse($searchables->contains($term));
        $this->assertFalse($searchables->contains($user));
        $this->assertFalse($searchables->contains(new NotSearchable));
    }

    /** @test */
    public function it_gets_searchable_users()
    {
        $users = [User::make(), User::make()];
        $entry = Entry::make();
        $term = Term::make();
        $user = User::make();
        $asset = Asset::make();
        Entry::shouldReceive('all')->never();
        Entry::shouldReceive('whereCollection')->never();
        Term::shouldReceive('all')->never();
        Term::shouldReceive('whereTaxonomy')->never();
        Asset::shouldReceive('all')->never();
        Asset::shouldReceive('whereContainer')->never();

        User::shouldReceive('all')->once()->andReturn(UserCollection::make($users));

        $searchables = $this->makeSearchables(['searchables' => ['users']]);
        $this->assertEquals($users, $searchables->all()->all());
        foreach ($users as $item) {
            $this->assertTrue($searchables->contains($item));
        }
        $this->assertFalse($searchables->contains($entry));
        $this->assertFalse($searchables->contains($term));
        $this->assertFalse($searchables->contains($asset));
        $this->assertFalse($searchables->contains(new NotSearchable));
    }

    /** @test */
    public function it_transforms_values_set_in_the_config_file()
    {
        config()->set('statamic.search.indexes.default', [
            'fields' => [
                'title',
            ],
            'transformers' => [
                'title' => function ($value, $searchable) {
                    $this->assertEquals('Hello', $value);
                    $this->assertInstanceOf(EntryContract::class, $searchable);
                    $this->assertEquals('a', $searchable->id());

                    return strtoupper($value);
                },
            ],
        ]);

        $index = app(\Statamic\Search\Comb\Index::class, [
            'name' => 'default',
            'config' => config('statamic.search.indexes.default'),
        ]);

        $searchable = EntryFactory::collection('test')->id('a')->data(['title' => 'Hello'])->make();
        $searchables = new Searchables($index);

        $this->assertEquals([
            'title' => 'HELLO',
        ], $searchables->fields($searchable));
    }

    /** @test */
    public function it_uses_regular_value_if_theres_not_a_corresponding_transformer()
    {
        config()->set('statamic.search.indexes.default', [
            'fields' => ['title'],
            'transformers' => [],
        ]);

        $index = app(\Statamic\Search\Comb\Index::class, [
            'name' => 'default',
            'config' => config('statamic.search.indexes.default'),
        ]);

        $searchable = EntryFactory::collection('test')->data(['title' => 'Hello'])->make();
        $searchables = new Searchables($index);

        $this->assertEquals([
            'title' => 'Hello',
        ], $searchables->fields($searchable));
    }

    /** @test */
    public function it_transforms_by_a_class_set_in_the_config_file()
    {
        config()->set('statamic.search.indexes.default', [
            'fields' => [
                'title',
            ],
            'transformers' => [
                'title' => BasicTestTransformer::class,
            ],
        ]);

        $index = app(\Statamic\Search\Comb\Index::class, [
            'name' => 'default',
            'config' => config('statamic.search.indexes.default'),
        ]);

        $searchable = EntryFactory::collection('test')->id('a')->data(['title' => 'Hello'])->make();
        $searchables = new Searchables($index);

        $this->assertEquals([
            'title' => 'HELLO',
        ], $searchables->fields($searchable));
    }

    /** @test */
    public function if_transformed_value_is_a_string_without_a_matching_class_it_throws_exception()
    {
        $this->expectExceptionMessage('Search transformer [foo] not found.');

        config()->set('statamic.search.indexes.default', [
            'fields' => [
                'title',
            ],
            'transformers' => [
                'title' => 'foo',
            ],
        ]);

        $index = app(\Statamic\Search\Comb\Index::class, [
            'name' => 'default',
            'config' => config('statamic.search.indexes.default'),
        ]);

        $searchable = EntryFactory::collection('test')->data(['title' => 'Hello'])->make();
        $searchables = new Searchables($index);
        $searchables->fields($searchable);
    }

    /** @test */
    public function if_a_closure_based_transformer_returns_an_array_it_gets_combined_into_the_results()
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

        $searchable = EntryFactory::collection('test')->data(['title' => 'Hello'])->make();
        $searchables = new Searchables($index);

        $this->assertEquals([
            'title' => 'Hello',
            'title_upper' => 'HELLO',
        ], $searchables->fields($searchable));
    }

    /** @test */
    public function if_a_class_based_transformer_returns_an_array_it_gets_combined_into_the_results()
    {
        config()->set('statamic.search.indexes.default', [
            'fields' => [
                'title',
            ],
            'transformers' => [
                'title' => ArrayTestTransformer::class,
            ],
        ]);

        $index = app(\Statamic\Search\Comb\Index::class, [
            'name' => 'default',
            'config' => config('statamic.search.indexes.default'),
        ]);

        $searchable = EntryFactory::collection('test')->data(['title' => 'Hello'])->make();
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

class NotSearchable
{
    //
}

class BasicTestTransformer
{
    public function handle($value, $field, $searchable)
    {
        Assert::assertEquals('Hello', $value);
        Assert::assertEquals('title', $field);
        Assert::assertInstanceOf(EntryContract::class, $searchable);
        Assert::assertEquals('a', $searchable->id());

        return strtoupper($value);
    }
}

class ArrayTestTransformer
{
    public function handle($value, $field)
    {
        return [
            $field => $value,
            $field.'_upper' => strtoupper($value),
        ];
    }
}
