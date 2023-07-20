<?php

namespace Statamic\Query\Traits;

use Closure;
use InvalidArgumentException;
use Statamic\Facades;
use Statamic\Fieldtypes;

trait QueriesRelationships
{
    /**
     * Add a relationship count / exists condition to the query.
     *
     * @param  string  $relation
     * @param  string  $operator
     * @param  int  $count
     * @param  string  $boolean
     * @param  \Closure|null  $callback
     * @return \Statamic\Query\Builder|static
     *
     * @throws \RuntimeException
     */
    public function has($relation, $operator = '>=', $count = 1, $boolean = 'and', Closure $callback = null)
    {
        [$relationQueryBuilder, $relationField] = $this->getRelationQueryBuilderAndField($relation);

        if (! $callback) {
            return $this->whereJsonLength($relation, $operator, $count, $boolean);
        }

        $ids = $relationQueryBuilder
            ->where($callback)
            ->get(['id'])
            ->map(fn ($item) => $item->id())
            ->all();

        $maxItems = $relationField->config()['max_items'] ?? 0;

        if ($maxItems == 1) {
            return $this->whereIn($relation, $ids);
        }

        if (empty($ids)) {
            return $this->whereJsonContains($relation, ['']);
        }

        return $this->where(function ($subquery) use ($relation, $ids) {
            foreach ($ids as $count => $id) {
                $subquery->{$count == 0 ? 'whereJsonContains' : 'orWhereJsonContains'}($relation, [$id]);
            }
        });
    }

    /**
     * Add a relationship count / exists condition to the query with an "or".
     *
     * @param  string  $relation
     * @param  string  $operator
     * @param  int  $count
     * @return \Statamic\Query\Builder|static
     */
    public function orHas($relation, $operator = '>=', $count = 1)
    {
        return $this->has($relation, $operator, $count, 'or');
    }

    /**
     * Add a relationship count / exists condition to the query.
     *
     * @param  string  $relation
     * @param  string  $boolean
     * @param  \Closure|null  $callback
     * @return \Statamic\Query\Builder|static
     */
    public function doesntHave($relation, $boolean = 'and', Closure $callback = null)
    {
        return $this->{$boolean == 'and' ? 'where' : 'orwhere'}(function ($subquery) use ($relation, $callback) {
            return $subquery->whereNull($relation)
                ->orHas($relation, '<', 1, 'and', $callback);
        });
    }

    /**
     * Add a relationship count / exists condition to the query with an "or".
     *
     * @param  string  $relation
     * @return \Statamic\Query\Builder|static
     */
    public function orDoesntHave($relation)
    {
        return $this->doesntHave($relation, 'or');
    }

    /**
     * Add a relationship count / exists condition to the query with where clauses.
     *
     * @param  string  $relation
     * @param  \Closure|null  $callback
     * @param  string  $operator
     * @param  int  $count
     * @return \Statamic\Query\Builder|static
     */
    public function whereHas($relation, Closure $callback = null, $operator = '>=', $count = 1)
    {
        return $this->has($relation, $operator, $count, 'and', $callback);
    }

    /**
     * Add a relationship count / exists condition to the query with where clauses and an "or".
     *
     * @param  string  $relation
     * @param  \Closure|null  $callback
     * @param  string  $operator
     * @param  int  $count
     * @return \Statamic\Query\Builder|static
     */
    public function orWhereHas($relation, Closure $callback = null, $operator = '>=', $count = 1)
    {
        return $this->has($relation, $operator, $count, 'or', $callback);
    }

    /**
     * Add a relationship count / exists condition to the query with where clauses.
     *
     * @param  string  $relation
     * @param  \Closure|null  $callback
     * @return \Statamic\Query\Builder|static
     */
    public function whereDoesntHave($relation, Closure $callback = null)
    {
        return $this->doesntHave($relation, 'and', $callback);
    }

    /**
     * Add a relationship count / exists condition to the query with where clauses and an "or".
     *
     * @param  string  $relation
     * @param  \Closure|null  $callback
     * @return \Statamic\Query\Builder|static
     */
    public function orWhereDoesntHave($relation, Closure $callback = null)
    {
        return $this->doesntHave($relation, 'or', $callback);
    }

    /**
     * Add a basic where clause to a relationship query.
     *
     * @param  string  $relation
     * @param  \Closure|string|array|\Illuminate\Contracts\Database\Query\Expression  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return \Statamic\Query\Builder|static
     */
    public function whereRelation($relation, $column, $operator = null, $value = null)
    {
        return $this->whereHas($relation, function ($query) use ($column, $operator, $value) {
            if ($column instanceof Closure) {
                $column($query);
            } else {
                $query->where($column, $operator, $value);
            }
        });
    }

    /**
     * Add an "or where" clause to a relationship query.
     *
     * @param  string  $relation
     * @param  \Closure|string|array|\Illuminate\Contracts\Database\Query\Expression  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return \Statamic\Query\Builder|static
     */
    public function orWhereRelation($relation, $column, $operator = null, $value = null)
    {
        return $this->orWhereHas($relation, function ($query) use ($column, $operator, $value) {
            if ($column instanceof Closure) {
                $column($query);
            } else {
                $query->where($column, $operator, $value);
            }
        });
    }

    /**
     * Get the blueprints available to this query builder
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getBlueprintsForRelations()
    {
        return collect();
    }

    /**
     * Get the query builder and field for the relation we are querying (if they exist)
     *
     * @param  string  $relation
     * @return \Statamic\Query\Builder
     */
    protected function getRelationQueryBuilderAndField($relation)
    {
        $relationField = $this->getBlueprintsForRelations()
            ->flatMap(function ($blueprint) use ($relation) {
                return $blueprint->fields()->all()->map(function ($field) use ($relation) {
                    if ($field->handle() == $relation && $field->fieldtype()->isRelationship()) {
                        return $field;
                    }
                })
                    ->filter()
                    ->values();
            })
            ->filter()
            ->first();

        if (! $relationField) {
            throw new InvalidArgumentException("Relation {$relation} does not exist");
        }

        $queryBuilder = $relationField->fieldtype()->relationshipQueryBuilder();

        if (! $queryBuilder) {
            throw new InvalidArgumentException("Relation {$relation} does not support subquerying");
        }

        return [$queryBuilder, $relationField];
    }
}
