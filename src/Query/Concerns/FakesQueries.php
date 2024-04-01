<?php

namespace Statamic\Query\Concerns;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Statamic\Query\Dumper\Dumper;

trait FakesQueries
{
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
            (new Dumper($this, $bindings))->dump(),
            $bindings->all(),
            $time,
            app($key)
        ));

        return $value;
    }

    public function prepareForFakeQuery(): array
    {
        return [
            'wheres' => $this->wheres,
            'columns' => $this->columns,
            'orderBys' => $this->orderBys,
            'limit' => $this->limit,
            'offset' => $this->offset,
        ];
    }
}
