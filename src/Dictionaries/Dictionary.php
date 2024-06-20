<?php

namespace Statamic\Dictionaries;

use Statamic\Extend\HasHandle;
use Statamic\Extend\HasTitle;
use Statamic\Extend\RegistersItself;

abstract class Dictionary
{
    use HasHandle, HasTitle, RegistersItself;

    protected static $binding = 'dictionaries';

    abstract public function options(?string $search = null): array;

    abstract public function get(string $option);
}
