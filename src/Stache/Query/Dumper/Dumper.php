<?php

namespace Statamic\Stache\Query\Dumper;

use Statamic\Stache\Query\Dumper\Concerns\DumpsQueryParts;
use Statamic\Stache\Query\Dumper\Concerns\DumpsQueryValues;
use Statamic\Stache\Query\Dumper\Concerns\DumpsWheres;

class Dumper
{
    use DumpsQueryParts, DumpsQueryValues, DumpsWheres;

    protected $wheres = [];
    protected $columns = [];
    protected $orderBys = [];
    protected $limit;
    protected $offset;
    protected $store;
    protected $extraFrom = '';
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

    public function setExtraFromStatement($extraFrom): self
    {
        $this->extraFrom = $extraFrom;

        return $this;
    }

    public function setDumpActualValues($dumpValues): self
    {
        $this->dumpActualValues = $dumpValues;

        return $this;
    }

    public function dump(): string
    {
        $query = 'SELECT '.$this->dumpColumns()."\n".'FROM '.get_class($this->store);

        if ($this->extraFrom) {
            $query .= '{'.$this->extraFrom.'}';
        }

        $query .= $this->dumpWheres();
        $query .= $this->dumpLimits();
        $query .= $this->dumpOrderBys();

        return $query;
    }
}
