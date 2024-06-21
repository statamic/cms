<?php

namespace Statamic\Dictionaries;

use Statamic\Extend\HasHandle;
use Statamic\Extend\HasTitle;
use Statamic\Extend\RegistersItself;

abstract class Dictionary
{
    use HasHandle, HasTitle, RegistersItself;

    protected static $binding = 'dictionaries';

    /**
     * Returns all options, optionally filtered by a search term.
     */
    abstract public function options(?string $search = null): array;

    /**
     * Returns data for a single option, given the option's key.
     */
    abstract public function get(string $key): string|array;
}
