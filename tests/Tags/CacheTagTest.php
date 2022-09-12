<?php

namespace Tests\Tags;

use Illuminate\Support\Facades\Cache;
use Mockery;
use Statamic\Facades\Config;
use Statamic\Facades\Endpoint\URL;
use Statamic\Facades\Parse;
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

        Cache::shouldReceive('store')->andReturn(
            $this->mock(\Illuminate\Contracts\Cache\Store::class)
                ->shouldReceive('get')->once()->andReturn(null)
                ->shouldReceive('put')->once()->with(Mockery::any(), 'expensive', Mockery::any())
                ->getMock()
        );
        $this->assertEquals('expensive', $this->tag($template));
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

        $this->tag($template);

        Cache::shouldReceive('store')->andReturn(
            $this->mock(\Illuminate\Contracts\Cache\Store::class)
                ->shouldReceive('get')->once()->andReturn('expensive')
                ->getMock()
        );

        $this->tag($template);
    }

    /** @test */
    public function it_does_not_use_the_cached_content_for_a_different_user_when_using_scope_user()
    {
        $template = '{{ cache scope="user" }}expensive{{ /cache }}';

        $this->actingAs(tap(User::make()->id('user1'))->save());

        $this->tag($template);

        $this->actingAs(tap(User::make()->id('user2'))->save());

        Cache::shouldReceive('store')->andReturn(
            $this->mock(\Illuminate\Contracts\Cache\Store::class)
                ->shouldReceive('get')->once()->andReturn(null)
                ->shouldReceive('put')->once()->with(Mockery::any(), 'expensive', Mockery::any())
                ->getMock()
        );

        $this->tag($template);
    }

    /** @test */
    public function it_uses_the_cached_content_for_the_same_page_when_using_scope_page()
    {
        $template = '{{ cache scope="page" }}expensive{{ /cache }}';

        $this->mock(URL::class)->shouldReceive('getCurrent')->andReturn('/test/url');

        $this->tag($template);

        Cache::shouldReceive('store')->andReturn(
            $this->mock(\Illuminate\Contracts\Cache\Store::class)
                ->shouldReceive('get')->once()->andReturn('expensive')
                ->getMock()
        );

        $this->tag($template);
    }

    /** @test */
    public function it_does_not_use_the_cached_content_for_a_different_page_when_using_scope_page()
    {
        $template = '{{ cache scope="page" }}expensive{{ /cache }}';

        $this->mock(URL::class)->shouldReceive('getCurrent')->andReturn('/test/url');

        $this->tag($template);

        $this->mock(URL::class)->shouldReceive('getCurrent')->andReturn('/some/other/url');

        Cache::shouldReceive('store')->andReturn(
            $this->mock(\Illuminate\Contracts\Cache\Store::class)
                ->shouldReceive('get')->once()->andReturn(null)
                ->shouldReceive('put')->once()->with(Mockery::any(), 'expensive', Mockery::any())
                ->getMock()
        );

        $this->tag($template);
    }

    /** @test */
    public function it_uses_the_cached_content_for_the_same_site_when_using_scope_site()
    {
        $template = '{{ cache scope="site" }}expensive{{ /cache }}';

        $this->mock(Sites::class)->shouldReceive('getCurrent')->andReturn('default');

        $this->tag($template);

        Cache::shouldReceive('store')->andReturn(
            $this->mock(\Illuminate\Contracts\Cache\Store::class)
                ->shouldReceive('get')->once()->andReturn('expensive')
                ->getMock()
        );

        $this->tag($template);
    }

    /** @test */
    public function it_does_not_use_the_cached_content_for_a_different_site_when_using_scope_site()
    {
        $template = '{{ cache scope="site" }}expensive{{ /cache }}';

        $this->mock(Sites::class)->shouldReceive('getCurrent')->andReturn('default');

        $this->tag($template);

        $this->mock(Sites::class)->shouldReceive('getCurrent')->andReturn('other');

        Cache::shouldReceive('store')->andReturn(
            $this->mock(\Illuminate\Contracts\Cache\Store::class)
                ->shouldReceive('get')->once()->andReturn(null)
                ->shouldReceive('put')->once()->with(Mockery::any(), 'expensive', Mockery::any())
                ->getMock()
        );

        $this->tag($template);
    }

    /** @test */
    public function it_uses_the_cached_content_for_a_different_site_when_using_scope_global()
    {
        $template = '{{ cache scope="global" }}expensive{{ /cache }}';

        $this->mock(Sites::class)->shouldReceive('getCurrent')->andReturn('default');

        $this->tag($template);

        $this->mock(Sites::class)->shouldReceive('getCurrent')->andReturn('other');

        Cache::shouldReceive('store')->andReturn(
            $this->mock(\Illuminate\Contracts\Cache\Store::class)
                ->shouldReceive('get')->once()->andReturn('expensive')
                ->getMock()
        );

        $this->tag($template);
    }

    // Site::setConfig(['sites' => [
    //     'en' => ['url' => '/'],
    //     'fr' => ['url' => '/fr'],
    // ]]);

    private function tag($tag, $data = [])
    {
        return (string) Parse::template($tag, $data);
    }
}
