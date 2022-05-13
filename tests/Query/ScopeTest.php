<?php

namespace Tests\Query;

use Exception;
use Illuminate\Support\Facades\Storage;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Entry;
use Statamic\Facades\Term;
use Statamic\Facades\User;
use Statamic\Query\Scopes\Scope;
use Statamic\Statamic;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ScopeTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        app('statamic.scopes')[ScopeForAllBuilders::handle()] = ScopeForAllBuilders::class;
        app('statamic.scopes')[ScopeForLimitedBuilders::handle()] = ScopeForLimitedBuilders::class;
    }

    /** @test **/
    public function can_get_builders()
    {
        $builders = collect(['entries', 'terms'])->map(function ($builder) {
            return get_class(Statamic::query($builder));
        });

        $this->assertEquals(ScopeForAllBuilders::builders(), collect());
        $this->assertEquals(ScopeForLimitedBuilders::builders(), $builders);
    }

    /** @test **/
    public function can_use_scope_on_all_builders()
    {
        Storage::fake('test', ['url' => '/assets']);
        tap(AssetContainer::make('test')->disk('test'))->save();

        try {
            Entry::query()->scopeForAllBuilders()->get();
            Term::query()->scopeForAllBuilders()->get();
            Asset::query()->where('container', 'test')->scopeForAllBuilders()->get();
            User::query()->scopeForAllBuilders()->get();
            $this->assertTrue(true);
        } catch (\Throwable $th) {
            $this->assertTrue(false);
        }
    }

    /** @test **/
    public function can_only_use_scope_on_defined_builders()
    {
        $this->expectException(Exception::class);

        Entry::query()->scopeForLimitedBuilders()->get();
        Term::query()->scopeForLimitedBuilders()->get();
        Asset::query()->scopeForLimitedBuilders()->get();
        User::query()->scopeForLimitedBuilders()->get();
    }
}

class ScopeForAllBuilders extends Scope
{
    public function apply($query, $params)
    {
        $query->where('title', 'Post 1');
    }
}

class ScopeForLimitedBuilders extends Scope
{
    protected static $builders = ['entries', 'terms'];

    public function apply($query, $params)
    {
        $query->where('title', 'Post 1');
    }
}
