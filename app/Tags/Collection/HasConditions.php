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
        if ($this->isBooleanCondition($condition)) {
            return $this->queryBooleanCondition($query, $field, $condition, $value);
        }

        switch ($condition) {
            case 'is':
            case 'equals':
                return $this->queryIsCondition($query, $field, $value);
            case 'not':
            case 'isnt':
            case 'aint':
            case '¯\\_(ツ)_/¯':
                return $this->queryNotCondition($query, $field, $value);
            case 'exists':
            case 'isset':
                return $this->queryExistsCondition($query, $field, $value);
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
    }

    public function queryBooleanCondition($query, $field, $condition, $value)
    {
        $regexOperator = $value ? 'regexp' : 'not regexp';
        $comparisonOperator = $value ? '=' : '!=';

        switch ($condition) {
            case 'is_alpha':
                return $this->queryIsAlphaCondition($query, $field, $regexOperator);
            case 'is_alpha_numeric':
                return $this->queryIsAlphaNumericCondition($query, $field, $regexOperator);
            case 'is_numeric':
                return $this->queryIsNumericCondition($query, $field, $regexOperator);
            case 'is_url':
                return $this->queryIsUrlCondition($query, $field, $regexOperator);
            case 'is_embeddable':
                return $this->queryIsEmbeddableCondition($query, $field, $regexOperator);
            case 'is_email':
                return $this->queryIsEmailCondition($query, $field, $regexOperator);
            case 'is_empty':
            case 'is_blank':
            case 'doesnt_exist':
            case 'not_set':
            case 'isnt_set':
            case 'null':
                return $this->queryIsEmptyCondition($query, $field, $comparisonOperator);
            case 'exists':
            case 'isset':
                return $this->queryIsEmptyCondition($query, $field, $value ? '!=' : '=');
            case 'is_numberwang':
                return $this->queryIsNumberwangCondition($query, $field, $regexOperator);
        }
    }

    public function isBooleanCondition($condition)
    {
        $nonConventionalBooleanConditions = [
            'exists',
            'isset',
            'doesnt_exist',
            'not_set',
            'isnt_set',
            'null',
        ];

        return Str::startsWith($condition, 'is_') || in_array($condition, $nonConventionalBooleanConditions);
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

    public function queryMatchesRegexCondition($query, $field, $pattern)
    {
        if (Str::startsWith($pattern, '/')) {
            $pattern = $this->removeRegexDelimitersAndModifiers($pattern);
        }

        $query->where($field, 'regexp', $pattern);
    }

    public function queryDoesntMatchRegexCondition($query, $field, $pattern)
    {
        if (Str::startsWith($pattern, '/')) {
            $pattern = $this->removeRegexDelimitersAndModifiers($pattern);
        }

        $query->where($field, 'not regexp', $pattern);
    }

    public function queryIsAlphaCondition($query, $field, $regexOperator)
    {
        $query->where($field, $regexOperator, '^[a-z]+$');
    }

    public function queryIsAlphaNumericCondition($query, $field, $regexOperator)
    {
        $query->where($field, $regexOperator, '^[a-z0-9]+$');
    }

    public function queryIsNumericCondition($query, $field, $regexOperator)
    {
        $query->where($field, $regexOperator, '^[0-9]*(\.[0-9]+)?$');
    }

    public function queryIsUrlCondition($query, $field, $regexOperator)
    {
        $query->where($field, $regexOperator, '^(https|http):\/\/[^\ ]+$');
    }

    public function queryIsEmbeddableCondition($query, $field, $regexOperator)
    {
        $domainPatterns = collect([
            'youtube',
            'vimeo',
            'youtu.be',
        ])->implode('|');

        $query->where($field, $regexOperator, "^(https|http):\/\/[^\ ]*({$domainPatterns})[^\/]*\/[^\ ]+$");
    }

    public function queryIsEmailCondition($query, $field, $regexOperator)
    {
        $query->where($field, $regexOperator, '^[^\ ]+@[^\ ]+\.[^\ ]+$');
    }

    public function queryIsEmptyCondition($query, $field, $comparisonOperator)
    {
        // TODO: Add `whereNull()` and `whereNotNull()` to our query builder so that this can be Eloquent compatible.
        $query->where($field, $comparisonOperator, null);
    }

    public function queryIsNumberwangCondition($query, $field, $regexOperator)
    {
        $query->where($field, $regexOperator, "^(1|22|7|9|1002|2\.3|15|109876567|31)$");
    }

    /**
     * This is for backwards compatibility, because v2's regex conditions required delimiters.
     * Passing delimiters doesn't work with Eloquent and `regexp`, so we remove them from
     * the user's pattern if passed, so that regex conditions will work as expected.
     *
     * @param string $pattern
     * @return string
     */
    protected function removeRegexDelimitersAndModifiers($pattern)
    {
        return preg_replace(['/^\//', '/\/\w*$/'], ['', ''], $pattern);
    }
}
