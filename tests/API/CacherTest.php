<?php

namespace Tests\API;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\API\AbstractCacher;
use Statamic\Events\EntrySaved;
use Statamic\Events\Event;
use Statamic\Facades;
use Statamic\Facades\Token;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class CacherTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private $collection;

    public function setUp(): void
    {
        parent::setUp();

        Facades\Config::set('statamic.api.enabled', true);
        Facades\Config::set('statamic.api.resources.collections', true);

        $this->collection = Facades\Collection::make('articles')->save();
    }

    protected function makeEntry($slug)
    {
        return EntryFactory::id($slug)->slug($slug)->collection($this->collection)->make();
    }

    #[Test]
    public function it_caches_endpoint_using_default_cacher()
    {
        $this->makeEntry('apple')->save();

        $this->get($endpoint = '/api/collections/articles/entries')
            ->assertOk()
            ->assertJson(['data' => [
                ['id' => 'apple'],
            ]]);

        $hash = md5($endpoint);
        $cacheKey = "api-cache:$hash";

        $this->assertTrue(Cache::has($cacheKey));
        $this->assertEquals([$cacheKey], Cache::get('api-cache:tracked-responses'));

        // manually manipulate whats in the cache so we can be sure it uses it.
        Cache::put($cacheKey, response()->json(['foo' => 'bar']), 10);

        $this->get($endpoint)
            ->assertOk()
            ->assertJson(['foo' => 'bar']);
    }

    #[Test]
    #[DataProvider('bypassCacheProvider')]
    public function it_bypasses_cache_when_using_a_valid_token($endpoint, $headers)
    {
        optional(Token::find('test-token'))->delete(); // garbage collection
        Token::make('test-token', TestTokenHandler::class)->save();

        $this->makeEntry('apple')->save();

        $this->get($endpoint, $headers)
            ->assertOk()
            ->assertJson(['data' => [
                ['id' => 'apple'],
            ]]);

        $hash = md5($endpoint);
        $this->assertFalse(Cache::has("api-cache:$hash"));
        $this->assertNull(Cache::get('api-cache:tracked-responses'));
    }

    #[Test]
    #[DataProvider('bypassCacheProvider')]
    public function it_doesnt_bypass_cache_when_using_an_invalid_token($endpoint, $headers)
    {
        // No token should exist, but do garbage collection.
        // There may be a leftover token from a previous test.
        optional(Token::find('test-token'))->delete();

        $this->makeEntry('apple')->save();

        $this->get($endpoint, $headers)
            ->assertOk()
            ->assertJson(['data' => [
                ['id' => 'apple'],
            ]]);

        $hash = md5($endpoint);
        $this->assertTrue(Cache::has($cacheKey = "api-cache:$hash"));
        $this->assertEquals([$cacheKey], Cache::get('api-cache:tracked-responses'));
    }

    public static function bypassCacheProvider()
    {
        $endpoint = '/api/collections/articles/entries';

        return [
            [$endpoint.'?token=test-token', []],
            [$endpoint, ['X-Statamic-Token' => 'test-token']],
        ];
    }

    #[Test]
    #[DefineEnvironment('setCustomExpiry')]
    public function it_caches_endpoint_using_configured_expiry()
    {
        $this->makeEntry('apple')->save();

        $endpoint = '/api/collections/articles/entries';
        $hash = md5($endpoint);
        $cacheKey = "api-cache:$hash";

        Carbon::setTestNow(now());

        $this->get($endpoint)->assertOk();

        $this->assertTrue(Cache::has($cacheKey));

        Carbon::setTestNow(now()->addMinutes(14));

        $this->assertFalse(Cache::has($cacheKey));
    }

    #[Test]
    public function it_caches_endpoint_with_query_params()
    {
        $this->makeEntry('apple')->save();

        $this->get($endpoint = '/api/collections/articles/entries?query=params')
            ->assertOk()
            ->assertJson(['data' => [
                ['id' => 'apple'],
            ]]);

        $hash = md5($endpoint);
        $cacheKey = "api-cache:$hash";

        $this->assertTrue(Cache::has($cacheKey));
        $this->assertEquals([$cacheKey], Cache::get('api-cache:tracked-responses'));
    }

    #[Test]
    public function it_caches_multiple_endpoints()
    {
        $this->makeEntry('apple')->save();

        $this->get($endpointOne = '/api/collections/articles/entries?query=one')
            ->assertOk()
            ->assertJson(['data' => [
                ['id' => 'apple'],
            ]]);

        $this->get($endpointTwo = '/api/collections/articles/entries?query=two')
            ->assertOk()
            ->assertJson(['data' => [
                ['id' => 'apple'],
            ]]);

        $hashOne = md5($endpointOne);
        $hashTwo = md5($endpointTwo);
        $this->assertTrue(Cache::has("api-cache:$hashOne"));
        $this->assertTrue(Cache::has("api-cache:$hashTwo"));

        $cachedResponses = [
            "api-cache:$hashOne",
            "api-cache:$hashTwo",
        ];

        $this->assertEquals($cachedResponses, Cache::get('api-cache:tracked-responses'));
    }

    #[Test]
    public function it_busts_whole_cache_when_content_is_saved()
    {
        $entry = $this->makeEntry('apple');
        $entry->save();

        $this->get($endpointOne = '/api/collections/articles/entries?query=one')
            ->assertOk()
            ->assertJson(['data' => [
                ['id' => 'apple'],
            ]]);

        $this->get($endpointTwo = '/api/collections/articles/entries?query=two')
            ->assertOk()
            ->assertJson(['data' => [
                ['id' => 'apple'],
            ]]);

        $hashOne = md5($endpointOne);
        $hashTwo = md5($endpointTwo);
        $this->assertTrue(Cache::has("api-cache:$hashOne"));
        $this->assertTrue(Cache::has("api-cache:$hashTwo"));
        $this->assertCount(2, Cache::get('api-cache:tracked-responses'));

        $entry->save();

        $this->assertFalse(Cache::has("api-cache:$hashOne"));
        $this->assertFalse(Cache::has("api-cache:$hashTwo"));
        $this->assertFalse(Cache::has('api-cache:tracked-responses'));
    }

    #[Test]
    public function it_busts_whole_cache_when_unrelated_content_is_saved()
    {
        $this->makeEntry('apple')->save();

        $this->get($endpointOne = '/api/collections/articles/entries?query=one')
            ->assertOk()
            ->assertJson(['data' => [
                ['id' => 'apple'],
            ]]);

        $this->get($endpointTwo = '/api/collections/articles/entries?query=two')
            ->assertOk()
            ->assertJson(['data' => [
                ['id' => 'apple'],
            ]]);

        $hashOne = md5($endpointOne);
        $hashTwo = md5($endpointTwo);

        $this->assertTrue(Cache::has("api-cache:$hashOne"));
        $this->assertTrue(Cache::has("api-cache:$hashTwo"));
        $this->assertCount(2, Cache::get('api-cache:tracked-responses'));

        Facades\Form::make('contact')->save();

        $this->assertFalse(Cache::has("api-cache:$hashOne"));
        $this->assertFalse(Cache::has("api-cache:$hashTwo"));
        $this->assertFalse(Cache::has('api-cache:tracked-responses'));
    }

    #[Test]
    public function it_can_disable_default_cacher_by_setting_false_on_parent_cache_config()
    {
        Facades\Config::set('statamic.api.cache', false);

        $this->makeEntry('apple')->save();

        $this->get($endpoint = '/api/collections/articles/entries')
            ->assertOk()
            ->assertJson(['data' => [
                ['id' => 'apple'],
            ]]);

        $hash = md5($endpoint);
        $cacheKey = "api-cache:$hash";

        $this->assertFalse(Cache::has($cacheKey));
        $this->assertFalse(Cache::has('api-cache:tracked-responses'));
    }

    #[Test]
    public function it_can_disable_default_cacher_by_setting_false_on_child_class_config()
    {
        Facades\Config::set('statamic.api.cache.class', false);

        $this->makeEntry('apple')->save();

        $this->get($endpoint = '/api/collections/articles/entries')
            ->assertOk()
            ->assertJson(['data' => [
                ['id' => 'apple'],
            ]]);

        $hash = md5($endpoint);
        $cacheKey = "api-cache:$hash";

        $this->assertFalse(Cache::has($cacheKey));
        $this->assertFalse(Cache::has('api-cache:tracked-responses'));
    }

    #[Test]
    public function it_can_use_custom_cacher()
    {
        Facades\Config::set('statamic.api.cache.class', CustomCacher::class);

        $entry = $this->makeEntry('apple');
        $entry->save();

        $this->get($endpoint = '/api/collections/articles/entries')
            ->assertOk()
            ->assertJson(['data' => [
                ['id' => 'apple'],
            ]]);

        $hash = md5($endpoint);
        $cacheKey = "api-cache:$hash";

        $this->assertFalse(Cache::has($cacheKey));
        $this->assertFalse(Cache::has('api-cache:tracked-responses'));
        $this->assertTrue(Cache::has('custom-cache'));

        Facades\Form::make('contact')->save();

        $this->assertTrue(Cache::has('custom-cache'));

        $entry->save();

        $this->assertFalse(Cache::has('custom-cache'));
    }

    protected function setCustomExpiry($app)
    {
        $app->config->set('statamic.api.cache.expiry', 13);
    }
}

class CustomCacher extends AbstractCacher
{
    public function get(Request $request)
    {
        return Cache::get('custom-cache');
    }

    public function put(Request $request, JsonResponse $response)
    {
        return Cache::put('custom-cache', $response, 1);
    }

    public function handleInvalidationEvent(Event $event)
    {
        if (! $event instanceof EntrySaved) {
            return;
        }

        Cache::forget('custom-cache');
    }
}

class TestTokenHandler
{
    public function handle($token, $request, $next)
    {
        return $next($request);
    }
}
