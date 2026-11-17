<?php

namespace Tests\View\Instrumentation;

use Statamic\Contracts\View\Antlers\Parser as ParserContract;
use Statamic\Facades\Antlers;
use Statamic\Tags\Tags;
use Statamic\View\Antlers\Language\Facades\Runtime;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Statamic\View\Debugbar\AntlersProfiler\PerformanceTracer;
use Statamic\View\Instrumentation\HtmlInstrumentation;
use Statamic\View\Instrumentation\InstrumentationManager;
use Tests\Antlers\ParserTestCase;
use Tests\FakesViews;
use Tests\View\Instrumentation\Concerns\InteractsWithRuntimeConfigurations;
use Tests\View\Instrumentation\Support\RecordingLegacyTracer;

class InstrumentationConfigurationTest extends ParserTestCase
{
    use FakesViews, InteractsWithRuntimeConfigurations;

    protected function setUp(): void
    {
        parent::setUp();

        GlobalRuntimeState::$isEvaluatingUserData = false;
    }

    public function test_marker_configuration_covers_normal_renders_and_nested_views()
    {
        config(['statamic.antlers.instrumentation' => [
            'enabled' => true,
            'comments' => true,
            'prefix' => 'trace',
            'attributes' => ['data-trace'],
            'metadata' => ['app' => 'demo'],
            'componentPrefixes' => ['ui-'],
        ]]);

        $this->withFakeViews();
        $this->viewShouldReturnRaw('marker-prefixed', '<ui-box>{{ title }}</ui-box>');
        $this->assertSame('<ui-box>Hello</ui-box>', view('marker-prefixed', ['title' => 'Hello'])->render());

        $this->viewShouldReturnRaw('marker-child', '<strong>{{ title }}</strong>');
        $this->viewShouldReturnRaw('marker-page', '<main><a href="{{ url }}">{{ title }}</a>{{ partial:marker-child }}</main>');

        $data = ['title' => 'Hello', 'url' => '/example'];
        $rendered = view('marker-page', $data)->render();
        $this->assertStringContainsString('<!-- trace:start ', $rendered);
        $this->assertStringContainsString('href="/example"', $rendered);
        $this->assertMatchesRegularExpression('/<strong data-trace="[^"]+"><!-- trace:start .*? -->Hello<!-- trace:end \d+ --><\/strong>/', $rendered);
        $this->assertSame('demo', HtmlInstrumentation::decode($rendered)['app']);
        $this->assertSame($rendered, view('marker-page', $data)->render());
    }

    public function test_multiple_instrumenters_keep_authored_antlers_coordinates()
    {
        $observed = [];
        Runtime::register(HtmlInstrumentation::make()->markersUsing(function ($metadata) use (&$observed) {
            $observed[] = $metadata;

            return ["<!-- custom\nstart -->", '<!-- custom end -->'];
        })->attributes('data-first'));
        Runtime::register(HtmlInstrumentation::make()->comments('second')->attributes('data-second'));
        $source = "<main>é\r\n<p>{{ title }}</p></main>";

        for ($render = 0; $render < 2; $render++) {
            $output = (string) app(ParserContract::class)->parse($source, ['title' => 'Hello']);
            preg_match_all('/<!-- second:start (.*?) -->/', $output, $matches);
            $metadata = array_map([HtmlInstrumentation::class, 'decode'], $matches[1]);
            $this->assertCount(1, $metadata);

            $entry = $metadata[0];
            $this->assertSame(mb_strpos($source, '{{ title }}'), $entry['start']);
            $this->assertSame(mb_strpos($source, '{{ title }}') + 11, $entry['end']);
            $this->assertSame(2, $entry['line']);
            $first = collect($observed)->firstWhere('expression', 'title');
            $this->assertSame($first['start'], $entry['start']);
            $this->assertSame($first['end'], $entry['end']);
            $this->assertStringContainsString('data-first=', $output);
            $this->assertStringContainsString('data-second=', $output);
        }
    }

