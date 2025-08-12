<?php

namespace Tests\Search;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Mockery;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Entry;
use Statamic\Facades\Search;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Facades\User;
use Statamic\Search\Searchables;
use Statamic\Search\Searchables\Assets;
use Statamic\Search\Searchables\Entries;
use Statamic\Search\Searchables\Provider;
use Statamic\Search\Searchables\Providers;
use Statamic\Search\Searchables\Terms;
use Statamic\Search\Searchables\Users;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class SearchablesTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        Taxonomy::make('tags')->save();

        Storage::fake('images');
        Storage::fake('documents');
        Storage::fake('audio');
        AssetContainer::make('images')->disk('images')->save();
        AssetContainer::make('documents')->disk('documents')->save();
        AssetContainer::make('audio')->disk('audio')->save();
    }

    #[Test]
    public function it_checks_all_providers_for_whether_an_item_is_searchable()
    {
        app(Providers::class)->register($entries = Mockery::mock(Entries::class)->makePartial());
        app(Providers::class)->register($terms = Mockery::mock(Terms::class)->makePartial());
        app(Providers::class)->register($assets = Mockery::mock(Assets::class)->makePartial());
        app(Providers::class)->register($users = Mockery::mock(Users::class)->makePartial());

        $searchable = Mockery::mock();
        $searchables = $this->makeSearchables(['searchables' => 'all']);

        // Check twice.
        // First time they'll all return false, so contains() will return false.
        // Second time, assets will return true, so contains() will return true early, and users won't be checked.

        $entries->shouldReceive('contains')->with($searchable)->twice()->andReturn(false, false);
        $terms->shouldReceive('contains')->with($searchable)->twice()->andReturn(false, false);
        $assets->shouldReceive('contains')->with($searchable)->twice()->andReturn(false, true);
        $users->shouldReceive('contains')->with($searchable)->once()->andReturn(false);

        $this->assertFalse($searchables->contains($searchable));
        $this->assertTrue($searchables->contains($searchable));
    }

    #[Test]
    public function all_searchables_include_entries_terms_assets_and_users()
    {
        app(Providers::class)->register($entries = Mockery::mock(Entries::class)->makePartial());
        app(Providers::class)->register($terms = Mockery::mock(Terms::class)->makePartial());
        app(Providers::class)->register($assets = Mockery::mock(Assets::class)->makePartial());
        app(Providers::class)->register($users = Mockery::mock(Users::class)->makePartial());

        $entries->shouldReceive('provide')->andReturn(collect([$entryA = Entry::make(), $entryB = Entry::make()]));
        $terms->shouldReceive('provide')->andReturn(collect([$termA = Term::make(), $termB = Term::make()]));
        $assets->shouldReceive('provide')->andReturn(collect([$assetA = Asset::make(), $assetB = Asset::make()]));
        $users->shouldReceive('provide')->andReturn(collect([$userA = User::make(), $userB = User::make()]));

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
    }

    #[Test]
    public function all_searchables_doesnt_include_searchable_where_included_in_all_is_false()
    {
        app(Providers::class)->register($entries = Mockery::mock(Entries::class)->makePartial());
        app(Providers::class)->register($terms = Mockery::mock(Terms::class)->makePartial());
        app(Providers::class)->register($assets = Mockery::mock(Assets::class)->makePartial());
        app(Providers::class)->register($users = Mockery::mock(Users::class)->makePartial());

        $entries->shouldReceive('provide')->andReturn(collect([$entryA = Entry::make(), $entryB = Entry::make()]));
        $terms->shouldReceive('provide')->andReturn(collect([$termA = Term::make(), $termB = Term::make()]));
        $assets->shouldReceive('provide')->andReturn(collect([$assetA = Asset::make(), $assetB = Asset::make()]));

        $users->shouldReceive('includedInAll')->andReturn(false);
        $users->shouldReceive('provide')->andReturn(collect([$userA = User::make(), $userB = User::make()]));

        $searchables = $this->makeSearchables(['searchables' => 'all']);

        $everythingApartFromUsers = [
            $entryA,
            $entryB,
            $termA,
            $termB,
            $assetA,
            $assetB,
        ];

        $items = $searchables->all();
        $this->assertInstanceOf(Collection::class, $items);
        $this->assertEquals($everythingApartFromUsers, $items->all());
        $this->assertFalse($searchables->contains($userA));
        $this->assertFalse($searchables->contains($userB));
    }

    #[Test]
    public function it_gets_searchables_from_specific_providers()
    {
        app(Providers::class)->register($entries = Mockery::mock(Entries::class)->makePartial());
        app(Providers::class)->register($terms = Mockery::mock(Terms::class)->makePartial());
        app(Providers::class)->register($users = Mockery::mock(Users::class)->makePartial());

        $entries->shouldReceive('provide')->once()->andReturn(collect([$entryA = Entry::make(), $entryB = Entry::make()]));
        $users->shouldReceive('provide')->once()->andReturn(collect([$userA = User::make(), $userB = User::make()]));
        $terms->shouldReceive('provide')->never();

        $searchables = $this->makeSearchables(['searchables' => ['collection:blog', 'collection:pages', 'users']]);

        $this->assertEquals([$entryA, $entryB, $userA, $userB], $searchables->all()->all());
    }

    #[Test]
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
            'locale' => 'en',
        ]);

        $searchable = EntryFactory::collection('test')->id('a')->data(['title' => 'Hello'])->make();
        $searchables = new Searchables($index);

        $this->assertEquals([
            'title' => 'HELLO',
        ], $searchables->fields($searchable));
    }

    #[Test]
    public function it_uses_regular_value_if_theres_not_a_corresponding_transformer()
    {
        config()->set('statamic.search.indexes.default', [
            'fields' => ['title'],
            'transformers' => [],
        ]);

        $index = app(\Statamic\Search\Comb\Index::class, [
            'name' => 'default',
            'config' => config('statamic.search.indexes.default'),
            'locale' => 'en',
        ]);

        $searchable = EntryFactory::collection('test')->data(['title' => 'Hello'])->make();
        $searchables = new Searchables($index);

        $this->assertEquals([
            'title' => 'Hello',
        ], $searchables->fields($searchable));
    }

    #[Test]
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
            'locale' => 'en',
        ]);

        $searchable = EntryFactory::collection('test')->id('a')->data(['title' => 'Hello'])->make();
        $searchables = new Searchables($index);

        $this->assertEquals([
            'title' => 'HELLO',
        ], $searchables->fields($searchable));
    }

    #[Test]
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
            'locale' => 'en',
        ]);

        $searchable = EntryFactory::collection('test')->data(['title' => 'Hello'])->make();
        $searchables = new Searchables($index);
        $searchables->fields($searchable);
    }

    #[Test]
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
            'locale' => 'en',
        ]);

        $searchable = EntryFactory::collection('test')->data(['title' => 'Hello'])->make();
        $searchables = new Searchables($index);

        $this->assertEquals([
            'title' => 'Hello',
            'title_upper' => 'HELLO',
        ], $searchables->fields($searchable));
    }

    #[Test]
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
            'locale' => 'en',
        ]);

        $searchable = EntryFactory::collection('test')->data(['title' => 'Hello'])->make();
        $searchables = new Searchables($index);

        $this->assertEquals([
            'title' => 'Hello',
            'title_upper' => 'HELLO',
        ], $searchables->fields($searchable));
    }

    #[Test]
    public function can_register_a_custom_searchable_and_get_results()
    {
        $a = new TestCustomSearchable(['title' => 'Custom 1']);
        $b = new TestCustomSearchable(['title' => 'Custom 2']);
        $c = new NotSearchable;
        app()->instance('all-custom-searchables', collect([$a, $b]));

        Search::registerSearchableProvider(TestCustomSearchables::class);

        $searchables = $this->makeSearchables(['searchables' => ['custom']]);

        $this->assertEquals([$a, $b], $searchables->all()->all());
        $this->assertTrue($searchables->contains($a));
        $this->assertTrue($searchables->contains($b));
        $this->assertFalse($searchables->contains($c));
    }

    #[Test]
    public function it_throws_exception_when_using_unknown_searchable()
    {
        $this->expectExceptionMessage('Unknown searchable [test]');
        $this->makeSearchables(['searchables' => ['test']]);
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

class TestCustomSearchable
{
    //
}

class TestCustomSearchables extends Provider
{
    public static function handle(): string
    {
        return 'custom';
    }

    public function find(array $keys): Collection
    {
    }

    public static function referencePrefix(): string
    {
        return 'customprefix';
    }

    public function provide(): Collection
    {
        return app('all-custom-searchables');
    }

    public function contains($searchable): bool
    {
        return $searchable instanceof TestCustomSearchable;
    }
}
