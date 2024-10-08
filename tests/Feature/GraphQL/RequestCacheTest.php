<?php

namespace Tests\Feature\GraphQL;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\GraphQL;
use Statamic\Facades\Token;
use Statamic\GraphQL\Queries\PingQuery;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('graphql')]
class RequestCacheTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function getEnvironmentSetup($app)
    {
        parent::getEnvironmentSetUp($app);

        GraphQL::addQuery(QueryOne::class);
        GraphQL::addQuery(QueryTwo::class);
        GraphQL::addMiddleware(TrackRequests::class);
    }

    #[Test]
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

    #[Test]
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

    #[Test]
    #[DataProvider('bypassCacheProvider')]
    public function it_bypasses_cache_when_using_a_valid_token($url, $headers)
    {
        $this->withoutExceptionHandling();

        app()->instance('request-tracking', $requests = Collection::make());

        optional(Token::find('test-token'))->delete(); // garbage collection
        Token::make('test-token', TestTokenHandler::class)->save();

        Collection::times(2)->each(function () use ($url, $headers) {
            $this
                ->post($url, ['query' => '{one}'], $headers)
                ->assertExactJson(['data' => ['one' => 'one']]);
        });

        $this->assertCount(2, $requests);
        $this->assertEquals([
            ['query' => '{one}', 'variables' => null],
            ['query' => '{one}', 'variables' => null],
        ], $requests->all());
    }

    #[Test]
    #[DataProvider('bypassCacheProvider')]
    public function it_doesnt_bypass_cache_when_using_an_invalid_token($url, $headers)
    {
        $this->withoutExceptionHandling();

        app()->instance('request-tracking', $requests = Collection::make());

        // No token should exist, but do garbage collection.
        // There may be a leftover token from a previous test.
        optional(Token::find('test-token'))->delete();

        Collection::times(2)->each(function () use ($url, $headers) {
            $this
                ->post($url, ['query' => '{one}'], $headers)
                ->assertExactJson(['data' => ['one' => 'one']]);
        });

        $this->assertCount(1, $requests);
        $this->assertEquals([
            ['query' => '{one}', 'variables' => null],
        ], $requests->all());
    }

    public static function bypassCacheProvider()
    {
        return [
            ['/graphql?token=test-token', []],
            ['/graphql', ['X-Statamic-Token' => 'test-token']],
        ];
    }

    #[Test]
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

    #[Test]
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

    #[Test]
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

class TestTokenHandler
{
    public function handle($token, $request, $next)
    {
        return $next($request);
    }
}
