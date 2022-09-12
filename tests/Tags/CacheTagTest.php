<?php

namespace Tests\Tags;

use Illuminate\Cache\Events\CacheHit;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Cache\Events\KeyWritten;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Statamic\Facades\Config;
use Statamic\Facades\Parse;
use Statamic\Facades\Site;
use Statamic\Facades\URL;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class CacheTagTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_caches_its_contents_the_first_time()
    {
        $template = '{{ cache }}expensive{{ /cache }}';

        Event::fake();

        $this->assertEquals('expensive', $this->tag($template));

        $this->assertMissed();
    }

    /** @test */
    public function it_skips_the_cache_if_cache_tags_are_not_enabled()
    {
        Config::set('statamic.system.cache_tags_enabled', false);

        Cache::shouldReceive('store')->never();

        $this->assertEquals('expensive', $this->tag('{{ cache }}expensive{{ /cache }}'));
    }

    /** @test */
    public function it_uses_the_cached_content_for_the_same_user_when_using_scope_user()
    {
        $template = '{{ cache scope="user" }}expensive{{ /cache }}';

        $this->actingAs(tap(User::make()->id('user1'))->save());

        Event::fake();

        $this->tag($template);

        $this->assertMissed();

        Event::fake();

        $this->tag($template);

        $this->assertHit();
    }

    /** @test */
    public function it_does_not_use_the_cached_content_for_a_different_user_when_using_scope_user()
    {
        $template = '{{ cache scope="user" }}expensive{{ /cache }}';

        $this->actingAs(tap(User::make()->id('user1'))->save());

        Event::fake();

        $this->tag($template);

        $this->assertMissed();

        $this->actingAs(tap(User::make()->id('user2'))->save());

        Event::fake();

        $this->tag($template);

        $this->assertMissed();
    }

    /** @test */
    public function it_uses_the_cached_content_for_the_same_page_when_using_scope_page()
    {
        $template = '{{ cache scope="page" }}expensive{{ /cache }}';

        $mock = \Mockery::mock(URL::getFacadeRoot())->makePartial();
        URL::swap($mock);

        $mock->shouldReceive('getCurrent')->andReturn('/test/url');

        Event::fake();

        $this->tag($template);

        $this->assertMissed();

        Event::fake();

        $this->tag($template);

        $this->assertHit();
    }

    /** @test */
    public function it_does_not_use_the_cached_content_for_a_different_page_when_using_scope_page()
    {
        $template = '{{ cache scope="page" }}expensive{{ /cache }}';

        $mock = \Mockery::mock(URL::getFacadeRoot())->makePartial();
        URL::swap($mock);

        $mock->shouldReceive('getCurrent')->once()->andReturn('/test/url');

        Event::fake();

        $this->tag($template);

        $this->assertMissed();

        $mock->shouldReceive('getCurrent')->once()->andReturn('/some/other/url');

        Event::fake();

        $this->tag($template);

        $this->assertMissed();
    }

    /** @test */
    public function it_uses_the_cached_content_for_the_same_site_when_using_scope_site()
    {
        $template = '{{ cache scope="site" }}expensive{{ /cache }}';

        Site::setConfig([
            'default' => 'default',
            'sites' => [
                'default' => [],
                'other' => [],
            ],
        ]);

        Site::setCurrent('default');

        Event::fake();

        $this->tag($template);

        $this->assertMissed();

        Event::fake();

        $this->tag($template);

        $this->assertHit();
    }

    /** @test */
    public function it_does_not_use_the_cached_content_for_a_different_site_when_using_scope_site()
    {
        $template = '{{ cache scope="site" }}expensive{{ /cache }}';

        Site::setConfig([
            'default' => 'default',
            'sites' => [
                'default' => [],
                'other' => [],
            ],
        ]);

        Site::setCurrent('default');

        Event::fake();

        $this->tag($template);

        $this->assertMissed();

        Site::setCurrent('other');

        Event::fake();

        $this->tag($template);

        $this->assertMissed();
    }

    /** @test */
    public function it_uses_the_cached_content_for_a_different_site_when_using_scope_global()
    {
        $template = '{{ cache scope="global" }}expensive{{ /cache }}';

        $this->mock(Sites::class)->shouldReceive('getCurrent')->andReturn('default');

        Event::fake();

        $this->tag($template);

        $this->assertMissed();

        $this->mock(Sites::class)->shouldReceive('getCurrent')->andReturn('other');

        Event::fake();

        $this->tag($template);

        $this->assertHit();
    }

    private function assertHit()
    {
        Event::assertDispatched(CacheHit::class, function ($event) {
            return $event->value === 'expensive';
        });
        Event::assertNotDispatched(CacheMissed::class);
        Event::assertNotDispatched(KeyWritten::class);
    }

    private function assertMissed()
    {
        Event::assertNotDispatched(CacheHit::class);
        Event::assertDispatched(CacheMissed::class);
        Event::assertDispatched(KeyWritten::class, function ($event) {
            return $event->value === 'expensive';
        });
    }

    private function tag($tag, $data = [])
    {
        return (string) Parse::template($tag, $data);
    }
}
