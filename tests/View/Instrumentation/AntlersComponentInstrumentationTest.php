<?php

namespace Tests\View\Instrumentation;

use ReflectionProperty;
use Statamic\Tags\ComponentProxy;
use Statamic\View\Antlers\Language\Facades\Runtime;
use Statamic\View\Antlers\Language\Parser\ComponentCompiler;
use Statamic\View\Instrumentation\HtmlInstrumentation;
use Statamic\View\Instrumentation\InstrumentationState;
use Tests\Antlers\ParserTestCase;
use Tests\FakesViews;
use Tests\View\Instrumentation\Concerns\DecodesMarkers;
use Tests\View\Instrumentation\Concerns\InteractsWithRuntimeConfigurations;
use Throwable;

class AntlersComponentInstrumentationTest extends ParserTestCase
{
    use DecodesMarkers, FakesViews, InteractsWithRuntimeConfigurations;

    public function test_runtime_markers_after_components_use_authored_offsets_and_lines()
    {
        $source = "<x-review\r\n title=\"é\" />\r\n<p>{{ after }}</p>";
        $output = $this->renderInstrumentedComponent('<hr>', $source, ['after' => 'Hello']);
        $metadata = $this->decodedMarkers($output);
        $after = collect($metadata)->firstWhere('expression', 'after');

        $this->assertNotNull($after);
        $this->assertSame(mb_strpos($source, '{{ after }}'), $after['start']);
        $this->assertSame(mb_strpos($source, '{{ after }}') + 11, $after['end']);
        $this->assertSame(3, $after['line']);
        $this->assertCount(1, $metadata);
    }

    public function test_component_source_maps_preserve_native_tag_regions_and_adjacent_expression_extents()
    {
        $source = "<div>é\r\n{{ before }}<s-foo>{{ inside }}<s-bar /></s-foo>{{ after }}</div>";
        $compiler = new ComponentCompiler;
        $compiled = $compiler->compile($source, true);
        $output = InstrumentationState::withSourceMap(
            $compiler->sourceMap(),
            fn () => HtmlInstrumentation::make()->instrument($compiled)
        );
        $metadata = collect($this->decodedMarkers($output))->keyBy('expression');

        foreach (['before', 'inside', 'after'] as $expression) {
            $snippet = '{{ '.$expression.' }}';
            $this->assertSame(mb_strpos($source, $snippet), $metadata[$expression]['start']);
            $this->assertSame(mb_strpos($source, $snippet) + mb_strlen($snippet), $metadata[$expression]['end']);
            $this->assertSame(2, $metadata[$expression]['line']);
        }

        $this->assertSame(mb_strpos($source, '<s-foo>'), $metadata['%foo']['start']);
        $this->assertSame(mb_strpos($source, '</s-foo>') + 8, $metadata['%foo']['end']);
        $this->assertSame(mb_strpos($source, '<s-bar />'), $metadata['%bar']['start']);
        $this->assertSame(mb_strpos($source, '<s-bar />') + 9, $metadata['%bar']['end']);
    }

    public function test_component_source_identity_is_part_of_the_instrumentation_result_cache()
    {
        $instrumenter = HtmlInstrumentation::make();
        $compiledSources = [];

        foreach (['<x-review />', "<x-review\r\n />"] as $component) {
            $source = $component.'<p>{{ title }}</p>';
            $compiler = new ComponentCompiler;
            $compiledSources[] = $compiled = $compiler->compile($source, true);
            $output = InstrumentationState::withSourceMap($compiler->sourceMap(), fn () => $instrumenter->instrument($compiled));

            $this->assertSame(mb_strpos($source, '{{ title }}'), HtmlInstrumentation::decode($output)['start']);
        }

        $this->assertSame($compiledSources[0], $compiledSources[1]);
    }

    public function test_component_view_is_instrumented_without_reinstrumenting_its_slot()
    {
        $rendered = $this->renderInstrumentedComponent(
            '<article>{{ slot }}</article>',
            '<x-review><b>{{ value }}</b></x-review>',
            ['value' => 'Hello']
        );

        $this->assertSame(1, substr_count($rendered, '<!-- antlers:start '));
        $this->assertSame(1, substr_count($rendered, 'data-antlers='));
        $this->assertStringContainsString('<b>Hello</b>', $rendered);
        $this->assertStringNotContainsString('<b data-antlers=', $rendered);
        $this->assertSame('slot', HtmlInstrumentation::decode($rendered)['expression']);
    }

