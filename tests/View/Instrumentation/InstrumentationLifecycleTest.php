<?php

namespace Tests\View\Instrumentation;

use Statamic\Contracts\View\Antlers\Parser as ParserContract;
use Statamic\Tags\Loader;
use Statamic\Tags\Tags;
use Statamic\View\Antlers\Language\Facades\Runtime;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Tests\Antlers\ParserTestCase;
use Tests\FakesViews;

class InstrumentationLifecycleTest extends ParserTestCase
{
    use FakesViews;

    const PLAIN = '<div><p>[12][eq]</p></div>';

    protected function setUp(): void
    {
        parent::setUp();

        GlobalRuntimeState::$isEvaluatingUserData = false;

        $this->app['statamic.tags']['lifecycle_spy'] = LifecycleSpyTag::class;
        $this->app['statamic.tags']['delegate'] = DelegatingIncludeTag::class;
        LifecycleSpyTag::$count = 0;

        $this->withFakeViews();
        $this->viewShouldReturnRaw('card', '<p>[{{ slot | length }}][{{ if slot == "<b>Hello</b>" }}eq{{ else }}ne{{ /if }}]</p>');
        $this->viewShouldReturnRaw('page', '<div>{{ partial:card }}<b>{{ title }}</b>{{ /partial:card }}</div>');
    }

    private function render(): string
    {
        return view('page', ['title' => 'Hello'])->render();
    }

    private function enable(string $prefix = 'antlers'): void
    {
        config(['statamic.antlers.instrumentation' => ['enabled' => true, 'comments' => true, 'prefix' => $prefix]]);
    }

    private function disable(): void
    {
        config(['statamic.antlers.instrumentation.enabled' => false]);
    }

    public function test_toggling_instrumentation_takes_effect_on_the_next_render()
    {
        $this->disable();
        $this->assertSame(self::PLAIN, $this->render());

        $this->enable();
        $rendered = $this->render();

        $this->assertStringContainsString('<!-- antlers:start ', $rendered);
        $this->assertSame(self::PLAIN, Runtime::removeMarkers($rendered));

        $this->disable();

        $this->assertSame(self::PLAIN, $this->render());
        $this->assertFalse(Runtime::instrumenting());
    }

    public function test_changing_the_prefix_after_rendering_emits_only_the_new_prefix()
    {
        $this->enable('first');
        $this->assertStringContainsString('<!-- first:start ', $this->render());

        $this->enable('second');
        $rendered = $this->render();

        $this->assertStringContainsString('<!-- second:start ', $rendered);
        $this->assertStringNotContainsString('first:', $rendered);
        $this->assertSame(self::PLAIN, Runtime::removeMarkers($rendered));
    }

    public function test_parsers_resolved_before_a_configuration_change_follow_it()
    {
        $this->enable();
        $parser = app(ParserContract::class);
        $this->assertStringContainsString('<!-- antlers:start ', (string) $parser->parse('<p>{{ title }}</p>', ['title' => 'Hello']));

        $this->disable();
        $this->assertSame('<p>Hello</p>', (string) $parser->parse('<p>{{ title }}</p>', ['title' => 'Hello']));

        $this->enable('again');
        $this->assertStringContainsString('<!-- again:start ', (string) $parser->parse('<p>{{ title }}</p>', ['title' => 'Hello']));
    }

    public function test_the_cache_tag_caches_again_once_instrumentation_is_disabled()
    {
        $this->viewShouldReturnRaw('cached', '<div>{{ cache }}<p>{{ lifecycle_spy }}</p>{{ /cache }}</div>');

        $this->enable();
        view('cached')->render();
        view('cached')->render();
        $this->assertSame(2, LifecycleSpyTag::$count);

        $this->disable();
        $this->assertSame('<div><p>spy</p></div>', view('cached')->render());
        $this->assertSame('<div><p>spy</p></div>', view('cached')->render());
        $this->assertSame(3, LifecycleSpyTag::$count);
    }

    public function test_includes_rendered_by_other_tags_pass_instrumented_slots_to_their_views()
    {
        $this->viewShouldReturnRaw('wrapper', '<section>{{ slot }}</section>');
        $this->viewShouldReturnRaw('delegated', '<div>{{ delegate }}<b>{{ title }}</b>{{ /delegate }}</div>');

        $this->disable();
        $plain = view('delegated', ['title' => 'Hello'])->render();
        $this->assertSame('<div><section><b>Hello</b></section></div>', $plain);

        $this->enable();
        $rendered = view('delegated', ['title' => 'Hello'])->render();

        $this->assertMatchesRegularExpression('/<section><!-- antlers:start [^ ]+ --><b><!-- antlers:start [^ ]+ -->Hello<!-- antlers:end \d+ --><\/b><!-- antlers:end \d+ --><\/section>/', $rendered);
        $this->assertSame($plain, Runtime::removeMarkers($rendered));
    }
}

class LifecycleSpyTag extends Tags
{
    public static $count = 0;

    public function index()
    {
        static::$count++;

        return 'spy';
    }
}

class DelegatingIncludeTag extends Tags
{
    public function index()
    {
        return app(Loader::class)->load('include', [
            'parser' => $this->parser,
            'params' => ['src' => 'wrapper'],
            'content' => $this->content,
            'context' => $this->context->all(),
            'tag' => 'include:wrapper',
            'tag_method' => 'wrapper',
        ])->wildcard('wrapper');
    }
}
