<?php

namespace Statamic\Fieldtypes\Bard;

use Scrumpy\ProseMirrorToHtml\Nodes\Node;

class UnorderedListNode extends Node
{
    public function matching()
    {
        return $this->node->type === 'unordered_list';
    }

    public function tag()
    {
        return 'ul';
    }
}
