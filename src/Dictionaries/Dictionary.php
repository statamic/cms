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
     * Returns a key/value array of options.
     */
    abstract public function options(?string $search = null): array;

    /**
     * Returns a single option.
     */
    abstract public function get(string $key): array;

    public function context($context)
    {
        $this->context = $context;

        return $this;
    }

    protected function fieldItems()
    {
        return $this->fields;
    }

    abstract public function getGqlType();
}
