<?php

namespace Statamic\View\Antlers\Language\Nodes;

class ParserFailNode extends AntlersNode
{
    public static function makeWithStartPosition(Position $startPosition)
    {
        $newNode = new ParserFailNode();
        $newNode->startPosition = $startPosition;
        $newNode->endPosition = $startPosition;

        return $newNode;
    }
}
