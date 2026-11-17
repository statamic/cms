<?php

namespace Tests\StaticCaching;

use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Statamic\StaticCaching\Cacher;
use Statamic\View\Antlers\Language\Facades\Runtime;
use Statamic\View\Instrumentation\HtmlInstrumentation;
use Tests\FakesContent;
use Tests\FakesViews;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class InstrumentationBypassTest extends TestCase
{
    use FakesContent;
    use FakesViews;
    use PreventSavingStacheItemsToDisk;

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('cache.default', 'file');
        $app['config']->set('statamic.static_caching.strategy', 'half');
    }

    #[Test]
    public function instrumented_pages_are_neither_cached_nor_served_from_the_cache()
    {
        $this->withStandardFakeViews();
        $this->viewShouldReturnRaw('default', '<h1>{{ title }}</h1>');

        $page = $this->createPage('about', ['with' => ['title' => 'First']]);

        $this->get('/about')->assertOk()->assertSee('<h1>First</h1>', false);
        $this->assertTrue(app(Cacher::class)->hasCachedPage(Request::create('/about')));

        Runtime::register(HtmlInstrumentation::make());

        $page->set('title', 'Second')->saveQuietly();

        $content = $this->get('/about')->assertOk()->getContent();

        $this->assertStringContainsString('<h1><!-- antlers:start ', $content);
        $this->assertStringContainsString('Second', $content);
        $this->assertStringNotContainsString('First', $content);

        $page->set('title', 'Third')->saveQuietly();

        $this->get('/about')->assertOk()->assertSee('Third')->assertDontSee('Second');

        Runtime::flush();

        $this->get('/about')->assertOk()->assertSee('<h1>First</h1>', false);
    }
}
