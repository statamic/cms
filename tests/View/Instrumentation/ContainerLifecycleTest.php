<?php

namespace Tests\View\Instrumentation;

use ReflectionProperty;
use Statamic\Contracts\View\Antlers\Parser;
use Statamic\View\Antlers\Language\Facades\Runtime;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Statamic\View\Antlers\Language\Runtime\RuntimeConfiguration;
use Statamic\View\Instrumentation\HtmlInstrumentation;
use Statamic\View\Instrumentation\InstrumentationManager;
use Tests\TestCase;
use Tests\View\Instrumentation\Concerns\InteractsWithRuntimeConfigurations;
use Tests\View\Instrumentation\Support\RecordingLegacyTracer;
use Tests\View\Instrumentation\Support\RecordingSpanTracer;
use WeakReference;

class ContainerLifecycleTest extends TestCase
{
    use InteractsWithRuntimeConfigurations;

    protected function setUp(): void
    {
        parent::setUp();

        GlobalRuntimeState::$isEvaluatingUserData = false;
    }

    public function test_configured_tracers_follow_their_container_bindings_and_receive_events()
    {
        $created = [];
        $this->app->bind(RecordingLegacyTracer::class, function () use (&$created) {
            $tracer = new RecordingLegacyTracer;
            $created[] = $tracer;

            return $tracer;
        });
        $this->app->singleton(RecordingSpanTracer::class);
        config(['statamic.antlers.tracing' => true, 'statamic.antlers.tracers' => [RecordingLegacyTracer::class, RecordingSpanTracer::class]]);

        $first = $this->app->make(Parser::class);
        $second = $this->app->make(Parser::class);
        $this->assertCount(2, $created);
        $this->assertNotSame($created[0], $created[1]);
        $this->assertSame($this->runtimeConfiguration($first)->traceManager->spanTracers(), $this->runtimeConfiguration($second)->traceManager->spanTracers());

        $this->assertSame('one', (string) $first->parse('{{ title }}', ['title' => 'one']));
        $this->assertSame('two', (string) $second->parse('{{ title }}', ['title' => 'two']));
        $this->assertNotEmpty($created[0]->entered);
        $this->assertNotEmpty($created[1]->entered);
    }

    public function test_late_registration_and_removal_reach_every_live_parser()
    {
        $first = $this->app->make(Parser::class);
        $second = $this->app->make(Parser::class);
        $instrumentation = HtmlInstrumentation::make()->comments('late');
        $tracer = new RecordingLegacyTracer;
        Runtime::register($instrumentation)->register($tracer);

        foreach ([$first, $second] as $parser) {
            $this->assertStringContainsString('<!-- late:start', (string) $parser->parse('<p>{{ title }}</p>', ['title' => 'Hello']));
            $this->assertContains($tracer, $this->runtimeConfiguration($parser)->traceManager->tracers());
        }

        Runtime::forget($instrumentation)->forget($tracer);

        foreach ([$first, $second] as $parser) {
            $this->assertSame('<p>Hello</p>', (string) $parser->parse('<p>{{ title }}</p>', ['title' => 'Hello']));
            $this->assertSame([], $this->runtimeConfiguration($parser)->traceManager->tracers());
            $this->assertFalse($this->runtimeConfiguration($parser)->isTracingEnabled);
        }

        Runtime::register($instrumentation)->register($tracer)->flush();

        foreach ([$first, $second] as $parser) {
            $this->assertSame('<p>Hello</p>', (string) $parser->parse('<p>{{ title }}</p>', ['title' => 'Hello']));
            $this->assertSame([], $this->runtimeConfiguration($parser)->traceManager->tracers());
            $this->assertFalse($this->runtimeConfiguration($parser)->isTracingEnabled);
        }
    }

    public function test_the_manager_only_touches_tracing_it_is_responsible_for()
    {
        $ownTracer = new RecordingLegacyTracer;
        $configuration = new RuntimeConfiguration;
        $configuration->enableTracing()->registerTracer($ownTracer);
        Runtime::applyTo($configuration);

        Runtime::forget($ownTracer);
        $this->assertContains($ownTracer, $configuration->traceManager->tracers());

        $configuration->isTracingEnabled = false;
        Runtime::preparse(fn ($template) => $template);
        $this->assertFalse($configuration->isTracingEnabled);
    }

    public function test_the_instrumentation_manager_does_not_keep_runtime_configurations_alive()
    {
        $configuration = new RuntimeConfiguration;
        $reference = WeakReference::create($configuration);
        Runtime::applyTo($configuration);
        unset($configuration);

        $this->assertNull($reference->get());
    }

    public function test_changing_configurations_do_not_accumulate_instrumenters_or_cached_results()
    {
        for ($request = 1; $request <= 12; $request++) {
            config(['statamic.antlers.instrumentation' => ['enabled' => true, 'metadata' => ['request' => $request]]]);

            $rendered = (string) $this->app->make(Parser::class)->parse('<p>{{ title }}</p>', ['title' => 'Hello']);

            $this->assertSame($request, HtmlInstrumentation::decode($rendered)['request']);
        }

        $configured = (new ReflectionProperty(InstrumentationManager::class, 'configured'))
            ->getValue($this->app->make(InstrumentationManager::class));

        $this->assertCount(1, $configured);
    }
}
