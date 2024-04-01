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

    public function __construct(
        private $query, private $bindings
    ) {
        $data = $query->prepareForFakeQuery();
        $this->table = $this->getTableName($query);
        $this->wheres = $data['wheres'];
        $this->columns = $data['columns'];
        $this->orderBys = $data['orderBys'];
        $this->limit = $data['limit'];
        $this->offset = $data['offset'];
    }

    public function dump(): string
    {
        $query = 'select '.$this->dumpColumns().' from '.$this->table;

        if (! empty($this->wheres)) {
            $query .= ' where '.$this->dumpWheres();
        }

        $query .= $this->dumpLimits();
        $query .= $this->dumpOrderBys();

        return $query;
    }
}
