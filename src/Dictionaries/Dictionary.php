<?php

namespace Statamic\Dictionaries;

use Illuminate\Support\Collection;
use Statamic\Extend\HasHandle;
use Statamic\Extend\HasTitle;
use Statamic\Extend\RegistersItself;

abstract class Dictionary
{
    use HasHandle, HasTitle, RegistersItself;

    protected static $binding = 'dictionaries';

    abstract public function all(): array;

    abstract public function get(string $option);

    // Goal: To have an easy way to point at a JSON or YAML file and have it return its data
    // If the data is a key/value pair, it should return the key as the value and the value as the label
    // If it's not, try and identify the key and value and return them as such
}
