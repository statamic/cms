<?php

namespace Statamic\View\Antlers\Language\Runtime\Tracing;

use Statamic\View\Antlers\Language\Nodes\AbstractNode;

interface NodeVisitorContract
{
    /**
     * Invoked on each parent render node in the document.
     *
     * @param  AbstractNode  $node  The node.
     * @return mixed
     */
    public function visit(AbstractNode $node);
}
