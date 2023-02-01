<?php

namespace Statamic\Fieldtypes\Bard;

use Tiptap\Core\Node;

class SetNode extends Node
{
    public static $name = 'set';

    public function parseHTML()
    {
        return [
            [
                'tag' => 'set',
            ],
        ];
    }

    public function renderHTML($node, $HTMLAttributes = [])
    {
        return ['content' => "<set>{$node->index}</set>"];
    }
}