    public function test_named_component_slots_stay_neutral_and_keep_markers_out_of_attributes()
    {
        $configuration = $this->runtimeConfiguration();
        $data = ['value' => 'Hello'];
        $caller = '<x-review><b>{{ value }}</b><x-slot:footer><i>{{ value }}</i></x-slot:footer></x-review>';

        $this->withFakeViews();
        $this->viewShouldReturnRaw(
            'components.review',
            '<div title="{{ footer }}">{{ slot | upper }}<footer>{{ footer }}</footer><aside>{{ footer | upper }}</aside><input value="{{ slot }}"></div>'
        );

        $parser = $this->parser($data, true);
        $parser->setRuntimeConfiguration($configuration);

        $plain = (string) $parser->parse($caller, $data);

        $this->assertSame(
            '<div title="<i>Hello</i>"><B>HELLO</B><footer><i>Hello</i></footer><aside><I>HELLO</I></aside><input value="<b>Hello</b>"></div>',
            $plain
        );

        Runtime::register(HtmlInstrumentation::make()->attributes('data-antlers'));

        $instrumented = (string) $parser->parse($caller, $data);

        $this->assertNotSame($plain, $instrumented);
        $this->assertSame($plain, Runtime::removeMarkers($instrumented));
        $this->assertDoesNotMatchRegularExpression('/(title|value)="[^"]*<!--/', $instrumented);
        $this->assertMatchesRegularExpression('/<footer data-antlers="[^"]+"><!-- antlers:start [^ ]+ --><i>Hello<\/i><!-- antlers:end \d+ --><\/footer>/', $instrumented);
        $this->assertSame(['slot | upper', 'footer', 'footer | upper'], $this->markerExpressions($instrumented));
        $this->assertFalse(InstrumentationState::suppressed());
        $this->assertFalse(InstrumentationState::inComponentView());
    }

    public function test_views_rendered_inside_component_slots_are_not_instrumented()
    {
        $this->withFakeViews();
        $this->viewShouldReturnRaw('components.review', '<input value="{{ slot }}">');
        $this->viewShouldReturnRaw('review-label', '{{ value }}');

        $parser = $this->instrumentedParser(['value' => 'Hello'], HtmlInstrumentation::make()->attributes('data-antlers'));

        $rendered = (string) $parser->parse('<x-review>{{ partial:review-label }}</x-review>', ['value' => 'Hello']);

        $this->assertStringNotContainsString('<!--', $rendered);
        $this->assertStringContainsString(' value="Hello">', $rendered);
        $this->assertFalse(InstrumentationState::suppressed());
    }

    public function test_components_rendered_into_raw_text_are_not_instrumented_while_safe_occurrences_are()
    {
        $rendered = $this->renderInstrumentedComponent(
            '<b>{{ title }}</b>',
            '<textarea><x-review title="Hello" /></textarea><p><x-review title="There" /></p>'
        );

        $this->assertStringStartsWith('<textarea><b>Hello</b></textarea>', $rendered);
        $this->assertStringContainsString('<b data-antlers="', $rendered);
        $this->assertStringContainsString('>There<', $rendered);
    }

    public function test_component_failures_release_instrumentation_and_component_render_state()
    {
        $this->withFakeViews();
        $this->viewShouldReturnRaw('components.review', '{{ value | modifier_that_does_not_exist }}');

        $parser = $this->instrumentedParser(['value' => 'Hello'], HtmlInstrumentation::make()->attributes('data-antlers'));

        try {
            $parser->parse('<x-review />', ['value' => 'Hello']);
            $this->fail('Expected component rendering to throw.');
        } catch (Throwable $throwable) {
            $this->assertNotSame('', $throwable->getMessage());
        }

        $stack = new ReflectionProperty(ComponentProxy::class, 'componentStack');
        $stack->setAccessible(true);

        $this->assertFalse(InstrumentationState::inComponentView());
        $this->assertSame([], $stack->getValue());
        $this->assertStringContainsString(
            '<p data-antlers="',
            (string) $parser->parse('<p>{{ value }}</p>', ['value' => 'Hello'])
        );
    }

    protected function renderInstrumentedComponent($component, $caller, $data = [])
    {
        $this->withFakeViews();
        $this->viewShouldReturnRaw('components.review', $component);

        return (string) $this->instrumentedParser($data, HtmlInstrumentation::make()->attributes('data-antlers'))->parse($caller, $data);
    }
}
