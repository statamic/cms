<?php

namespace Statamic\Query\Concerns;

use Closure;
use InvalidArgumentException;

trait QueriesRelationships
{
    /**
     * Add a relationship count / exists condition to the query.
     *
     * @param  string  $relation
     * @param  string  $operator
     * @param  int  $count
     * @param  string  $boolean
     * @return \Statamic\Query\Builder|static
     *
     * @throws \InvalidArgumentException
     */
    public function has($relation, $operator = '>=', $count = 1, $boolean = 'and', ?Closure $callback = null)
    {
        if (str_contains($relation, '.')) {
            throw new InvalidArgumentException('Nested relations are not supported');
        }

        [$relationQueryBuilder, $relationField] = $this->getRelationQueryBuilderAndField($relation);

        $maxItems = $relationField->config()['max_items'] ?? 0;
        $negate = in_array($operator, ['!=', '<']);

        if (! $callback) {
            if ($maxItems == 1) {
                $method = $boolean == 'and' ? 'whereNull' : 'orWhereNull';
                if (! $negate) {
                    $method = str_replace('Null', 'NotNull', $method);
                }

                return $this->$method($relation);
            }

            return $this->{$boolean == 'and' ? 'whereJsonLength' : 'orWhereJsonLength'}($relation, $operator, $count);
        }

        if ($count != 1) {
            throw new InvalidArgumentException('Counting with subqueries in has clauses is not supported');
        }

        // Get the "IDs" - but really it's the values that are stored in the content.
        // In some cases, like taxonomy term fields, the values saved to the content
        // are not the actual IDs. e.g. term slugs will get saved when the field
        // is only configured with a single taxonomy.
        $idMapFn = $relationField->fieldtype()->relationshipQueryIdMapFn() ?? fn ($item) => $item->id();

        $ids = $relationQueryBuilder
            ->where($callback)
            ->get(['id'])
            ->map($idMapFn)
            ->all();

        if ($maxItems == 1) {
            $method = $boolean == 'and' ? 'whereIn' : 'orWhereIn';
            if ($negate) {
                $method = str_replace('here', 'hereNot', $method);
            }

            return $this->$method($relation, $ids);
        }

        if (empty($ids)) {
            return $this->{$boolean == 'and' ? 'whereJsonContains' : 'orWhereJsonContains'}($relation, ['']);
        }

        return $this->{$boolean == 'and' ? 'where' : 'orWhere'}(function ($subquery) use ($ids, $negate, $relation) {
            foreach ($ids as $count => $id) {
                $method = $count == 0 ? 'whereJsonContains' : 'orWhereJsonContains';
                if ($negate) {
                    $method = str_replace('Contains', 'DoesntContain', $method);
                }

                $subquery->$method($relation, [$id]);
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
     * @return \Statamic\Query\Builder|static
     */
    public function doesntHave($relation, $boolean = 'and', ?Closure $callback = null)
    {
        return $this->has($relation, '<', 1, $boolean, $callback);
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
     * @param  string  $operator
     * @param  int  $count
     * @return \Statamic\Query\Builder|static
     */
    public function whereHas($relation, ?Closure $callback = null, $operator = '>=', $count = 1)
    {
        return $this->has($relation, $operator, $count, 'and', $callback);
    }

    /**
     * Add a relationship count / exists condition to the query with where clauses and an "or".
     *
     * @param  string  $relation
     * @param  string  $operator
     * @param  int  $count
     * @return \Statamic\Query\Builder|static
     */
    public function orWhereHas($relation, ?Closure $callback = null, $operator = '>=', $count = 1)
    {
        return $this->has($relation, $operator, $count, 'or', $callback);
    }

    /**
     * Add a relationship count / exists condition to the query with where clauses.
     *
     * @param  string  $relation
     * @return \Statamic\Query\Builder|static
     */
    public function whereDoesntHave($relation, ?Closure $callback = null)
    {
        return $this->doesntHave($relation, 'and', $callback);
    }

    /**
     * Add a relationship count / exists condition to the query with where clauses and an "or".
     *
     * @param  string  $relation
     * @return \Statamic\Query\Builder|static
     */
    public function orWhereDoesntHave($relation, ?Closure $callback = null)
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
