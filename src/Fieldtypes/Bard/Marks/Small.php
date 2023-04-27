<?php

namespace Statamic\Fieldtypes\Bard\Marks;

use Tiptap\Core\Mark;

class Small extends Mark
{
    public static $name = 'small';

    public function parseHTML()
    {
        return [
            [
                'tag' => 'small',
            ],
        ];
    }

    public function renderHTML($mark)
    {
        return ['small', 0];
    }
}
