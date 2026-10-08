<?php

namespace Statamic\Fieldtypes\Bard\Marks;

use Tiptap\Marks\Strike as TiptapStrike;
use Tiptap\Utils\HTML;

class Strike extends TiptapStrike
{
    public function renderHTML($mark, $HTMLAttributes = [])
    {
        return [
            'strike',
            HTML::mergeAttributes($this->options['HTMLAttributes'], $HTMLAttributes),
            0,
        ];
    }
}
