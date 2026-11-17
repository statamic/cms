<?php

namespace Tests\View\Instrumentation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Statamic\StaticCaching\Cacher;
use Statamic\StaticCaching\NoCache\StringFragment;
use Statamic\View\Antlers\Language\Facades\Runtime;
use Statamic\View\Instrumentation\HtmlInstrumentation;
use Tests\FakesContent;
use Tests\FakesViews;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;
use Tests\View\Instrumentation\Concerns\DecodesMarkers;

class NocacheInstrumentationTest extends TestCase
{
    use DecodesMarkers, FakesContent, FakesViews, PreventSavingStacheItemsToDisk;

    const FIRST = '<main><p>Existing</p><b>Existing</b></main>';

    const SECOND = '<main><p>Existing</p><b>Updated</b></main>';

    const UNCACHED_SECOND = '<main><p>Updated</p><b>Updated</b></main>';

    private string $slug = '';

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('cache.default', 'file');
        $app['config']->set('statamic.static_caching.strategy', 'half');
    }

    public function test_nocache_regions_render_their_captured_content_without_reinstrumenting_it()
    {
        Runtime::register(HtmlInstrumentation::make());

        [$first, $second] = $this->renderTwice();

        $this->assertFalse(app(Cacher::class)->hasCachedPage(Request::create('/'.$this->slug)));

        foreach ([[self::FIRST, $first], [self::UNCACHED_SECOND, $second]] as [$plain, $instrumented]) {
            $this->assertSame($plain, Runtime::removeMarkers($instrumented));
            $this->assertSame(1, substr_count($instrumented, '<b><!-- antlers:start '));

            $markers = collect($this->decodedMarkers($instrumented));

            $this->assertSame(2, $markers->where('expression', 'title')->count());
            $this->assertSame(['default.antlers.html'], $markers->where('expression', 'title')->pluck('view')->unique()->all());
            $this->assertSame([], $markers->pluck('view')->filter(fn ($view) => str_contains((string) $view, 'nocache'))->all());
        }
    }

    public function test_string_fragments_keep_their_captured_markers_without_adding_new_ones()
    {
        $instrumenter = HtmlInstrumentation::make();
        Runtime::register($instrumenter);
        $captured = $instrumenter('<p>{{ title }}</p>');

        $this->assertSame(['title'], $this->markerExpressions($captured));

        $fragment = fn (string $contents) => (new StringFragment('fragment', $contents, 'antlers.html', ['title' => 'Hello']))->render();

        $this->assertSame('<p>Hello</p>', $fragment('<p>{{ title }}</p>'));
        $this->assertSame(str_replace('{{ title }}', 'Hello', $captured), $fragment($captured));
        $this->assertSame(['title'], $this->markerExpressions($fragment($captured)));
    }

    private function renderTwice(): array
    {
        Cache::flush();
        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('default', '<main><p>{{ title }}</p>{{ nocache }}<b>{{ title }}</b>{{ /nocache }}</main>');

        $this->slug = 'page-'.Str::lower(Str::random(8));
        $page = $this->createPage($this->slug, ['with' => ['title' => 'Existing']]);

        $first = $this->get('/'.$this->slug)->assertOk()->content();

        $page->set('title', 'Updated')->saveQuietly();

        $second = $this->get('/'.$this->slug)->assertOk()->content();

        return [trim($first), trim($second)];
    }
}
