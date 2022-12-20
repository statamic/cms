<?php

namespace Statamic\Tags\Concerns;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Fields\LabeledValue;
use Statamic\Fields\Value;
use Statamic\Support\Str;

trait QueriesConditions
{
    use GetsPipedArrayValues;

    protected function queryConditions($query)
    {
        $this->queryableConditionParams()->each(function ($value, $param) use ($query) {
            $this->queryCondition(
                $query,
                $field = explode(':', $param)[0],
                explode(':', $param)[1] ?? false,
                $this->getQueryConditionValue($value, $field)
            );
        });
    }

    protected function queryableConditionParams()
    {
        return $this->params->filter(function ($value, $param) {
            return Str::contains($param, ':');
        });
    }

    protected function isQueryingCondition($field)
    {
        return $this->queryableConditionParams()
            ->map(function ($value, $param) {
                return explode(':', $param)[0];
            })
            ->contains($field);
    }

    protected function queryCondition($query, $field, $condition, $value)
    {
        $regexOperator = $value ? 'regexp' : 'not regexp';

        switch ($condition) {
            case 'is':
            case 'equals':
                return $this->queryIsCondition($query, $field, $value);
            case 'not':
            case 'isnt':
            case 'aint':
            case '¯\\_(ツ)_/¯':
                return $this->queryNotCondition($query, $field, $value);
            case 'is_empty':
            case 'is_blank':
            case 'doesnt_exist':
            case 'not_set':
            case 'isnt_set':
            case 'null':
                return $this->queryIsEmptyCondition($query, $field, $value);
            case 'exists':
            case 'isset':
                return $this->queryIsEmptyCondition($query, $field, ! $value);
            case 'contains':
                return $this->queryContainsCondition($query, $field, $value);
            case 'doesnt_contain':
                return $this->queryDoesntContainCondition($query, $field, $value);
            case 'in':
                return $this->queryInCondition($query, $field, $value);
            case 'not_in':
                return $this->queryNotInCondition($query, $field, $value);
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
            case 'is_after':
            case 'is_future':
                return $this->queryIsAfterCondition($query, $field, $value);
            case 'is_before':
            case 'is_past':
                return $this->queryIsBeforeCondition($query, $field, $value);
            case 'is_numberwang':
                return $this->queryIsNumberwangCondition($query, $field, $regexOperator);
        }
    }

    protected function queryIsCondition($query, $field, $value)
    {
        return $query->where($field, $value);
    }

    protected function queryNotCondition($query, $field, $value)
    {
        return $query->where($field, '!=', $value);
    }

    protected function queryContainsCondition($query, $field, $value)
    {
        return $query->where($field, 'like', "%{$value}%");
    }

    protected function queryDoesntContainCondition($query, $field, $value)
    {
        return $query->where($field, 'not like', "%{$value}%");
    }

    protected function queryInCondition($query, $field, $value)
    {
        if (is_string($value)) {
            $value = $this->getPipedValues($value);
        }

        return $query->whereIn($field, $value);
    }

    protected function queryNotInCondition($query, $field, $value)
    {
        if (is_string($value)) {
            $value = $this->getPipedValues($value);
        }

        return $query->whereNotIn($field, $value);
    }

    protected function queryStartsWithCondition($query, $field, $value)
    {
        return $query->where($field, 'like', "{$value}%");
    }

    protected function queryDoesntStartWithCondition($query, $field, $value)
    {
        return $query->where($field, 'not like', "{$value}%");
    }

    protected function queryEndsWithCondition($query, $field, $value)
    {
        return $query->where($field, 'like', "%{$value}");
    }

    protected function queryDoesntEndWithCondition($query, $field, $value)
    {
        return $query->where($field, 'not like', "%{$value}");
    }

    protected function queryGreaterThanCondition($query, $field, $value)
    {
        return $query->where($field, '>', $value);
    }

    protected function queryLessThanCondition($query, $field, $value)
    {
        return $query->where($field, '<', $value);
    }

    protected function queryGreaterThanOrEqualToCondition($query, $field, $value)
    {
        return $query->where($field, '>=', $value);
    }

    protected function queryLessThanOrEqualToCondition($query, $field, $value)
    {
        return $query->where($field, '<=', $value);
    }

