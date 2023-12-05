<?php

namespace Statamic\Query\Scopes;

use Statamic\Extend\HasHandle;
use Statamic\Extend\RegistersItself;
use Statamic\Statamic;

abstract class Scope
{
    use HasHandle, RegistersItself;

    protected static $binding = 'scopes';
    protected static $builders;

    /**
     * Apply the scope to a given query builder.
     *
     * @param  \Statamic\Query\Builder  $builder
     * @param  array  $values
     * @return void
     */
    abstract public function apply($query, $values);

    /**
     * Return the query builders that this scope supports.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function builders()
    {
        return collect(static::$builders)->map(function ($builder) {
            return get_class(Statamic::query($builder));
        });
    }
}
