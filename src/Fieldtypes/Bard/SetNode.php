<?php

namespace Statamic\Fieldtypes\Bard;

use ProseMirrorToHtml\Nodes\Node;

class SetNode extends Node
{
    public function matching()
    {
        return $this->node->type === 'set';
    }

    public function tag()
    {
        return 'set';
    }

    public function text()
    {
        return $this->node->index;
    }
}
