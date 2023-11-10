<?php

namespace Statamic\Fieldtypes\Bard\Marks;

use Tiptap\Core\Mark;
use Tiptap\Utils\HTML;

class Small extends Mark
{
    public static $name = 'small';

    public function addOptions()
    {
        return [
            'HTMLAttributes' => [],
        ];
    }

    public function parseHTML()
    {
        return [
            [
                'tag' => 'small',
            ],
        ];
    }

    public function renderHTML($mark, $HTMLAttributes = [])
    {
        return [
            'small',
            HTML::mergeAttributes($this->options['HTMLAttributes'], $HTMLAttributes),
            0,
        ];
    }
}
