<?php

namespace Statamic\View\Antlers\Language\Runtime\Tracing;

use Statamic\View\Antlers\Language\Runtime\NodeProcessor;

/** @internal */
interface ProcessorAwareTracerContract
{
    public function setNodeProcessor(NodeProcessor $processor): void;
}
