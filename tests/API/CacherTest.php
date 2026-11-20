<?php

namespace Tests\API;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Cache\Events\WritingKey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event as EventFacade;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\API\AbstractCacher;
use Statamic\API\Cachers\DefaultCacher;
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

        $cacheKey = $this->cacheKey($endpoint);

        $this->assertTrue(Cache::has($cacheKey));
        $this->assertEquals([$cacheKey], Cache::get('api-cache:tracked-responses'));

        // manually manipulate whats in the cache so we can be sure it uses it.
        Cache::put($cacheKey, [
            'content' => json_encode(['foo' => 'bar']),
            'status' => 200,
            'headers' => ['content-type' => ['application/json']],
        ], 10);

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

        $this->assertFalse(Cache::has($this->cacheKey($endpoint)));
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

        $this->assertTrue(Cache::has($cacheKey = $this->cacheKey($endpoint)));
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

        Carbon::setTestNow(now());

        $this->get($endpoint)->assertOk();

        $cacheKey = $this->cacheKey($endpoint);

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

        $cacheKey = $this->cacheKey($endpoint);

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

        $keyOne = $this->cacheKey($endpointOne);
        $keyTwo = $this->cacheKey($endpointTwo);
        $this->assertTrue(Cache::has($keyOne));
        $this->assertTrue(Cache::has($keyTwo));

        $cachedResponses = [$keyOne, $keyTwo];

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

        $keyOne = $this->cacheKey($endpointOne);
        $keyTwo = $this->cacheKey($endpointTwo);
        $this->assertTrue(Cache::has($keyOne));
        $this->assertTrue(Cache::has($keyTwo));
        $this->assertCount(2, Cache::get('api-cache:tracked-responses'));

        $entry->save();

        $this->assertFalse(Cache::has($keyOne));
        $this->assertFalse(Cache::has($keyTwo));
        $this->assertFalse(Cache::has('api-cache:generation'));
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

        $keyOne = $this->cacheKey($endpointOne);
        $keyTwo = $this->cacheKey($endpointTwo);

        $this->assertTrue(Cache::has($keyOne));
        $this->assertTrue(Cache::has($keyTwo));
        $this->assertCount(2, Cache::get('api-cache:tracked-responses'));

        Facades\Form::make('contact')->save();

        $this->assertFalse(Cache::has($keyOne));
        $this->assertFalse(Cache::has($keyTwo));
        $this->assertFalse(Cache::has('api-cache:generation'));
        $this->assertFalse(Cache::has('api-cache:tracked-responses'));
    }

    /**
     * @see https://github.com/statamic/cms/issues/15523
     */
    #[Test]
    public function it_busts_responses_cached_concurrently_when_content_is_saved()
    {
        $entry = $this->makeEntry('apple');
        $entry->save();

        $requestOne = Request::create('/api/collections/articles/entries?query=one');
        $requestTwo = Request::create('/api/collections/articles/entries?query=two');

        $workerOne = new DefaultCacher(['expiry' => 60]);
        $workerTwo = new DefaultCacher(['expiry' => 60]);

        $workerOne->get($requestOne);
        $workerTwo->get($requestTwo);

        $workerTwoIsWriting = false;

        EventFacade::listen(WritingKey::class, function () use (&$workerTwoIsWriting, $workerTwo, $requestTwo) {
            if ($workerTwoIsWriting) {
                return;
            }

            $workerTwoIsWriting = true;
            $workerTwo->put($requestTwo, new JsonResponse(['data' => 'two']));
        });

        $workerOne->put($requestOne, new JsonResponse(['data' => 'one']));

        $entry->save();

        $this->assertNull((new DefaultCacher(['expiry' => 60]))->get($requestOne));
        $this->assertNull((new DefaultCacher(['expiry' => 60]))->get($requestTwo));
    }

    /**
     * @see https://github.com/statamic/cms/issues/15523
     */
    #[Test]
    public function it_doesnt_cache_a_stale_response_when_content_is_saved_mid_request()
    {
        $entry = $this->makeEntry('apple');
        $entry->save();

        $request = Request::create('/api/collections/articles/entries');

        $cacher = new DefaultCacher(['expiry' => 60]);
        $cacher->get($request);

        $cacher->handleInvalidationEvent(new EntrySaved($entry));

        $cacher->put($request, new JsonResponse(['data' => 'stale']));

        $this->assertNull($cacher->get(Request::create('/api/collections/articles/entries')));
    }

    #[Test]
    public function a_long_lived_cacher_uses_the_current_generation_for_each_request()
    {
        $cacher = new DefaultCacher(['expiry' => 60]);

        $cacher->put(Request::create('/api/collections/articles/entries'), new JsonResponse(['data' => 'old']));

        Cache::forget('api-cache:generation');

        $this->assertNull($cacher->get(Request::create('/api/collections/articles/entries')));
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

        $this->assertFalse(Cache::has($this->cacheKey($endpoint)));
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

        $this->assertFalse(Cache::has($this->cacheKey($endpoint)));
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

        $this->assertFalse(Cache::has($this->cacheKey($endpoint)));
        $this->assertFalse(Cache::has('api-cache:tracked-responses'));
        $this->assertTrue(Cache::has('custom-cache'));

        Facades\Form::make('contact')->save();

        $this->assertTrue(Cache::has('custom-cache'));

        $entry->save();

        $this->assertFalse(Cache::has('custom-cache'));
    }

    private function cacheKey($endpoint)
    {
        return 'api-cache:'.Cache::get('api-cache:generation').':'.md5($endpoint);
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
