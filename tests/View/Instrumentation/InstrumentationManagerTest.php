<?php

namespace Tests\View\Instrumentation;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Statamic\Contracts\View\Antlers\Parser;
use Statamic\View\Antlers\Language\Facades\Runtime;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Statamic\View\Instrumentation\HtmlInstrumentation;
use Statamic\View\Instrumentation\InstrumentationManager;
use stdClass;
use Tests\TestCase;
use Tests\View\Instrumentation\Concerns\InteractsWithRuntimeConfigurations;
use Tests\View\Instrumentation\Support\RecordingLegacyTracer;
use Tests\View\Instrumentation\Support\RecordingSpanTracer;

class InstrumentationManagerTest extends TestCase
{
    use InteractsWithRuntimeConfigurations;

    protected function setUp(): void
    {
        parent::setUp();

        GlobalRuntimeState::$isEvaluatingUserData = false;
    }

    public function test_tracers_can_be_registered_and_forgotten_by_class_name()
    {
        $parser = app(Parser::class);
        $configuration = $this->runtimeConfiguration($parser);

        $this->assertFalse($configuration->isTracingEnabled);

        Runtime::register(RecordingLegacyTracer::class)->register(RecordingLegacyTracer::class);

        $tracers = $configuration->traceManager->tracers();

        $this->assertCount(1, $tracers);
        $this->assertInstanceOf(RecordingLegacyTracer::class, $tracers[0]);
        $this->assertTrue($configuration->isTracingEnabled);

        $parser->parse('{{ title }}', ['title' => 'Hello']);

        $this->assertNotEmpty($tracers[0]->entered);

        Runtime::forget(RecordingLegacyTracer::class);

        $this->assertSame([], $configuration->traceManager->tracers());
        $this->assertFalse($configuration->isTracingEnabled);

        Runtime::register(RecordingLegacyTracer::class);

        $this->assertCount(1, $configuration->traceManager->tracers());
        $this->assertNotSame($tracers[0], $configuration->traceManager->tracers()[0]);
        $this->assertTrue($configuration->isTracingEnabled);
    }

    public function test_pieces_that_are_not_instrumentation_are_rejected()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Instrumentation pieces must be an HtmlInstrumentation instance, a TracerContract, or a RuntimeTracerContract.');

        Runtime::register(new stdClass);
    }

    #[DataProvider('invalidEngines')]
    public function test_invalid_engine_lists_are_rejected(array $engines, string $message)
    {
        $configuration = $this->runtimeConfiguration();
        $tracer = new RecordingLegacyTracer;

        try {
            Runtime::register($tracer, $engines);
            $this->fail('Expected the engine list to be rejected.');
        } catch (InvalidArgumentException $exception) {
            $this->assertSame($message, $exception->getMessage());
        }

        $this->assertFalse($configuration->isTracingEnabled);

        Runtime::register($tracer);

        try {
            Runtime::forget($tracer, $engines);
            $this->fail('Expected the engine list to be rejected.');
        } catch (InvalidArgumentException $exception) {
            $this->assertSame($message, $exception->getMessage());
        }

        $this->assertSame([$tracer], $configuration->traceManager->tracers());
        $this->assertTrue($configuration->isTracingEnabled);
    }

    public static function invalidEngines(): array
    {
        return [
            'empty' => [[], 'At least one engine must be provided.'],
            'unknown' => [['blade'], 'Unknown instrumentation engine [blade].'],
            'non-string' => [[1], 'Instrumentation engine names must be strings.'],
        ];
    }

    public function test_flush_clears_every_registered_piece_and_resolved_instance()
    {
        $manager = app(InstrumentationManager::class);
        $parser = app(Parser::class);
        $configuration = $this->runtimeConfiguration($parser);
        $instrumentation = HtmlInstrumentation::make();

        $manager->register($instrumentation)->register(RecordingLegacyTracer::class)->register(new RecordingSpanTracer);
        $resolved = $configuration->traceManager->tracers()[0];

        $this->assertTrue($manager->instrumenting());
        $this->assertStringContainsString('<!-- antlers:start ', (string) $parser->parse('<p>{{ title }}</p>', ['title' => 'Hello']));

        $this->assertSame($manager, $manager->flush());

        $this->assertFalse($manager->instrumenting());
        $this->assertSame('<p>Hello</p>', (string) $parser->parse('<p>{{ title }}</p>', ['title' => 'Hello']));
        $this->assertSame([], $configuration->traceManager->tracers());
        $this->assertSame([], $configuration->traceManager->spanTracers());
        $this->assertFalse($configuration->isTracingEnabled);

        $manager->register(RecordingLegacyTracer::class);

        $this->assertNotSame($resolved, $configuration->traceManager->tracers()[0]);
        $this->assertTrue($configuration->isTracingEnabled);
    }
}