    protected function queryMatchesRegexCondition($query, $field, $pattern)
    {
        if (Str::startsWith($pattern, '/')) {
            $pattern = $this->removeRegexDelimitersAndModifiers($pattern);
        }

        return $query->where($field, 'regexp', $pattern);
    }

    protected function queryDoesntMatchRegexCondition($query, $field, $pattern)
    {
        if (Str::startsWith($pattern, '/')) {
            $pattern = $this->removeRegexDelimitersAndModifiers($pattern);
        }

        return $query->where($field, 'not regexp', $pattern);
    }

    protected function queryIsAlphaCondition($query, $field, $regexOperator)
    {
        return $query->where($field, $regexOperator, '^[a-z]+$');
    }

    protected function queryIsAlphaNumericCondition($query, $field, $regexOperator)
    {
        return $query->where($field, $regexOperator, '^[a-z0-9]+$');
    }

    protected function queryIsNumericCondition($query, $field, $regexOperator)
    {
        return $query->where($field, $regexOperator, '^[0-9]*(\.[0-9]+)?$');
    }

    protected function queryIsUrlCondition($query, $field, $regexOperator)
    {
        return $query->where($field, $regexOperator, '^(https|http):\/\/[^\ ]+$');
    }

    protected function queryIsEmbeddableCondition($query, $field, $regexOperator)
    {
        $domainPatterns = collect([
            'youtube',
            'vimeo',
            'youtu.be',
        ])->implode('|');

        return $query->where($field, $regexOperator, "^(https|http):\/\/[^\ ]*({$domainPatterns})[^\/]*\/[^\ ]+$");
    }

    protected function queryIsEmailCondition($query, $field, $regexOperator)
    {
        return $query->where($field, $regexOperator, '^[^\ ]+@[^\ ]+\.[^\ ]+$');
    }

    protected function queryIsEmptyCondition($query, $field, $boolean)
    {
        return $query->where($field, $boolean ? '=' : '!=', null);
    }

    protected function queryIsAfterCondition($query, $field, $value)
    {
        $comparison = is_bool($value) ? $value : true;
        $date = $this->getDateComparisonValue($value);

        return $comparison
            ? $this->queryGreaterThanCondition($query, $field, $date)
            : $this->queryLessThanCondition($query, $field, $date);
    }

    protected function queryIsBeforeCondition($query, $field, $value)
    {
        $comparison = is_bool($value) ? $value : true;
        $date = $this->getDateComparisonValue($value);

        return $comparison
            ? $this->queryLessThanCondition($query, $field, $date)
            : $this->queryGreaterThanCondition($query, $field, $date);
    }

    private function getDateComparisonValue($value)
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        if (is_string($value)) {
            return Carbon::parse($value);
        }

        return Carbon::now();
    }

    protected function queryIsNumberwangCondition($query, $field, $regexOperator)
    {
        return $query->where($field, $regexOperator, "^(1|22|7|9|1002|2\.3|15|109876567|31)$");
    }

    /**
     * This is for backwards compatibility, because v2's regex conditions required delimiters.
     * Passing delimiters doesn't work with Eloquent and `regexp`, so we remove them from
     * the user's pattern if passed, so that regex conditions will work as expected.
     *
     * @param  string  $pattern
     * @return string
     */
    protected function removeRegexDelimitersAndModifiers($pattern)
    {
        return preg_replace(['/^\//', '/\/\w*$/'], ['', ''], $pattern);
    }

    protected function getQueryConditionValue($value, $field)
    {
        if ($value instanceof Value) {
            $value = $value->value();
        }

        if ($value instanceof Carbon) {
            return $value;
        }

        if (is_array($value)) {
            $value = collect($value);
        }

        if ($value instanceof Collection) {
            $value = $value->map(function ($value) use ($field) {
                return $this->getQueryConditionValue($value, $field);
            })->all();
        }

        if ($value instanceof Augmentable) {
            $value = $value->augmentedValue($field)->value();
        }

        if ($value instanceof LabeledValue) {
            $value = $value->value();
        }

        if (is_object($value)) {
            throw new \LogicException("Cannot query [$field] using value [".get_class($value).']');
        }

        return $value;
    }
}
