<?php

namespace Statamic\Tags\Collection;

use Statamic\API\Str;

trait HasConditions
{
    public function queryConditions($query)
    {
        foreach ($this->parameters as $param => $value) {
            $this->queryCondition(
                $query,
                explode(':', $param)[0],
                explode(':', $param)[1] ?? false,
                $value
            );
        }
    }

    public function queryCondition($query, $field, $condition, $value)
    {
        switch ($condition) {
            case 'is':
            case 'equals':
                return $this->queryIsCondition($query, $field, $value);
            case 'not':
            case 'isnt':
            case 'aint':
            case '¯\\_(ツ)_/¯':
                return $this->queryNotCondition($query, $field, $value);
            case 'contains':
                return $this->queryContainsCondition($query, $field, $value);
            case 'doesnt_contain':
                return $this->queryDoesntContainCondition($query, $field, $value);
            case 'starts_with':
            case 'begins_with':
                return $this->queryStartsWithCondition($query, $field, $value);
            case 'doesnt_start_with':
            case 'doesnt_begin_with':
                return $this->queryDoesntStartWithCondition($query, $field, $value);
        }
    }

    public function queryIsCondition($query, $field, $value)
    {
        $query->where($field, $value);
    }

    public function queryNotCondition($query, $field, $value)
    {
        $query->where($field, '!=', $value);
    }

    public function queryContainsCondition($query, $field, $value)
    {
        $query->where($field, 'like', "%{$value}%");
    }

    public function queryDoesntContainCondition($query, $field, $value)
    {
        $query->where($field, 'not like', "%{$value}%");
    }

    public function queryStartsWithCondition($query, $field, $value)
    {
        $query->where($field, 'like', "{$value}%");
    }

    public function queryDoesntStartWithCondition($query, $field, $value)
    {
        $query->where($field, 'not like', "{$value}%");
    }
}
