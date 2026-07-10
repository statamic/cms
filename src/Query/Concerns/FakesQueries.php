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
            ($sql = $this->dumper())->dump(),
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

    public function toSql(): string
    {
        return $this->dumper()->dump();
    }

    public function toRawSql(): string
    {
        $sql = ($dumper = $this->dumper())->dump();
        $bindings = $dumper->bindings()->all();
        $connection = $dumper->connection();

        return $connection
            ->query()
            ->getGrammar()
            ->substituteBindingsIntoRawSql($sql, $connection->prepareBindings($bindings));
    }

    public function dumpRawSql(): static
    {
        dump($this->toRawSql());

        return $this;
    }

    public function ddRawSql(): void
    {
        dd($this->toRawSql());
    }

    public function ray(): static
    {
        throw_unless(function_exists('ray'), new \Exception('Ray is not installed. Run `composer require spatie/laravel-ray --dev`'));

        ray($this->toRawSql());

        return $this;
    }

    private function dumper(): Dumper
    {
        return new Dumper($this);
    }
}
