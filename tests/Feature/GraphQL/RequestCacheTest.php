<?php

namespace Tests\Feature\GraphQL;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Statamic\Facades\Form;
use Statamic\GraphQL\DefaultSchema;
use Statamic\GraphQL\Queries\PingQuery;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group graphql */
class RequestCacheTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function getEnvironmentSetup($app)
    {
        parent::getEnvironmentSetUp($app);

        // todo: if/when https://github.com/rebing/graphql-laravel/pull/706 is merged,
        // we'll be able to just push a custom query within the test.
        $app->instance(DefaultSchema::class, new CustomSchema);
    }

    /** @test */
    public function it_caches_a_request()
    {
        $this->withoutExceptionHandling();

        app()->instance('request-tracking', $requests = Collection::make());

        Collection::times(2)->each(function () {
            $this
                ->post('/graphql', ['query' => '{one}'])
                ->assertExactJson(['data' => ['one' => 'one']]);

            $this
                ->post('/graphql', ['query' => '{one, two}'])
                ->assertExactJson(['data' => ['one' => 'one', 'two' => 'two']]);

            $this
                ->post('/graphql', ['query' => '{one}', 'variables' => ['foo' => 'bar']])
                ->assertExactJson(['data' => ['one' => 'one']]);

            $this
                ->post('/graphql', ['query' => '{one, two}', 'variables' => ['foo' => 'bar']])
                ->assertExactJson(['data' => ['one' => 'one', 'two' => 'two']]);

            $this
                ->post('/graphql', ['query' => '{one}', 'variables' => ['foo' => 'baz']])
                ->assertExactJson(['data' => ['one' => 'one']]);

            $this
                ->post('/graphql', ['query' => '{one, two}', 'variables' => ['foo' => 'baz']])
                ->assertExactJson(['data' => ['one' => 'one', 'two' => 'two']]);
        });

        $this->assertCount(6, $requests);
        $this->assertEquals([
            ['query' => '{one}', 'variables' => null],
            ['query' => '{one, two}', 'variables' => null],
            ['query' => '{one}', 'variables' => ['foo' => 'bar']],
            ['query' => '{one, two}', 'variables' => ['foo' => 'bar']],
            ['query' => '{one}', 'variables' => ['foo' => 'baz']],
            ['query' => '{one, two}', 'variables' => ['foo' => 'baz']],
        ], $requests->all());
    }

    /** @test */
    public function it_caches_endpoint_using_configured_expiry()
    {
        config(['statamic.graphql.cache.expiry' => 13]);
        app()->instance('request-tracking', $requests = Collection::make());

        // md5('{one}')_md5(json_encode(['foo' => 'bar']))
        $key = 'gql-cache:0737a8fd85124abab1445165f4753936_9bb58f26192e4ba00f01e2e7b136bbd8';

        Carbon::setTestNow(now());
        $this->assertFalse(Cache::has($key));

        $this->post('/graphql', ['query' => '{one}', 'variables' => ['foo' => 'bar']]);

        $this->assertTrue(Cache::has($key));

        Carbon::setTestNow(now()->addMinutes(14));

        $this->assertFalse(Cache::has($key));
    }

    /** @test */
    public function it_busts_whole_cache_when_content_is_saved()
    {
        Cache::forever('completely-unrelated-thing', 'test');

        $entry = EntryFactory::collection('test')->create();

        app()->instance('request-tracking', $requests = Collection::make());

        $this->post('/graphql', ['query' => '{one}']);

        $entry->save();

        $this->post('/graphql', ['query' => '{one}']);

        $this->assertCount(2, $requests);
        $this->assertEquals([
            ['query' => '{one}', 'variables' => null],
            ['query' => '{one}', 'variables' => null],
        ], $requests->all());

        // Ensure that the solution was not just to clear the entire cache.
        $this->assertEquals('test', Cache::get('completely-unrelated-thing'));
    }

    /** @test */
    public function it_ignores_configured_events()
    {
        $entry = EntryFactory::collection('test')->create();
        $form = tap(Form::make('test'))->save();

        config(['statamic.graphql.cache.ignored_events' => [
            \Statamic\Events\EntrySaved::class,
        ]]);

        app()->instance('request-tracking', $requests = Collection::make());

        $this->post('/graphql', ['query' => '{one}']);

        $entry->save();

        $this->post('/graphql', ['query' => '{one}']);

        $this->assertCount(1, $requests);
        $this->assertEquals([
            ['query' => '{one}', 'variables' => null],
        ], $requests->all());

        $form->save();

        $this->post('/graphql', ['query' => '{one}']);

        $this->assertCount(2, $requests);
        $this->assertEquals([
            ['query' => '{one}', 'variables' => null],
            ['query' => '{one}', 'variables' => null],
        ], $requests->all());
    }

    /** @test */
    public function it_can_disable_caching()
    {
        config(['statamic.graphql.cache' => false]);

        app()->instance('request-tracking', $requests = Collection::make());

        Collection::times(2)->each(function () {
            $this
                ->post('/graphql', ['query' => '{one}'])
                ->assertExactJson(['data' => ['one' => 'one']]);
        });

        $this->assertCount(2, $requests);
        $this->assertEquals([
            ['query' => '{one}', 'variables' => null],
            ['query' => '{one}', 'variables' => null],
        ], $requests->all());
    }
}

class CustomSchema extends DefaultSchema
{
    public function getConfig()
    {
        $config = parent::getConfig();

        $config['query'][] = QueryOne::class;
        $config['query'][] = QueryTwo::class;
        $config['middleware'][] = TrackRequests::class;

        return $config;
    }
}

class QueryOne extends PingQuery
{
    protected $attributes = ['name' => 'one'];

    public function resolve()
    {
        return 'one';
    }
}

class QueryTwo extends PingQuery
{
    protected $attributes = ['name' => 'two'];

    public function resolve()
    {
        return 'two';
    }
}

class TrackRequests
{
    public function handle($request, $next)
    {
        app('request-tracking')[] = [
            'query' => $request->input('query'),
            'variables' => $request->input('variables'),
        ];

        return $next($request);
    }
}
