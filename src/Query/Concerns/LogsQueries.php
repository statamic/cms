<?php

namespace Statamic\Query\Concerns;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Statamic\Query\Dumper\Dumper;
use Statamic\Stache\Query\EntryQueryBuilder;

trait LogsQueries
{
    private static $connection;

    public function dumpStacheQuery($bindings)
    {
        $extraFrom = '';

        if ($this instanceof EntryQueryBuilder) {
            if (is_array($this->collections)) {
                $extraFrom = implode(', ', $this->collections);
            }
        }

        return (new Dumper(
            $this->store,
            $this->wheres,
            $this->columns,
            $this->orderBys,
            $this->limit,
            $this->offset,
            $bindings,
        ))
            ->setExtraFromStatement($extraFrom)
            ->dump();
    }

    protected function emitQueryEvent($startTime, $endTime): void
    {
        if (! config('statamic.system.fake_sql_queries', false)) {
            return;
        }

        $bindings = collect();

        static::$connection ??= DB::connectUsing('stache', [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);

        event(new QueryExecuted(
            $this->dumpStacheQuery($bindings),
            $bindings->all(),
            ($endTime - $startTime) / 1000000,
            static::$connection
        ));
    }
}
