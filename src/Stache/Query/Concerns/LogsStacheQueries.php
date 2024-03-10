<?php

namespace Statamic\Stache\Query\Concerns;

use Illuminate\Database\Connection;
use Illuminate\Database\Events\QueryExecuted;
use Statamic\Stache\Query\StacheQueryDumper;

trait LogsStacheQueries
{
    protected $loggerEnabled = false;
    protected $logRealValues = false;

    public function dumpStacheQuery()
    {
        return (new StacheQueryDumper(
            $this->store,
            $this->wheres,
            $this->columns,
            $this->orderBys,
            $this->limit,
            $this->offset
        ))
            ->setDumpActualValues($this->logRealValues)
            ->dump();
    }

    protected function emitQueryEvent($startTime, $endTime): void
    {
        if (! $this->loggerEnabled) {
            return;
        }

        event(new QueryExecuted(
            $this->dumpStacheQuery(),
            [],
            ($endTime - $startTime) / 1000000,
            new Connection(fn () => null, 'Stache')
        ));
    }
}
