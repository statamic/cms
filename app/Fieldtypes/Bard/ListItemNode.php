<?php

namespace Statamic\Fieldtypes\Bard;

use Scrumpy\ProseMirrorToHtml\Nodes\Node;

class ListItemNode extends Node
{
    public function matching()
    {
        return $this->node->type === 'list_item';
    }

    public function tag()
    {
        return 'li';
    }
}
