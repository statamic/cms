<?php

namespace Tests\View\Instrumentation\Support;

use Statamic\View\Antlers\Language\Nodes\AbstractNode;
use Statamic\View\Antlers\Language\Runtime\Tracing\RuntimeTracerContract;

class RecordingLegacyTracer implements RuntimeTracerContract
{
    public array $entered = [];

    public array $exited = [];

    public array $exits = [];

    public int $completions = 0;

    public function onEnter(AbstractNode $node)
    {
        $this->entered[] = $node;
    }

    public function onExit(AbstractNode $node, $runtimeContent)
    {
        $this->exited[] = $node;
        $this->exits[] = [$node, $runtimeContent];
    }

    public function onRenderComplete()
    {
        $this->completions++;
    }
}
