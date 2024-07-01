<?php

namespace Statamic\Dictionaries;

use Statamic\Extend\HasFields;
use Statamic\Extend\HasHandle;
use Statamic\Extend\HasTitle;
use Statamic\Extend\RegistersItself;

abstract class Dictionary
{
    use HasFields, HasHandle, HasTitle, RegistersItself;

    protected static $binding = 'dictionaries';

    protected $fields = [];
    protected $context = [];

    /**
     * Returns all options, optionally filtered by a search term.
     */
    abstract public function options(?string $search = null): array;

    /**
     * Returns data for a single option, given the option's key.
     */
    abstract public function get(string $key): string|array;

    public function context($context)
    {
        $this->context = $context;

        return $this;
    }

    protected function fieldItems()
    {
        return $this->fields;
    }
}
