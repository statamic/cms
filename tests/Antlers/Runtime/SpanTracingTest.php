<?php

namespace Tests\Antlers\Runtime;

use Illuminate\Support\Arr;
use RuntimeException;
use Statamic\Facades\Antlers;
use Statamic\Tags\Loader;
use Statamic\Tags\Tags;
use Statamic\View\Antlers\Language\Facades\Runtime;
use Statamic\View\Antlers\Language\Nodes\AbstractNode;
use Statamic\View\Antlers\Language\Nodes\Conditions\ConditionNode;
use Statamic\View\Antlers\Language\Runtime\EnvironmentDetails;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Statamic\View\Antlers\Language\Runtime\NodeProcessor;
use Statamic\View\Antlers\Language\Runtime\RuntimeConfiguration;
use Statamic\View\Antlers\Language\Runtime\RuntimeParser;
use Statamic\View\Antlers\Language\Runtime\StackReplacementManager;
use Statamic\View\Antlers\Language\Runtime\Tracing\ProcessorAwareTracerContract;
use Statamic\View\Antlers\Language\Runtime\Tracing\TraceManager;
use Statamic\View\Instrumentation\Span;
use Statamic\View\Instrumentation\TracerContract;
use Tests\Antlers\ParserTestCase;
use Tests\FakesViews;
use Tests\View\Instrumentation\Concerns\InteractsWithRuntimeConfigurations;
use Tests\View\Instrumentation\Concerns\TracesRenders;
use Tests\View\Instrumentation\Support\RecordingLegacyTracer;
use Tests\View\Instrumentation\Support\RecordingSpanTracer;
use Throwable;

class SpanTracingTest extends ParserTestCase
{
    use FakesViews, InteractsWithRuntimeConfigurations, TracesRenders;

    public function test_open_spans_and_legacy_tracers_are_completed_when_rendering_throws()
    {
        $legacy = new RecordingLegacyTracer;
        $span = new RecordingSpanTracer;
        $thrown = null;

        try {
            $this->tracingParser($legacy, $span)->parse('{{ title | modifier_that_does_not_exist }}', ['title' => 'Hello']);
        } catch (Throwable $throwable) {
            $thrown = $throwable;
        }

        $this->assertNotNull($thrown);

        $enters = $span->eventsOfType('enter');

        $this->assertNotEmpty($enters);
        $this->assertCount(count($enters), $span->eventsOfType('exit'));
        $this->assertSame('complete', $span->lastEventType());
        $this->assertNotEmpty($legacy->entered);
        $this->assertSame(1, $legacy->completions);
    }

    public function test_a_completion_failure_does_not_mask_the_template_failure()
    {
        try {
            $this->tracingParser(new FailingAntlersCompletionTracer)
                ->parse('{{ title | modifier_that_does_not_exist }}', ['title' => 'Hello']);
            $this->fail('Expected rendering to throw.');
        } catch (Throwable $exception) {
            $this->assertStringContainsString('modifier_that_does_not_exist', $exception->getMessage());
        }
    }

    public function test_nested_view_completion_does_not_abandon_the_calling_tag_span()
    {
        $this->withFakeViews();
        $this->viewShouldReturnRaw('outer', '<main>{{ partial:inner }}</main>');
        $this->viewShouldReturnRaw('inner', '<p>{{ title }}</p>');

        $tracer = new RecordingSpanTracer;

        Runtime::register($tracer, ['antlers']);

        $this->assertSame('<main><p>Hello</p></main>', view('outer', ['title' => 'Hello'])->render());

        $partialEnter = $tracer->eventsOfType('enter')->firstWhere('span.expression', 'partial:inner');
        $this->assertNotNull($partialEnter);

        $partialExit = $tracer->exitFor($partialEnter['span']);
        $this->assertNotNull($partialExit);
        $this->assertSame('<p>Hello</p>', $partialExit['output']);
        $this->assertSame($partialEnter['handle'], $partialExit['handle']);
    }

    public function test_render_completion_is_reported_once_for_the_outermost_render()
    {
        $this->withFakeViews();
        $this->viewShouldReturnRaw('outer', '<main>{{ partial:inner }}{{ partial:inner }}</main>');
        $this->viewShouldReturnRaw('inner', '<p>{{ title }}</p>');

        $tracer = new RecordingSpanTracer;

        Runtime::register($tracer, ['antlers']);

        view('outer', ['title' => 'Hello'])->render();

        $completions = $tracer->eventsOfType('complete');

        $this->assertCount(1, $completions);
        $this->assertSame('complete', $tracer->lastEventType());
    }

