<?php

namespace Tests\Antlers\Runtime;

use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionProperty;
use RuntimeException;
use Statamic\Tags\Loader;
use Statamic\View\Antlers\Language\Nodes\AbstractNode;
use Statamic\View\Antlers\Language\Runtime\EnvironmentDetails;
use Statamic\View\Antlers\Language\Runtime\NodeProcessor;
use Statamic\View\Antlers\Language\Runtime\Tracing\ProcessorAwareTracerContract;
use Statamic\View\Antlers\Language\Runtime\Tracing\TraceManager;
use Statamic\View\Instrumentation\Span;
use Statamic\View\Instrumentation\TracerContract;
use Tests\Antlers\ParserTestCase;
use Tests\View\Instrumentation\Concerns\InteractsWithRuntimeConfigurations;
use Tests\View\Instrumentation\Concerns\TracesRenders;
use Tests\View\Instrumentation\Support\RecordingLegacyTracer;
use Tests\View\Instrumentation\Support\RecordingSpanTracer;
use Throwable;

class TraceManagerErrorsTest extends ParserTestCase
{
    use InteractsWithRuntimeConfigurations, TracesRenders;

    public function test_a_failing_span_enter_surfaces_from_the_render_and_leaves_no_open_render()
    {
        $recording = new RecordingSpanTracer;
        $parser = $this->tracingParser($recording, new ThrowingSpanTracer(enter: true));
        $manager = $this->runtimeConfiguration($parser)->traceManager;

        try {
            $parser->parse('<p>{{ title }}</p>', ['title' => 'Hello']);
            $this->fail('Expected the tracer failure to surface.');
        } catch (Throwable $exception) {
            $this->assertStringContainsString('enter failed', $exception->getMessage());
        }

        $this->assertSame(0, $manager->renderDepth());
        $this->assertSame([], $this->frames($manager));
        $this->assertSame('complete', $recording->lastEventType());
        $this->assertCount($recording->eventsOfType('enter')->count(), $recording->eventsOfType('exit'));
    }

    public function test_a_failing_legacy_exit_surfaces_from_the_render_and_leaves_no_open_render()
    {
        $recording = new RecordingSpanTracer;
        $parser = $this->tracingParser(new ThrowingLegacyTracer(exit: true), $recording);
        $manager = $this->runtimeConfiguration($parser)->traceManager;

        try {
            $parser->parse('<p>{{ title }}</p>', ['title' => 'Hello']);
            $this->fail('Expected the tracer failure to surface.');
        } catch (Throwable $exception) {
            $this->assertStringContainsString('exit failed', $exception->getMessage());
        }

        $this->assertSame(0, $manager->renderDepth());
        $this->assertSame([], $this->frames($manager));
        $this->assertSame('complete', $recording->lastEventType());
        $this->assertCount($recording->eventsOfType('enter')->count(), $recording->eventsOfType('exit'));
    }

    public function test_a_failing_span_exit_does_not_stop_the_other_tracers_from_closing()
    {
        $recording = new RecordingSpanTracer;
        $manager = new TraceManager;
        $manager->registerTracer($recording);
        $manager->registerTracer(new ThrowingSpanTracer(exit: true));
        $node = $this->parseNodes('{{ title }}')[0];

        $manager->traceRenderStart();
        $manager->traceOnEnter($node);

        try {
            $manager->traceOnExit($node, 'Hello');
            $this->fail('Expected the tracer failure to surface.');
        } catch (RuntimeException $exception) {
            $this->assertSame('exit failed', $exception->getMessage());
        }

        $this->assertSame(['enter', 'exit'], array_column($recording->events, 'type'));
        $this->assertSame('Hello', $recording->events[1]['output']);
        $this->assertSame([], $this->frames($manager));

        $manager->traceRenderComplete();

        $this->assertSame(0, $manager->renderDepth());
    }

