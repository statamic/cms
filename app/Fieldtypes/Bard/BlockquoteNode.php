<?php

namespace Statamic\Fieldtypes\Bard;

use Scrumpy\ProseMirrorToHtml\Nodes\Node;

class BlockquoteNode extends Node
{
    public function matching()
    {
        return $this->node->type === 'blockquote';
    }

    public function tag()
    {
        return 'blockquote';
    }
}
