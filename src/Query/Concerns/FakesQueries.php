<?php

namespace Statamic\Query\Concerns;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Statamic\Query\Dumper\Dumper;
use Statamic\Stache\Query\EntryQueryBuilder;

trait FakesQueries
{
    public function dumpFakeQuery($bindings): string
    {
        $extraFrom = '';

        if ($this instanceof EntryQueryBuilder) {
            if (is_array($this->collections)) {
                $extraFrom = implode(', ', $this->collections);
            }
        }

        return (new Dumper(
            Dumper::getTableName($this),
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

    protected function withFakeQueryLogging(\Closure $callback)
    {
        if (! config('statamic.system.fake_sql_queries', false)) {
            return $callback();
        }

        $startTime = hrtime(true);

        $value = $callback();

        $time = (hrtime(true) - $startTime) / 1000000;

        $bindings = collect();

        if (! app()->bound($key = 'fake-query-connection')) {
            app()->instance($key, DB::connectUsing('fake', [
                'driver' => 'sqlite',
                'database' => ':memory:',
            ]));
        }

        event(new QueryExecuted(
            $this->dumpFakeQuery($bindings),
            $bindings->all(),
            $time,
            app($key)
        ));

        return $value;
    }
}