    #[DataProvider('completionFailures')]
    public function test_a_completion_failure_after_a_successful_render_surfaces_and_closes_the_render(callable $makeTracer)
    {
        $tracer = $makeTracer();
        $parser = $this->tracingParser($tracer);
        $manager = $this->runtimeConfiguration($parser)->traceManager;

        try {
            $parser->parse('{{ title }}', ['title' => 'Hello']);
            $this->fail('Expected the completion failure to surface.');
        } catch (Throwable $exception) {
            $this->assertStringContainsString('completion failed', $exception->getMessage());
        }

        $this->assertSame(0, $manager->renderDepth());
        $this->assertSame([], $this->frames($manager));

        $manager->removeTracer($tracer);

        $this->assertSame('Hello', (string) $parser->parse('{{ title }}', ['title' => 'Hello']));
        $this->assertSame(0, $manager->renderDepth());
    }

    public static function completionFailures(): array
    {
        return [
            'span tracer' => [fn () => new ThrowingSpanTracer(complete: true)],
            'legacy tracer' => [fn () => new ThrowingLegacyTracer(complete: true)],
        ];
    }

    public function test_removed_tracers_no_longer_receive_events()
    {
        $legacy = new RecordingLegacyTracer;
        $span = new RecordingSpanTracer;
        $aware = new ProcessorRecordingTracer;
        $manager = new TraceManager;

        foreach ([$legacy, $legacy, $span, $span, $aware] as $tracer) {
            $manager->registerTracer($tracer);
        }

        $this->assertSame([$legacy, $aware], $manager->tracers());
        $this->assertSame([$span], $manager->spanTracers());

        $manager->removeTracer($legacy);
        $manager->removeTracer($span);
        $manager->removeTracer($aware);
        $manager->removeTracer(new RecordingLegacyTracer);

        $this->assertSame([], $manager->tracers());
        $this->assertSame([], $manager->spanTracers());

        $node = $this->parseNodes('{{ title }}')[0];
        $manager->traceRenderStart();
        $manager->traceOnEnter($node, new NodeProcessor(new Loader, new EnvironmentDetails));
        $manager->traceOnExit($node, 'Hello');
        $manager->traceRenderComplete();

        $this->assertSame([], $legacy->entered);
        $this->assertSame([], $span->events);
        $this->assertSame([], $aware->processors);
        $this->assertSame(0, $legacy->completions);
        $this->assertSame(0, $manager->renderDepth());
    }

    protected function frames(TraceManager $manager): array
    {
        return (new ReflectionProperty(TraceManager::class, 'frames'))->getValue($manager);
    }
}

class ThrowingSpanTracer implements TracerContract
{
    public function __construct(
        private bool $enter = false,
        private bool $exit = false,
        private bool $complete = false,
    ) {
    }

    public function onEnter(Span $span): mixed
    {
        if ($this->enter) {
            throw new RuntimeException('enter failed');
        }

        return 'throwing-handle';
    }

    public function onExit(Span $span, mixed $handle, mixed $output): void
    {
        if ($this->exit) {
            throw new RuntimeException('exit failed');
        }
    }

    public function onRenderComplete(): void
    {
        if ($this->complete) {
            throw new RuntimeException('completion failed');
        }
    }
}

class ThrowingLegacyTracer extends RecordingLegacyTracer
{
    public function __construct(
        private bool $exit = false,
        private bool $complete = false,
    ) {
    }

    public function onExit(AbstractNode $node, $runtimeContent)
    {
        parent::onExit($node, $runtimeContent);

        if ($this->exit) {
            throw new RuntimeException('exit failed');
        }
    }

    public function onRenderComplete()
    {
        parent::onRenderComplete();

        if ($this->complete) {
            throw new RuntimeException('completion failed');
        }
    }
}

class ProcessorRecordingTracer extends RecordingLegacyTracer implements ProcessorAwareTracerContract
{
    public array $processors = [];

    public function setNodeProcessor(NodeProcessor $processor): void
    {
        $this->processors[] = $processor;
    }
}
