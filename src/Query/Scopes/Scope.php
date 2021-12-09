<?php

namespace Statamic\Query\Scopes;

use Statamic\Extend\HasHandle;
use Statamic\Extend\RegistersItself;

abstract class Scope
{
    use RegistersItself, HasHandle;

    protected static $binding = 'scopes';

    /**
     * Apply the scope to a given query builder.
     *
     * @param  \Statamic\Query\Builder  $builder
     * @param  array  $values
     * @return void
     */
    abstract public function apply($query, $values);
}
