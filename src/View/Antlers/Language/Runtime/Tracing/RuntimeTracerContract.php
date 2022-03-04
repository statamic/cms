<?php

namespace Statamic\View\Antlers\Language\Runtime\Tracing;

use Statamic\View\Antlers\Language\Nodes\AbstractNode;

interface RuntimeTracerContract
{
    /**
     * Invoked when the runtime is about to process a node.
     *
     * @param  AbstractNode  $node  The node to process.
     * @return mixed
     */
    public function onEnter(AbstractNode $node);

    /**
     * Invoked when the runtime has finished processing the reference node.
     *
     * @param  AbstractNode  $node  The node evaluated node.
     * @param  string|mixed  $runtimeContent  The evaluated runtime content.
     * @return mixed
     */
    public function onExit(AbstractNode $node, $runtimeContent);

    /**
     * Invoked when the runtime has been instructed that rendering is complete.
     */
    public function onRenderComplete();
}