    public function test_condition_spans_close_before_their_siblings_are_entered()
    {
        $tracer = new RecordingSpanTracer;

        $result = (string) $this->tracingParser($tracer)->parse('{{ if true }}A{{ /if }}{{ title }}', ['title' => 'Hello']);

        $this->assertSame('AHello', $result);

        $isCondition = fn (Span $span) => $span->raw() instanceof ConditionNode;
        $conditionEnter = $tracer->indexOfEvent('enter', $isCondition);
        $conditionExit = $tracer->indexOfEvent('exit', $isCondition);
        $closingExit = $tracer->indexOfEvent('exit', fn (Span $span) => $span->expression === '/if');
        $literalEnter = $tracer->indexOfEvent('enter', fn (Span $span) => $span->expression === 'A');
        $titleEnter = $tracer->indexOfEvent('enter', fn (Span $span) => $span->expression === 'title');

        $this->assertNotNull($conditionEnter);
        $this->assertNotNull($conditionExit);
        $this->assertNotNull($closingExit);
        $this->assertNotNull($literalEnter);
        $this->assertNotNull($titleEnter);

        $this->assertLessThan($literalEnter, $conditionExit);
        $this->assertLessThan($titleEnter, $conditionExit);
        $this->assertLessThan($titleEnter, $closingExit);
    }

    public function test_recursive_partial_spans_receive_their_real_output()
    {
        $this->withFakeViews();
        $this->viewShouldReturnRaw('rp', '{{ items }}[{{ partial:rp }}]{{ /items }}');

        $tracer = new RecordingSpanTracer;

        Runtime::register($tracer, ['antlers']);

        $result = view('rp', ['items' => [['items' => [['items' => []]]]]])->render();

        $this->assertSame('[[]]', $result);

        $itemsEnters = $tracer->eventsOfType('enter')
            ->where('span.expression', 'items')
            ->values();

        $this->assertCount(3, $itemsEnters);

        $outerExit = $tracer->exitFor($itemsEnters[0]['span']);
        $innerExit = $tracer->exitFor($itemsEnters[1]['span']);

        $this->assertNotNull($outerExit);
        $this->assertNotNull($innerExit);
        $this->assertSame('[[]]', $outerExit['output']);
        $this->assertSame('[]', $innerExit['output']);
        $this->assertGreaterThan($innerExit['index'], $outerExit['index']);
    }

    public function test_a_tracer_enabled_mid_render_receives_balanced_completion()
    {
        $tracer = new RecordingSpanTracer;
        EnableSpanTracingTag::$tracer = $tracer;
        $this->app['statamic.tags']['enable_span_tracing'] = EnableSpanTracingTag::class;

        $configuration = new RuntimeConfiguration;
        Runtime::applyTo($configuration);
        $this->assertFalse($configuration->isTracingEnabled);

        $parser = $this->parser([], true);
        $parser->setRuntimeConfiguration($configuration);

        $this->assertSame(
            'Hello',
            (string) $parser->parse('{{ enable_span_tracing }}{{ title }}', ['title' => 'Hello'])
        );

        $this->assertNotEmpty($tracer->eventsOfType('enter'));
        $this->assertSame('complete', $tracer->lastEventType());
        $this->assertSame(0, $configuration->traceManager->renderDepth());
    }

    public function test_a_tracer_enabled_mid_render_is_completed_when_the_render_throws()
    {
        $tracer = new RecordingSpanTracer;
        EnableSpanTracingTag::$tracer = $tracer;
        $this->app['statamic.tags']['enable_span_tracing'] = EnableSpanTracingTag::class;

        $configuration = new RuntimeConfiguration;
        Runtime::applyTo($configuration);
        $this->assertNull($configuration->traceManager);

        $parser = $this->parser([], true);
        $parser->setRuntimeConfiguration($configuration);

        try {
            $parser->parse('{{ enable_span_tracing }}{{ title | modifier_that_does_not_exist }}', ['title' => 'Hello']);
            $this->fail('Expected rendering to throw.');
        } catch (Throwable $exception) {
            $this->assertStringContainsString('modifier_that_does_not_exist', $exception->getMessage());
        }

        $this->assertNotEmpty($tracer->eventsOfType('enter'));
        $this->assertCount($tracer->eventsOfType('enter')->count(), $tracer->eventsOfType('exit'));
        $this->assertSame('complete', $tracer->lastEventType());
        $this->assertSame(0, $configuration->traceManager->renderDepth());
    }

