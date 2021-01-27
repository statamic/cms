<?php

namespace Tests\API;

use Closure;
use Facades\Tests\Factories\EntryFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Statamic\API\AbstractCacher;
use Statamic\Events\EntrySaved;
use Statamic\Events\Event;
use Statamic\Facades;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class CacherTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        $defaultConfig = include __DIR__.'/../../config/api.php';

        Facades\Config::set('statamic.api', $defaultConfig);
        Facades\Config::set('statamic.api.enabled', true);

        $this->collection = Facades\Collection::make('articles')->save();
    }

    protected function makeEntry($slug)
    {
        return EntryFactory::id($slug)->slug($slug)->collection($this->collection)->make();
    }

    /** @test */
    public function it_caches_endpoint_using_default_cacher()
    {
        $this->makeEntry('apple')->save();

        $this->get($endpoint = '/api/collections/articles/entries')
            ->assertOk()
            ->assertJson(['data' => [
                ['id' => 'apple'],
            ]]);

        $cacheKey = "api-cache:$endpoint";

        $this->assertTrue(Cache::has($cacheKey));
        $this->assertEquals([$cacheKey], Cache::get('api-cache:tracked-responses'));
    }

    /** @test */
    public function it_caches_endpoint_with_query_params()
    {
        $this->makeEntry('apple')->save();

        $this->get($endpoint = '/api/collections/articles/entries?query=params')
            ->assertOk()
            ->assertJson(['data' => [
                ['id' => 'apple'],
            ]]);

        $cacheKey = "api-cache:$endpoint";

        $this->assertTrue(Cache::has($cacheKey));
        $this->assertEquals([$cacheKey], Cache::get('api-cache:tracked-responses'));
    }

    /** @test */
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

        $this->assertTrue(Cache::has("api-cache:$endpointOne"));
        $this->assertTrue(Cache::has("api-cache:$endpointTwo"));

        $cachedResponses = [
            "api-cache:$endpointOne",
            "api-cache:$endpointTwo",
        ];

        $this->assertEquals($cachedResponses, Cache::get('api-cache:tracked-responses'));
    }

    /** @test */
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

        $this->assertTrue(Cache::has("api-cache:$endpointOne"));
        $this->assertTrue(Cache::has("api-cache:$endpointTwo"));
        $this->assertCount(2, Cache::get('api-cache:tracked-responses'));

        $entry->save();

        $this->assertFalse(Cache::has("api-cache:$endpointOne"));
        $this->assertFalse(Cache::has("api-cache:$endpointTwo"));
        $this->assertFalse(Cache::has('api-cache:tracked-responses'));
    }

    /** @test */
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

        $this->assertTrue(Cache::has("api-cache:$endpointOne"));
        $this->assertTrue(Cache::has("api-cache:$endpointTwo"));
        $this->assertCount(2, Cache::get('api-cache:tracked-responses'));

        Facades\Form::make('contact')->save();

        $this->assertFalse(Cache::has("api-cache:$endpointOne"));
        $this->assertFalse(Cache::has("api-cache:$endpointTwo"));
        $this->assertFalse(Cache::has('api-cache:tracked-responses'));
    }

    /** @test */
    public function it_can_disable_default_cacher_by_setting_false_on_parent_cache_config()
    {
        Facades\Config::set('statamic.api.cache', false);

        $this->makeEntry('apple')->save();

        $this->get($endpoint = '/api/collections/articles/entries')
            ->assertOk()
            ->assertJson(['data' => [
                ['id' => 'apple'],
            ]]);

        $cacheKey = "api-cache:$endpoint";

        $this->assertFalse(Cache::has($cacheKey));
        $this->assertFalse(Cache::has('api-cache:tracked-responses'));
    }

    /** @test */
    public function it_can_disable_default_cacher_by_setting_false_on_child_class_config()
    {
        Facades\Config::set('statamic.api.cache.class', false);

        $this->makeEntry('apple')->save();

        $this->get($endpoint = '/api/collections/articles/entries')
            ->assertOk()
            ->assertJson(['data' => [
                ['id' => 'apple'],
            ]]);

        $cacheKey = "api-cache:$endpoint";

        $this->assertFalse(Cache::has($cacheKey));
        $this->assertFalse(Cache::has('api-cache:tracked-responses'));
    }

    /** @test */
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

        $cacheKey = "api-cache:$endpoint";

        $this->assertFalse(Cache::has($cacheKey));
        $this->assertFalse(Cache::has('api-cache:tracked-responses'));
        $this->assertTrue(Cache::has('custom-cache'));

        Facades\Form::make('contact')->save();

        $this->assertTrue(Cache::has('custom-cache'));

        $entry->save();

        $this->assertFalse(Cache::has('custom-cache'));
    }
}

class CustomCacher extends AbstractCacher
{
    public function remember(Request $request, Closure $callback)
    {
        return Cache::remember('custom-cache', 1, $callback);
    }

    public function handleInvalidationEvent(Event $event)
    {
        if (! $event instanceof EntrySaved) {
            return;
        }

        Cache::forget('custom-cache');
    }
}
