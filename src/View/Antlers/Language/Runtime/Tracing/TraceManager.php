<?php

namespace Statamic\View\Antlers\Language\Runtime\Tracing;

use Statamic\View\Antlers\Language\Nodes\AbstractNode;
use Statamic\View\Antlers\Language\Runtime\NodeProcessor;
use Statamic\View\Instrumentation\Span;
use Statamic\View\Instrumentation\TracerContract;
use Throwable;

class TraceManager
{
    /**
     * The configured runtime tracers.
     *
     * @var RuntimeTracerContract[]
     */
    protected $tracers = [];

    /** @var ProcessorAwareTracerContract[] */
    protected $processorAwareTracers = [];

    /** @var NodeProcessor|null */
    protected $lastProcessor = null;

    /** @var TracerContract[] */
    protected $spanTracers = [];

    /** @var array<int, array{nodeId: int, span: Span, handles: array<int, array{0: TracerContract, 1: mixed}>, processorId: int|null, processorDepth: int}> */
    protected $frames = [];

    /** @var int[] */
    protected $renderBoundaries = [];

    /** @var TracerContract[] */
    protected $renderCompletionTracers = [];

    /** @param  RuntimeTracerContract|TracerContract  $tracer */
    public function registerTracer($tracer)
    {
        if ($tracer instanceof RuntimeTracerContract) {
            if (in_array($tracer, $this->tracers, true)) {
                return;
            }

            $this->tracers[] = $tracer;

            if ($tracer instanceof ProcessorAwareTracerContract) {
                $this->processorAwareTracers[] = $tracer;

                if ($this->lastProcessor !== null) {
                    $tracer->setNodeProcessor($this->lastProcessor);
                }
            }

            return;
        }

        if ($tracer instanceof TracerContract) {
            if (in_array($tracer, $this->spanTracers, true)) {
                return;
            }

            $this->spanTracers[] = $tracer;

            return;
        }

        throw new \InvalidArgumentException(
            'Tracers must implement RuntimeTracerContract or TracerContract.'
        );
    }

    /**
     * @param  RuntimeTracerContract|TracerContract  $tracer
     * @return void
     */
    public function removeTracer($tracer)
    {
        $this->tracers = array_values(array_filter(
            $this->tracers,
            fn ($registered) => $registered !== $tracer
        ));

        $this->processorAwareTracers = array_values(array_filter(
            $this->processorAwareTracers,
            fn ($registered) => $registered !== $tracer
        ));

        $this->spanTracers = array_values(array_filter(
            $this->spanTracers,
            fn ($registered) => $registered !== $tracer
        ));
    }

    /** @return RuntimeTracerContract[] */
    public function tracers()
    {
        return $this->tracers;
    }

    /**
     * @internal
     *
     * @return TracerContract[]
     */
    public function spanTracers()
    {
        return $this->spanTracers;
    }

    public function traceOnEnter(
        AbstractNode $node,
        ?NodeProcessor $processor = null,
        int $processorDepth = 0
    ) {
        if ($this->renderBoundaries === []) {
            $this->traceRenderStart();
        }

        if ($processor !== null && $processorDepth > 0) {
            $this->closeProcessorScopeFrames($processor, $processorDepth);
        }

        if ($processor !== null && $processor !== $this->lastProcessor && $this->processorAwareTracers !== []) {
            foreach ($this->processorAwareTracers as $tracer) {
                $tracer->setNodeProcessor($processor);
            }

            $this->lastProcessor = $processor;
        }

        foreach ($this->tracers as $tracer) {
            $tracer->onEnter($node);
        }

        if ($this->spanTracers !== []) {
            $span = Span::antlersNode($node, $processor, $processorDepth);
            $handles = [];

            try {
                foreach ($this->spanTracers as $tracer) {
                    $handles[] = [$tracer, $tracer->onEnter($span)];
                    $this->rememberRenderCompletionTracer($tracer);
                }
            } catch (Throwable $enterFailure) {
                try {
                    $this->closeHandles($span, $handles, null);
                } catch (Throwable $cleanupFailure) {
                    // Preserve the original onEnter failure after closing every acquired handle.
                }

                throw $enterFailure;
            }

            $processorId = null;

            if ($processor !== null) {
                $processorId = spl_object_id($processor);
            }

            $this->frames[] = [
                'nodeId' => spl_object_id($node),
                'span' => $span,
                'handles' => $handles,
                'processorId' => $processorId,
                'processorDepth' => $processorDepth,
            ];
        }
    }

    public function traceProcessorScopeComplete(NodeProcessor $processor, int $processorDepth): void
    {
        $this->closeProcessorScopeFrames($processor, $processorDepth);
    }

