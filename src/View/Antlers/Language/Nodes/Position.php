<?php

namespace Statamic\View\Antlers\Language\Nodes;

class Position
{
    public $index = -1;
    public $offset = 0;
    public $line = 0;
    public $char = 0;

    public function isBefore(Position $position)
    {
        if ($position->line > $this->line) {
            return true;
        }

        if ($position->line == $this->line && $this->offset < $position->offset) {
            return true;
        }

        return false;
    }
}
