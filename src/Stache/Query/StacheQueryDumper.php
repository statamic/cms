<?php

namespace Statamic\Stache\Query;

use Statamic\Stache\Query\Concerns\DumpsQueryParts;
use Statamic\Stache\Query\Concerns\DumpsQueryValues;
use Statamic\Stache\Query\Concerns\DumpsWheres;

class StacheQueryDumper
{
    use DumpsQueryParts, DumpsQueryValues, DumpsWheres;

    protected $wheres = [];
    protected $columns = [];
    protected $orderBys = [];
    protected $limit;
    protected $offset;
    protected $store;
    protected $dumpActualValues = false;

    public function __construct(
        $store, $wheres, $columns, $orderBys, $limit, $offset
    ) {
        $this->store = $store;
        $this->wheres = $wheres;
        $this->columns = $columns;
        $this->orderBys = $orderBys;
        $this->limit = $limit;
        $this->offset = $offset;
    }

    public function setDumpActualValues($dumpValues): self
    {
        $this->dumpActualValues = $dumpValues;

        return $this;
    }

    public function dump(): string
    {
        $query = 'SELECT '.$this->dumpColumns()."\n".'FROM '.get_class($this->store);
        $query .= $this->dumpWheres();
        $query .= $this->dumpLimits();
        $query .= $this->dumpOrderBys();

        return $query;
    }
}
