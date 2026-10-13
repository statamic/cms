<?php

namespace Statamic\View\Antlers\Language\Runtime\Tracing;

use Statamic\View\Antlers\Language\Nodes\AbstractNode;
use Statamic\View\Antlers\Language\Runtime\NodeProcessor;
use Statamic\View\Antlers\Language\Runtime\RuntimeParser;
use Statamic\View\Instrumentation\Span;
use Statamic\View\Instrumentation\TracerContract;
use Throwable;
use WeakMap;

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

    /** @var array<int, array{0: int, 1: Span, 2: array<int, array{0: TracerContract, 1: mixed}>, 3: int|null, 4: int}> */
    protected $frames = [];

    /** @var bool */
    protected $hasUnrememberedSpanTracers = false;

    /** @var int[] */
    protected $renderBoundaries = [];

    /** @var array{0: int, 1: int}|null */
    protected $implicitRenderScope = null;

    /** @var WeakMap<TraceManager, bool>|null */
    protected static $openRenders = null;

    /** @var TracerContract[] */
    protected static $renderCompletionTracers = [];

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
            $this->hasUnrememberedSpanTracers = true;

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
        $processorId = $processor === null ? null : spl_object_id($processor);

        if ($this->renderBoundaries === []) {
            $this->traceRenderStart();

            if ($processorId !== null && ! RuntimeParser::isRendering()) {
                $this->implicitRenderScope = [$processorId, $processorDepth];
            }
        }

        if ($processorId !== null && $processorDepth > 0 && $this->frames !== []) {
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

        if ($this->spanTracers === []) {
            return;
        }

        if ($this->hasUnrememberedSpanTracers) {
            foreach ($this->spanTracers as $tracer) {
                $this->rememberRenderCompletionTracer($tracer);
            }

            $this->hasUnrememberedSpanTracers = false;
        }

        $span = Span::antlersNode($node, $processor, $processorDepth);
        $handles = [];

        try {
            foreach ($this->spanTracers as $tracer) {
                $handles[] = [$tracer, $tracer->onEnter($span)];
            }
        } catch (Throwable $enterFailure) {
            try {
                $this->closeHandles($span, $handles, null);
            } catch (Throwable) {
            }

            throw $enterFailure;
        }

        $this->frames[] = [spl_object_id($node), $span, $handles, $processorId, $processorDepth];
    }

    public function traceProcessorScopeComplete(NodeProcessor $processor, int $processorDepth)
    {
        if ($this->frames !== []) {
            $this->closeProcessorScopeFrames($processor, $processorDepth);
        }

        if ($this->implicitRenderScope !== null
            && $this->implicitRenderScope[1] === $processorDepth
            && $this->implicitRenderScope[0] === spl_object_id($processor)) {
            $this->traceRenderComplete(false);
        }
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
            if ($this->frames[$index][0] === $nodeId) {
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
        self::$openRenders ??= new WeakMap;

        if (count(self::$openRenders) === 0) {
            self::$renderCompletionTracers = [];
        }

        foreach ($this->spanTracers as $tracer) {
            $this->rememberRenderCompletionTracer($tracer);
        }

        $this->hasUnrememberedSpanTracers = false;
        $this->renderBoundaries[] = count($this->frames);
        self::$openRenders[$this] = true;
    }

    /** @return int */
    public function renderDepth()
    {
        return count($this->renderBoundaries);
    }

    /** @param  bool  $notifyTracers */
    public function traceRenderComplete($notifyTracers = true)
    {
        $boundary = array_pop($this->renderBoundaries) ?? 0;
        $failure = null;
        self::$openRenders ??= new WeakMap;

        if ($this->renderBoundaries === []) {
            $this->implicitRenderScope = null;
            unset(self::$openRenders[$this]);
        }

        while (count($this->frames) > $boundary) {
            try {
                $this->closeFrame(array_pop($this->frames), null);
            } catch (Throwable $throwable) {
                $failure ??= $throwable;
            }
        }

        if ($notifyTracers) {
            foreach ($this->tracers as $tracer) {
                try {
                    $tracer->onRenderComplete();
                } catch (Throwable $throwable) {
                    $failure ??= $throwable;
                }
            }
        }

        if ($this->renderBoundaries === []) {
            $this->lastProcessor = null;
        }

        if (count(self::$openRenders) > 0) {
            if ($failure !== null) {
                throw $failure;
            }

            return;
        }

        $completionTracers = self::$renderCompletionTracers;
        self::$renderCompletionTracers = [];

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
        if (! in_array($tracer, self::$renderCompletionTracers, true)) {
            self::$renderCompletionTracers[] = $tracer;
        }
    }

    /** @param  array{0: int, 1: Span, 2: array<int, array{0: TracerContract, 1: mixed}>, 3: int|null, 4: int}  $frame */
    protected function closeFrame(array $frame, $runtimeContent)
    {
        $this->closeHandles($frame[1], $frame[2], $runtimeContent);
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

    protected function closeProcessorScopeFrames(NodeProcessor $processor, int $processorDepth)
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
                if ($this->frames[$index][3] === $processorId && $this->frames[$index][4] === $processorDepth) {
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
