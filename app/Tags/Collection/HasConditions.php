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
            case 'ends_with':
                return $this->queryEndsWithCondition($query, $field, $value);
            case 'doesnt_end_with':
                return $this->queryDoesntEndWithCondition($query, $field, $value);
            case 'greater_than':
            case 'gt':
                return $this->queryGreaterThanCondition($query, $field, $value);
            case 'less_than':
            case 'lt':
                return $this->queryLessThanCondition($query, $field, $value);
            case 'greater_than_or_equal_to':
            case 'gte':
                return $this->queryGreaterThanOrEqualToCondition($query, $field, $value);
            case 'less_than_or_equal_to':
            case 'lte':
                return $this->queryLessThanOrEqualToCondition($query, $field, $value);
            case 'matches':
            case 'match':
            case 'regex':
                return $this->queryMatchesRegexCondition($query, $field, $value);
            case 'doesnt_match':
                return $this->queryDoesntMatchRegexCondition($query, $field, $value);
        }

        if (Str::startsWith($condition, 'is_')) {
            $this->queryBooleanCondition($query, $field, $condition, $value);
        }
    }

    public function queryBooleanCondition($query, $field, $condition, $value)
    {
        $regexOperator = $value ? 'regexp' : 'not regexp';

        switch ($condition) {
            //
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

    public function queryEndsWithCondition($query, $field, $value)
    {
        $query->where($field, 'like', "%{$value}");
    }

    public function queryDoesntEndWithCondition($query, $field, $value)
    {
        $query->where($field, 'not like', "%{$value}");
    }

    public function queryGreaterThanCondition($query, $field, $value)
    {
        $query->where($field, '>', $value);
    }

    public function queryLessThanCondition($query, $field, $value)
    {
        $query->where($field, '<', $value);
    }

    public function queryGreaterThanOrEqualToCondition($query, $field, $value)
    {
        $query->where($field, '>=', $value);
    }

    public function queryLessThanOrEqualToCondition($query, $field, $value)
    {
        $query->where($field, '<=', $value);
    }

    public function queryMatchesRegexCondition($query, $field, $value)
    {
        $query->where($field, 'regexp', $value);
    }

    public function queryDoesntMatchRegexCondition($query, $field, $value)
    {
        $query->where($field, 'not regexp', $value);
    }
}