    public function traceOnExit(AbstractNode $node, $runtimeContent)
    {
        $failure = null;

        foreach ($this->tracers as $tracer) {
            try {
                $tracer->onExit($node, $runtimeContent);
            } catch (Throwable $throwable) {
                $failure ??= $throwable;
            }
        }

        if ($this->frames === []) {
            if ($failure !== null) {
                throw $failure;
            }

            return;
        }

        $nodeId = spl_object_id($node);
        $targetIndex = null;

        $boundary = $this->currentRenderBoundary();

        for ($index = count($this->frames) - 1; $index >= $boundary; $index--) {
            if ($this->frames[$index]['nodeId'] === $nodeId) {
                $targetIndex = $index;

                break;
            }
        }

        if ($targetIndex === null) {
            if ($failure !== null) {
                throw $failure;
            }

            return;
        }

        while (count($this->frames) - 1 > $targetIndex) {
            try {
                $this->closeFrame(array_pop($this->frames), null);
            } catch (Throwable $throwable) {
                $failure ??= $throwable;
            }
        }

        try {
            $this->closeFrame(array_pop($this->frames), $runtimeContent);
        } catch (Throwable $throwable) {
            $failure ??= $throwable;
        }

        if ($failure !== null) {
            throw $failure;
        }
    }

    public function traceRenderStart()
    {
        if ($this->renderBoundaries === []) {
            $this->renderCompletionTracers = $this->spanTracers;
        }

        $this->renderBoundaries[] = count($this->frames);
    }

    public function renderDepth(): int
    {
        return count($this->renderBoundaries);
    }

    public function traceRenderComplete()
    {
        $boundary = array_pop($this->renderBoundaries) ?? 0;
        $failure = null;

        while (count($this->frames) > $boundary) {
            try {
                $this->closeFrame(array_pop($this->frames), null);
            } catch (Throwable $throwable) {
                $failure ??= $throwable;
            }
        }

        foreach ($this->tracers as $tracer) {
            try {
                $tracer->onRenderComplete();
            } catch (Throwable $throwable) {
                $failure ??= $throwable;
            }
        }

        if ($this->renderBoundaries !== []) {
            if ($failure !== null) {
                throw $failure;
            }

            return;
        }

        $this->lastProcessor = null;
        $completionTracers = $this->renderCompletionTracers;
        $this->renderCompletionTracers = [];

        foreach ($completionTracers as $tracer) {
            try {
                $tracer->onRenderComplete();
            } catch (Throwable $throwable) {
                $failure ??= $throwable;
            }
        }

        if ($failure !== null) {
            throw $failure;
        }
    }

    protected function rememberRenderCompletionTracer(TracerContract $tracer)
    {
        if (! in_array($tracer, $this->renderCompletionTracers, true)) {
            $this->renderCompletionTracers[] = $tracer;
        }
    }

    /** @param  array{nodeId: int, span: Span, handles: array<int, array{0: TracerContract, 1: mixed}>, processorId: int|null, processorDepth: int}  $frame */
    protected function closeFrame(array $frame, $runtimeContent)
    {
        $this->closeHandles($frame['span'], $frame['handles'], $runtimeContent);
    }

    /** @param  array<int, array{0: TracerContract, 1: mixed}>  $handles */
    protected function closeHandles(Span $span, array $handles, $runtimeContent)
    {
        $failure = null;

        for ($index = count($handles) - 1; $index >= 0; $index--) {
            [$tracer, $handle] = $handles[$index];

            try {
                $tracer->onExit($span, $handle, $runtimeContent);
            } catch (Throwable $throwable) {
                $failure ??= $throwable;
            }
        }

        if ($failure !== null) {
            throw $failure;
        }
    }

    protected function closeProcessorScopeFrames(NodeProcessor $processor, int $processorDepth): void
    {
        if ($this->frames === []) {
            return;
        }

        $processorId = spl_object_id($processor);
        $boundary = $this->currentRenderBoundary();
        $failure = null;

        while (true) {
            $targetIndex = null;

            for ($index = count($this->frames) - 1; $index >= $boundary; $index--) {
                if ($this->frames[$index]['processorId'] === $processorId
                    && $this->frames[$index]['processorDepth'] === $processorDepth) {
                    $targetIndex = $index;

                    break;
                }
            }

            if ($targetIndex === null) {
                break;
            }

            while (count($this->frames) > $targetIndex) {
                try {
                    $this->closeFrame(array_pop($this->frames), null);
                } catch (Throwable $throwable) {
                    $failure ??= $throwable;
                }
            }
        }

        if ($failure !== null) {
            throw $failure;
        }
    }

    protected function currentRenderBoundary()
    {
        if ($this->renderBoundaries === []) {
            return 0;
        }

        return end($this->renderBoundaries);
    }
}
