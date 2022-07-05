<?php

namespace Statamic\Fieldtypes\Bard\Marks;

use ProseMirrorToHtml\Marks\Mark;

class Small extends Mark
{
    protected $markType = 'small';
    protected $tagName = 'small';
}
