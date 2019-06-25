<?php

namespace Statamic\Fieldtypes\Bard;

use Scrumpy\ProseMirrorToHtml\Nodes\Node;

class OrderedListNode extends Node
{
    public function matching()
    {
        return $this->node->type === 'ordered_list';
    }

    public function tag()
    {
        return 'ol';
    }
}