    public function test_spans_expose_their_processor_and_depth_as_meta()
    {
        $node = $this->parseNodes('<p>{{ title }}</p>')[1];
        $processor = new NodeProcessor(new Loader, new EnvironmentDetails);
        $processor->setData(['title' => 'Hello']);

        $span = Span::antlersNode($node, $processor, 3);

        $this->assertSame('title', $span->expression);
        $this->assertSame($node, $span->raw());
        $this->assertSame(['processor' => $processor, 'processor_depth' => 3], $span->meta());
        $this->assertSame($processor, $span->meta('processor'));
        $this->assertSame(3, $span->meta('processor_depth'));
        $this->assertNull($span->meta('missing'));
        $this->assertSame('Hello', $span->scope()['title']);

        $bare = Span::antlersNode($node);

        $this->assertSame(['processor' => null, 'processor_depth' => 0], $bare->meta());
        $this->assertNull($bare->scope());
    }

    public function test_a_configuration_set_on_the_processor_alone_still_completes_renders()
    {
        $legacy = new RecordingLegacyTracer;
        $span = new RecordingSpanTracer;
        $manager = new TraceManager;
        $manager->registerTracer($legacy);
        $manager->registerTracer($span);

        $configuration = new RuntimeConfiguration;
        $configuration->isTracingEnabled = true;
        $configuration->traceManager = $manager;

        $this->assertSame('Hello', $this->renderStringWithConfiguration('{{ title }}', $configuration, ['title' => 'Hello']));
        $this->assertSame('Hello', $this->renderStringWithConfiguration('{{ title }}', $configuration, ['title' => 'Hello']));

        $this->assertSame(2, $legacy->completions);
        $this->assertCount(2, $span->eventsOfType('complete'));
        $this->assertSame(0, $manager->renderDepth());
    }

    public function test_renders_through_other_parser_instances_do_not_complete_the_outer_render()
    {
        $this->app['statamic.tags']['fresh_parser_render'] = FreshParserRenderTag::class;
        $tracer = new RecordingSpanTracer;
        Runtime::register($tracer, ['antlers']);

        $configuration = new RuntimeConfiguration;
        Runtime::applyTo($configuration);
        $parser = $this->parser([], true);
        $parser->setRuntimeConfiguration($configuration);

        $output = (string) $parser->parse(
            '{{ items }}{{ fresh_parser_render }}{{ /items }}{{ title }}',
            ['items' => [['value' => 'a'], ['value' => 'b']], 'title' => 'Hello']
        );

        $this->assertSame('[a][b]Hello', $output);
        $this->assertCount(1, $tracer->eventsOfType('complete'));
        $this->assertSame('complete', $tracer->lastEventType());
    }

    public function test_nodes_reduced_after_a_render_do_not_leave_a_render_open()
    {
        RuntimeParser::clearRenderNodeCache();
        StackReplacementManager::clearStackState();
        $tracer = new RecordingSpanTracer;
        $parser = $this->tracingParser($tracer);
        $manager = $this->runtimeConfiguration($parser)->traceManager;

        GlobalRuntimeState::$containsLayout = true;

        try {
            $template = (string) $parser->parse('T{{ push:late }}P{{ /push:late }}', []);
        } finally {
            GlobalRuntimeState::$containsLayout = false;
        }

        $layout = (string) $parser->parse('<{{ stack:late }}[{{ value }}]{{ /stack:late }}>{{ template_content }}', ['template_content' => $template]);
        $rendered = StackReplacementManager::processReplacements($layout);

        $this->assertSame('<[P]>T', $rendered);
        $this->assertSame(0, $manager->renderDepth());
        $this->assertSame('complete', $tracer->lastEventType());

        $completions = $tracer->eventsOfType('complete')->count();
        $parser->parse('{{ title }}', ['title' => 'Hello']);

        $this->assertSame($completions + 1, $tracer->eventsOfType('complete')->count());
    }

