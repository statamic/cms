<?php

namespace Statamic\Query\Dumper;

use Statamic\Query\Dumper\Concerns\DumpsQueryParts;
use Statamic\Query\Dumper\Concerns\DumpsQueryValues;
use Statamic\Query\Dumper\Concerns\DumpsWheres;

class Dumper
{
    use DumpsQueryParts, DumpsQueryValues, DumpsWheres;

    protected $wheres = [];
    protected $columns = [];
    protected $orderBys = [];
    protected $limit;
    protected $offset;
    protected $table;
    protected $extraFrom = '';

    private $bindings;

    public function __construct(
        $table, $wheres, $columns, $orderBys, $limit, $offset, $bindings
    ) {
        $this->table = $table;
        $this->wheres = $wheres;
        $this->columns = $columns;
        $this->orderBys = $orderBys;
        $this->limit = $limit;
        $this->offset = $offset;
        $this->bindings = $bindings;
    }

    public function setExtraFromStatement($extraFrom): self
    {
        $this->extraFrom = $extraFrom;

        return $this;
    }

    public function dump(): string
    {
        $query = 'SELECT '.$this->dumpColumns()."\n".'FROM '.$this->table;

        if ($this->extraFrom) {
            $query .= '{'.$this->extraFrom.'}';
        }

        $query .= $this->dumpWheres();
        $query .= $this->dumpLimits();
        $query .= $this->dumpOrderBys();

        return $query;
    }
}
