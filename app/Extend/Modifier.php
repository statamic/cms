<?php

namespace Statamic\Extend;

/**
 * Modify values within templates
 */
class Modifier
{
    use RegistersItself, HasHandle;

    protected static $binding = 'modifiers';
}
