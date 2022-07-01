<?php

namespace Statamic\View\Antlers\Language\Runtime\Tracing;

use Statamic\View\Antlers\Language\Nodes\AbstractNode;

class TraceManager
{
    /**
     * The configured runtime tracers.
     *
     * @var RuntimeTracerContract[]
     */
    protected $tracers = [];

    public function registerTracer(RuntimeTracerContract $tracer)
    {
        $this->tracers[] = $tracer;
    }

    public function traceOnEnter(AbstractNode $node)
    {
        foreach ($this->tracers as $tracer) {
            $tracer->onEnter($node);
        }
    }

    public function traceOnExit(AbstractNode $node, $runtimeContent)
    {
        foreach ($this->tracers as $tracer) {
            $tracer->onExit($node, $runtimeContent);
        }
    }

    public function traceRenderComplete()
    {
        foreach ($this->tracers as $tracer) {
            $tracer->onRenderComplete();
        }
    }
}
