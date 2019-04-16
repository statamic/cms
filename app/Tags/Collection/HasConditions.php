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
        }
    }

    public function queryIsCondition($query, $field, $value)
    {
        $query->where($field, $value);
    }
}
