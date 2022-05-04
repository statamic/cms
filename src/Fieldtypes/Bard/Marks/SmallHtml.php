<?php

namespace Statamic\Fieldtypes\Bard\Marks;

use HtmlToProseMirror\Marks\Mark;

class SmallHtml extends Mark
{
    public function matching()
    {
        return $this->DOMNode->nodeName === 'small';
    }

    public function data()
    {
        return [
            'type' => 'small',
        ];
    }
}
