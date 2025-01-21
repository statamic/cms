<?php

namespace Statamic\Query\Dumper;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Statamic\Contracts\Query\Builder;
use Statamic\Query\Dumper\Concerns\DumpsQueryParts;
use Statamic\Query\Dumper\Concerns\DumpsQueryValues;
use Statamic\Query\Dumper\Concerns\DumpsWheres;
use Statamic\Stache\Query\Builder as StacheQueryBuilder;
use Statamic\Support\Str;

class Dumper
{
    use DumpsQueryParts, DumpsQueryValues, DumpsWheres;

    private array $wheres;
    private array $columns;
    private array $orderBys;
    private ?int $limit;
    private ?int $offset;
    private string $table;
    private Collection $bindings;

    public function __construct(private $query)
    {
        $data = $query->prepareForFakeQuery();
        $this->table = $this->getTableName($query);
        $this->wheres = $data['wheres'];
        $this->columns = $data['columns'] ?? ['*'];
        $this->orderBys = $data['orderBys'];
        $this->limit = $data['limit'];
        $this->offset = $data['offset'];
        $this->bindings = collect();
    }

    public function bindings(): Collection
    {
        return $this->bindings;
    }

    public function withBindings(Collection $bindings): self
    {
        $this->bindings = $bindings;

        return $this;
    }

    public function connection()
    {
        if (! app()->bound($key = 'fake-query-connection')) {
            app()->instance($key, DB::connectUsing('fake', [
                'driver' => 'sqlite',
                'database' => ':memory:',
            ]));
        }

        return app($key);
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

    private function getTableName(Builder $class): string
    {
        if (method_exists($class, 'getTableNameForFakeQuery')) {
            return $class->getTableNameForFakeQuery();
        }

        if ($class instanceof StacheQueryBuilder) {
            return Str::of(class_basename($class))
                ->before('QueryBuilder')
                ->lower()
                ->plural()
                ->toString();
        }

        return get_class($class);
    }
}