    public function test_a_tracer_enabled_inside_a_nested_render_is_completed_once()
    {
        $tracer = new RecordingSpanTracer;
        EnableSpanTracingTag::$tracer = $tracer;
        $this->app['statamic.tags']['enable_span_tracing'] = EnableSpanTracingTag::class;
        $this->app['statamic.tags']['reparsing_pair'] = ReparsingPairTag::class;

        $configuration = new RuntimeConfiguration;
        Runtime::applyTo($configuration);

        $parser = $this->parser([], true);
        $parser->setRuntimeConfiguration($configuration);

        $this->assertSame(
            'HelloHello',
            (string) $parser->parse('{{ reparsing_pair }}{{ enable_span_tracing }}{{ title }}{{ /reparsing_pair }}{{ title }}', ['title' => 'Hello'])
        );

        $this->assertCount(1, $tracer->eventsOfType('complete'));
        $this->assertSame('complete', $tracer->lastEventType());
        $this->assertSame(0, $configuration->traceManager->renderDepth());
    }

    public function test_processor_aware_tracers_receive_the_processor_before_a_node_is_entered()
    {
        $tracer = new ProcessorAwareLegacyTracer;
        $parser = $this->tracingParser($tracer);

        $parser->parse('{{ items }}{{ value }}{{ /items }}', ['items' => [['value' => 'a']]]);

        $this->assertNotEmpty($tracer->processors);
        $this->assertNotContains(null, $tracer->processors);
        $this->assertGreaterThan(1, count(array_unique(array_map('spl_object_id', $tracer->processors))));
    }

    public function test_else_and_elseif_heads_carry_the_evaluating_processor()
    {
        $legacy = new ProcessorAwareLegacyTracer;
        $span = new RecordingSpanTracer;
        $parser = $this->tracingParser($legacy, $span);

        $result = (string) $parser->parse(
            '{{ items }}{{ if alpha }}A{{ elseif beta }}B{{ else }}C{{ /if }}{{ /items }}',
            ['alpha' => false, 'beta' => false, 'items' => [['value' => 'a']]]
        );

        $this->assertSame('C', $result);

        $loopProcessor = $span->eventsOfType('enter')->firstWhere('span.expression', 'C')['span']->meta('processor');

        $this->assertInstanceOf(NodeProcessor::class, $loopProcessor);
        $this->assertNotSame($span->eventsOfType('enter')->firstWhere('span.expression', 'items')['span']->meta('processor'), $loopProcessor);

        foreach (['if alpha', 'elseif beta', 'else'] as $head) {
            $enter = $span->eventsOfType('enter')->firstWhere('span.expression', $head);

            $this->assertNotNull($enter, $head);
            $this->assertSame($loopProcessor, $enter['span']->meta('processor'), $head);
            $this->assertSame($loopProcessor, $legacy->processorFor($head), $head);

            $scope = Arr::only($enter['span']->scope(), ['alpha', 'beta', 'value']);
            ksort($scope);

            $this->assertSame(['alpha' => false, 'beta' => false, 'value' => 'a'], $scope, $head);
        }
    }
}

class FailingAntlersCompletionTracer implements TracerContract
{
    public function onEnter(Span $span): mixed
    {
        return null;
    }

    public function onExit(Span $span, mixed $handle, mixed $output): void
    {
    }

    public function onRenderComplete(): void
    {
        throw new RuntimeException('completion failed');
    }
}

class FreshParserRenderTag extends Tags
{
    public function index()
    {
        return (string) Antlers::parse('[{{ value }}]', $this->context->all());
    }
}

class ReparsingPairTag extends Tags
{
    public function index()
    {
        return $this->parse([]);
    }
}

class ProcessorAwareLegacyTracer extends RecordingLegacyTracer implements ProcessorAwareTracerContract
{
    public array $processors = [];

    public array $nodeProcessors = [];

    protected ?NodeProcessor $processor = null;

    public function setNodeProcessor(NodeProcessor $processor): void
    {
        $this->processor = $processor;
    }

    public function onEnter(AbstractNode $node)
    {
        $this->processors[] = $this->processor;
        $this->nodeProcessors[] = [$node, $this->processor];
    }

    public function processorFor(string $expression): ?NodeProcessor
    {
        foreach ($this->nodeProcessors as [$node, $processor]) {
            if (trim((string) $node->content) === $expression) {
                return $processor;
            }
        }

        return null;
    }
}

class EnableSpanTracingTag extends Tags
{
    public static ?TracerContract $tracer = null;

    public function index()
    {
        Runtime::register(static::$tracer, ['antlers']);

        return '';
    }
}
