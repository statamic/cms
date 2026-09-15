<?php

namespace Statamic\Forms\Logic;

use Statamic\Support\Arr;

/**
 * Evaluates a page rule's conditions against submission data.
 *
 * Mirrors the front-end operator semantics in
 * resources/js/components/field-conditions/Validator.js. Keep the two in sync
 * when operators are added or changed.
 */
class RuleEvaluator
{
    private const array NUMBER_COMPARISONS = ['>', '>=', '<', '<='];

    /**
     * Conditions are grouped by their `join`: a new "or" group begins at the
     * first condition and at every condition joined with "or". A group passes
     * when all of its conditions pass, and the rule passes when any group does.
     * In other words, "and" binds tighter than "or".
     */
    public function passes(array $conditions, array $data): bool
    {
        if (empty($conditions)) {
            return false;
        }

        return collect($this->groups($conditions))
            ->contains(fn (array $group): bool => $this->groupPasses($group, $data));
    }

    private function groups(array $conditions): array
    {
        $groups = [];
        $current = [];

        foreach ($conditions as $index => $condition) {
            $join = $condition['join'] ?? 'and';

            if ($index > 0 && $join === 'or') {
                $groups[] = $current;
                $current = [];
            }

            $current[] = $condition;
        }

        $groups[] = $current;

        return $groups;
    }

    private function groupPasses(array $group, array $data): bool
    {
        return collect($group)->every(fn (array $condition): bool => $this->conditionPasses($condition, $data));
    }

    private function conditionPasses(array $condition, array $data): bool
    {
        $operator = $this->normalizeOperator($condition['operator'] ?? 'equals');
        $lhs = $this->prepareLhs(Arr::get($data, $condition['field']), $operator);
        $rhs = $this->prepareRhs($condition['value'] ?? null, $operator);

        return match ($operator) {
            'includes' => $this->includes($lhs, $rhs),
            'includes_any' => $this->includesAny($lhs, $rhs),
            default => $this->compare($lhs, $operator, $rhs),
        };
    }

    private function normalizeOperator(string $operator): string
    {
        return match ($operator) {
            '', 'is', 'equals' => '==',
            'isnt', 'not' => '!=',
            'contains', 'includes' => 'includes',
            'contains_any', 'includes_any' => 'includes_any',
            default => $operator,
        };
    }

    private function prepareLhs(mixed $lhs, string $operator): mixed
    {
        if (in_array($operator, self::NUMBER_COMPARISONS)) {
            return $this->toNumber($lhs);
        }

        if ($operator === 'includes' && ! is_array($lhs)) {
            return $lhs === null || $lhs === '' ? '' : (string) $lhs;
        }

        if (is_string($lhs)) {
            $lhs = trim($lhs);

            return $lhs === '' ? null : $lhs;
        }

        return $lhs;
    }

    private function prepareRhs(mixed $rhs, string $operator): mixed
    {
        $rhs = match ($rhs) {
            'null' => null,
            'true' => true,
            'false' => false,
            default => $rhs,
        };

        if (in_array($operator, self::NUMBER_COMPARISONS)) {
            return $this->toNumber($rhs);
        }

        if ($rhs === 'empty' || $operator === 'includes' || $operator === 'includes_any') {
            return $rhs;
        }

        return is_string($rhs) ? trim($rhs) : $rhs;
    }

    private function compare(mixed $lhs, string $operator, mixed $rhs): bool
    {
        if ($rhs === 'empty') {
            $lhs = $this->isEmpty($lhs);
            $rhs = true;
            $operator = '==';
        }

        if (is_array($lhs)) {
            return false;
        }

        return match ($operator) {
            '==' => $lhs == $rhs,
            '!=' => $lhs != $rhs,
            '===' => $lhs === $rhs,
            '!==' => $lhs !== $rhs,
            '>' => $lhs > $rhs,
            '>=' => $lhs >= $rhs,
            '<' => $lhs < $rhs,
            '<=' => $lhs <= $rhs,
            default => false,
        };
    }

    private function includes(mixed $lhs, mixed $rhs): bool
    {
        if (is_array($lhs)) {
            return in_array($rhs, $lhs);
        }

        return str_contains((string) $lhs, (string) $rhs);
    }

    private function includesAny(mixed $lhs, mixed $rhs): bool
    {
        $values = collect(explode(',', (string) $rhs))
            ->map(fn (string $value): string => trim($value))
            ->reject(fn (string $value): bool => $value === '')
            ->all();

        if (is_array($lhs)) {
            return count(array_intersect($lhs, $values)) > 0;
        }

        return collect($values)->contains(fn (string $value): bool => str_contains((string) $lhs, $value));
    }

    private function isEmpty(mixed $value): bool
    {
        return match (true) {
            $value === null => true,
            is_array($value) => count($value) === 0,
            is_string($value) => $value === '',
            default => false,
        };
    }

    private function toNumber(mixed $value): int|float
    {
        if (is_numeric($value)) {
            return $value + 0;
        }

        return match ($value) {
            null, '', false => 0,
            true => 1,
            default => NAN,
        };
    }
}
