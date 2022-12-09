<?php

namespace Statamic\Fieldtypes\Bard;

use ProseMirrorToHtml\Nodes\Node;

class HeadingNode extends Node
{
    protected $nodeType = 'heading';

    public function tag()
    {
        $attrs = [];
        if (isset($this->node->attrs)) {
            if (isset($this->node->attrs->id)) {
                $attrs['id'] = $this->node->attrs->id;
            }
        }

        return [
            [
                'tag' =>  "h{$this->node->attrs->level}",
                'attrs' => $attrs,
            ],
        ];
    }
}
