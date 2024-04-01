<?php

namespace Statamic\Query\Concerns;

use Illuminate\Database\Events\QueryExecuted;
use Statamic\Query\Dumper\Dumper;

trait FakesQueries
{
    protected function withFakeQueryLogging(\Closure $callback)
    {
        if (! config('statamic.system.fake_sql_queries', false)) {
            return $callback();
        }

        $startTime = microtime(true);

        $value = $callback();

        $time = round((microtime(true) - $startTime) * 1000, 2);

        event(new QueryExecuted(
            ($sql = new Dumper($this))->dump(),
            $sql->bindings()->all(),
            $time,
            $sql->connection()
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
