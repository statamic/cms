<?php

namespace Tests\Tags;

use Illuminate\Cache\Events\CacheHit;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Cache\Events\KeyWritten;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Cascade;
use Statamic\Facades\Config;
use Statamic\Facades\Parse;
use Statamic\Facades\Site;
use Statamic\Facades\URL;
use Statamic\Facades\User;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class CacheTagTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_caches_its_contents_the_first_time()
    {
        $template = '{{ cache }}expensive{{ /cache }}';

        Event::fake();

        $this->assertEquals('expensive', $this->tag($template));

        $this->assertMissed();
    }

    #[Test]
    public function it_can_use_a_custom_cache_store()
    {
        config()->set('cache.stores.statamic', ['driver' => 'array']);

        $template = '{{ cache store="statamic" }}expensive{{ /cache }}';

        Event::fake();

        $this->assertEquals('expensive', $this->tag($template));

        $this->assertMissed();
    }

    #[Test]
    public function it_skips_the_cache_if_cache_tags_are_not_enabled()
    {
        Config::set('statamic.system.cache_tags_enabled', false);

        Cache::shouldReceive('store')->never();

        $this->assertEquals('expensive', $this->tag('{{ cache }}expensive{{ /cache }}'));
    }

    #[Test]
    public function it_uses_the_cached_content_for_the_same_user_when_using_scope_user()
    {
        $template = '{{ cache scope="user" }}expensive{{ /cache }}';

        $this->actingAs(tap(User::make()->id('user1'))->save());

        $original = Event::getFacadeRoot();
        Event::fake();

        $this->tag($template);

        $this->assertMissed();

        Event::swap($original);
        Event::fake();

        $this->tag($template);

        $this->assertHit();
    }

    #[Test]
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

    #[Test]
    public function it_uses_the_cached_content_for_the_same_page_when_using_scope_page()
    {
        $template = '{{ cache scope="page" }}expensive{{ /cache }}';

        $mock = \Mockery::mock(URL::getFacadeRoot())->makePartial();
        URL::swap($mock);

        $mock->shouldReceive('getCurrent')->andReturn('/test/url');

        $original = Event::getFacadeRoot();
        Event::fake();

        $this->tag($template);

        $this->assertMissed();

        Event::swap($original);
        Event::fake();

        $this->tag($template);

        $this->assertHit();
    }

    #[Test]
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

    #[Test]
    public function it_restores_section_contents_after()
    {
        $mock = \Mockery::mock(URL::getFacadeRoot())->makePartial();
        URL::swap($mock);

        $mock->shouldReceive('getCurrent')->andReturn('/test/url');

        $template = <<<'EOT'
{{ yield:header }}

{{ cache }}
    {{ section:header }}The Header{{ /section:header }}
    
    Cached content.
{{ /cache }}
EOT;
        $expected = <<<'EXP'
The Header


    
    
    Cached content.
EXP;
        $original = Event::getFacadeRoot();
        Event::fake();

        $this->assertSame($expected, trim($this->tag($template)));
        $this->assertMissed('Cached content.');

        Event::swap($original);
        Event::fake();

        view()->shared('__env')->flushSections();
        Cascade::instance()->clearSections();

        $this->assertSame($expected, trim($this->tag($template)));
        $this->assertHit('Cached content.');
    }

    #[Test]
    public function it_restores_stack_contents_after()
    {
        $mock = \Mockery::mock(URL::getFacadeRoot())->makePartial();
        URL::swap($mock);

        $mock->shouldReceive('getCurrent')->andReturn('/test/url');

        $template = <<<'EOT'
{{ stack:test }}{{ value }}{{ /stack:test }}

{{ cache }}
    {{ push:test }}Hello{{ /push:test }}{{ push:test }}, universe.{{ /push:test }}
    
    Cached content.
{{ /cache }}

{{ push:test }}The End.{{ /push:test }}{{ prepend:test }}The Beginning.{{ /prepend:test }}
EOT;
        $expected = <<<'EXP'
The Beginning.Hello, universe.The End.


    
    
    Cached content.
EXP;
        $original = Event::getFacadeRoot();
        Event::fake();

        $this->assertSame($expected, trim($this->tag($template)));
        $this->assertMissed('Cached content.');

        Event::swap($original);
        Event::fake();

        view()->shared('__env')->flushSections();
        Cascade::instance()->clearSections();

        $this->assertSame($expected, trim($this->tag($template)));
        $this->assertHit('Cached content.');
    }

    #[Test]
    public function it_uses_the_cached_content_for_the_same_site_when_using_scope_site()
    {
        $template = '{{ cache scope="site" }}expensive{{ /cache }}';

        $this->setSites([
            'default' => [],
            'other' => [],
        ]);

        Site::setCurrent('default');

        $original = Event::getFacadeRoot();
        Event::fake();

        $this->tag($template);

        $this->assertMissed();

        Event::swap($original);
        Event::fake();

        $this->tag($template);

        $this->assertHit();
    }

    #[Test]
    public function it_does_not_use_the_cached_content_for_a_different_site_when_using_scope_site()
    {
        $template = '{{ cache scope="site" }}expensive{{ /cache }}';

        $this->setSites([
            'default' => [],
            'other' => [],
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

    #[Test]
    public function it_uses_the_cached_content_for_a_different_site_when_using_scope_global()
    {
        $template = '{{ cache scope="global" }}expensive{{ /cache }}';

        $this->mock(Sites::class)->shouldReceive('getCurrent')->andReturn('default');

        $original = Event::getFacadeRoot();
        Event::fake();

        $this->tag($template);

        $this->assertMissed();

        $this->mock(Sites::class)->shouldReceive('getCurrent')->andReturn('other');

        Event::swap($original);
        Event::fake();

        $this->tag($template);

        $this->assertHit();
    }

    private function assertHit($contentValue = 'expensive')
    {
        Event::assertDispatched(CacheHit::class, function ($event) use ($contentValue) {
            if (is_string($event->value)) {
                return trim($event->value) === $contentValue;
            }

            return $event->value === $contentValue;
        });
        Event::assertNotDispatched(CacheMissed::class, function (CacheMissed $e) {
            return ! Str::endsWith($e->key, '_sections_stacks');
        });
        Event::assertNotDispatched(KeyWritten::class);
    }

    private function assertMissed($contentValue = 'expensive')
    {
        Event::assertNotDispatched(CacheHit::class);
        Event::assertDispatched(CacheMissed::class);
        Event::assertDispatched(KeyWritten::class, function ($event) use ($contentValue) {
            if (is_string($event->value)) {
                return trim($event->value) === $contentValue;
            }

            return $event->value === $contentValue;
        });
    }

    private function tag($tag, $data = [])
    {
        GlobalRuntimeState::resetGlobalState();

        return (string) Parse::template($tag, $data);
    }
}
