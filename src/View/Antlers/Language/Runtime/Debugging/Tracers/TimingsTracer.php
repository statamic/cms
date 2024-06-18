<?php

namespace Statamic\View\Antlers\Language\Runtime\Debugging\Tracers;

use Statamic\View\Antlers\Language\Nodes\AbstractNode;
use Statamic\View\Antlers\Language\Nodes\AntlersNode;
use Statamic\View\Antlers\Language\Nodes\LiteralNode;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Statamic\View\Antlers\Language\Runtime\Tracing\RuntimeTracerContract;

class TimingsTracer implements RuntimeTracerContract
{
    const KEY_LINE = 'l';
    const KEY_END_LINE = 'el';
    const KEY_CHAR = 'c';
    const KEY_END_CHAR = 'ec';
    const KEY_TIMES = 't';
    const KEY_ELAPSED = 'e';
    const KEY_SOURCE = 's';
    const KEY_IS_ANTLERS = 'a';

    protected $timings = [];

    protected $nodeStarts = [];

    public function onEnter(AbstractNode $node)
    {
        if ($node instanceof LiteralNode || ($node instanceof AntlersNode && $node->isClosingTag)) {
            return;
        }
        if (! array_key_exists($node->refId, $this->nodeStarts)) {
            $this->nodeStarts[$node->refId] = [];
            $this->timings[$node->refId] = [
                self::KEY_CHAR => $node->startPosition->char,
                self::KEY_LINE => $node->startPosition->line,
                self::KEY_END_CHAR => $node->endPosition->char,
                self::KEY_END_LINE => $node->endPosition->line,
                self::KEY_TIMES => 1,
                self::KEY_ELAPSED => 0,
                self::KEY_SOURCE => GlobalRuntimeState::$currentExecutionFile,
                self::KEY_IS_ANTLERS => ($node instanceof AntlersNode),
            ];
        } else {
            $this->timings[$node->refId][self::KEY_TIMES] += 1;
        }

        $this->nodeStarts[$node->refId][] = round(microtime(true) * 1000);
    }

    public function onExit(AbstractNode $node, $runtimeContent)
    {
        if ($node instanceof LiteralNode || ($node instanceof AntlersNode && $node->isClosingTag)) {
            return;
        }

        $curTime = round(microtime(true) * 1000);
        if (array_key_exists($node->refId, $this->nodeStarts)) {
            $lastTime = array_pop($this->nodeStarts[$node->refId]);

            $this->timings[$node->refId][self::KEY_ELAPSED] += ($curTime - $lastTime);
            $this->nodeStarts[$node->refId][] = $curTime;
        }
    }

    public function onRenderComplete()
    {
    }

    public function getTimings()
    {
        return array_values($this->timings);
    }
}