    public function test_marker_positions_account_for_view_front_matter()
    {
        config(['statamic.antlers.instrumentation' => ['enabled' => true, 'comments' => true, 'prefix' => 'trace']]);
        $this->app['statamic.tags']['string_template'] = StringTemplateTag::class;

        $directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'statamic-instrumentation-'.uniqid();
        mkdir($directory);
        $path = $directory.DIRECTORY_SEPARATOR.'front-matter-page.antlers.html';
        $contents = "---\ntitle: Héllo\n---\n\n<p>{{ title }}</p><div>{{ string_template }}</div><i>{{ snippet | antlers }}</i>";
        file_put_contents($path, $contents);
        $this->app['view']->addLocation($directory);

        try {
            $rendered = view('front-matter-page', ['title' => 'Hello', 'snippet' => '<b>{{ title }}</b>'])->render();
        } finally {
            unlink($path);
            rmdir($directory);
        }

        preg_match_all('/<!-- trace:start (.*?) -->/', $rendered, $matches);
        [$metadata, $tag, $snippet, $modifier] = array_map([HtmlInstrumentation::class, 'decode'], $matches[1]);

        $this->assertStringContainsString('Hello<!-- trace:end 1 --></p>', $rendered);
        $this->assertSame(5, $metadata['line']);
        $this->assertSame(mb_strpos($contents, '{{ title }}'), $metadata['start']);
        $this->assertSame(mb_strpos($contents, '{{ title }}') + 11, $metadata['end']);
        $this->assertSame('front-matter-page.antlers.html', $metadata['view']);
        $this->assertSame('front-matter-page.antlers.html', $tag['view']);
        $this->assertSame(['title', 1, 3], [$snippet['expression'], $snippet['line'], $snippet['start']]);
        $this->assertArrayNotHasKey('view', $snippet);
        $this->assertSame('snippet | antlers', $modifier['expression']);
        $this->assertMatchesRegularExpression('/<i><!-- trace:start [^ ]+ --><b>Hello<\/b><!-- trace:end \d+ --><\/i>/', $rendered);
    }

    public function test_configuration_changes_apply_to_subsequent_parsers()
    {
        $this->app->forgetInstance(InstrumentationManager::class);

        config(['statamic.antlers.instrumentation' => [
            'enabled' => true,
            'comments' => true,
            'prefix' => 'first',
        ]]);

        $this->app->make(ParserContract::class);

        config(['statamic.antlers.instrumentation' => [
            'enabled' => true,
            'comments' => true,
            'prefix' => 'second',
        ]]);

        $rendered = (string) $this->app->make(ParserContract::class)
            ->parse('<p>{{ title }}</p>', ['title' => 'Hello']);

        $this->assertCount(1, $this->runtimeConfiguration()->getPreparsers());
        $this->assertSame(1, substr_count($rendered, '<!-- second:start'));
        $this->assertStringNotContainsString('first:start', $rendered);

        config(['statamic.antlers.instrumentation.enabled' => false]);

        $rendered = (string) $this->app->make(ParserContract::class)
            ->parse('<p>{{ title }}</p>', ['title' => 'Hello']);

        $this->assertSame('<p>Hello</p>', $rendered);
    }

    public function test_withdrawing_the_profiler_tracer_leaves_user_tracers_registered()
    {
        $userTracer = new RecordingLegacyTracer;
        Runtime::register($userTracer, ['antlers']);

        $this->app->instance('debugbar', new InstrumentationConfigurationTestDebugbar);
        $this->app->make(ParserContract::class);

        config(['statamic.antlers.debugbar' => false]);
        $this->app->make(ParserContract::class);

        $config = $this->runtimeConfiguration();

        $this->assertNotContains($this->app->make(PerformanceTracer::class), $config->traceManager->tracers());
        $this->assertContains($userTracer, $config->traceManager->tracers());
        $this->assertTrue($config->isTracingEnabled);
    }
}

class InstrumentationConfigurationTestDebugbar
{
    public function isEnabled()
    {
        return true;
    }
}

class StringTemplateTag extends Tags
{
    public function index()
    {
        return Antlers::parse('<b>{{ title }}</b>', $this->context->all(), true);
    }
}
