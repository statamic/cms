<?php

namespace Statamic\View\Antlers\Language\Runtime\Tracing;

use Statamic\View\Antlers\Language\Runtime\NodeProcessor;

/** @internal */
interface ProcessorAwareTracerContract
{
    /** @param  NodeProcessor  $processor  The processor evaluating the node. */
    public function setNodeProcessor(NodeProcessor $processor);
}
