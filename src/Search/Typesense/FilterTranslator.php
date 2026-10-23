<?php

namespace Statamic\Search\Typesense;

use DateTimeInterface;
use Statamic\Query\OrderBy;

class FilterTranslator
{
    public function __construct(private Schema $schema)
    {
    }

    public function filters(array $wheres): ?string
    {
        if ($wheres === []) {
            return '';
        }

        $filters = [];

        foreach ($wheres as $where) {
            $filter = $this->where($where);

            if (is_null($filter)) {
                return null;
            }

            $boolean = $where['boolean'] ?? 'and';

            if (($where['type'] ?? null) === 'NotIn') {
                $boolean = 'and';
            }
            $filters[] = [
                'boolean' => $boolean,
                'filter' => $filter,
            ];
        }

        return collect($filters)->reduce(function (?string $expression, array $filter) {
            if (is_null($expression)) {
                return $filter['filter'];
            }

            $operator = $filter['boolean'] === 'or' ? '||' : '&&';

            return "({$expression} {$operator} {$filter['filter']})";
        });
    }

    public function sorts(array $orders): ?string
    {
        if ($orders === []) {
            return '';
        }

        if (count($orders) > 3) {
            return null;
        }

        $sorts = collect($orders)->map(function (OrderBy $order) {
            $field = $this->field($order->sort);

            if (! $field || ! $this->schema->sortable($field)) {
                return null;
            }

            return "{$field}:{$order->direction}";
        });

        return $sorts->contains(null) ? null : $sorts->implode(',');
    }

    public function escape(mixed $value, ?string $type = null): ?string
    {
        if ($value instanceof DateTimeInterface) {
            $value = $value->getTimestamp();
        }

        $type = str_replace('[]', '', (string) $type);

        if (in_array($type, ['int32', 'int64'], true)) {
            if (! is_int($value) && ! is_numeric($value)) {
                return null;
            }

            if ((float) $value !== (float) (int) $value) {
                return null;
            }

            return (string) ((int) $value);
        }

        if ($type === 'float') {
            if (! is_float($value) && ! is_int($value) && ! is_numeric($value)) {
                return null;
            }

            return (string) ((float) $value);
        }

        if ($type === 'bool' || is_bool($value)) {
            $value = is_bool($value)
                ? $value
                : filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);

            return is_null($value) ? null : ($value ? 'true' : 'false');
        }

        if (in_array($type, ['string', 'auto'], true) && is_scalar($value)) {
            $value = (string) $value;
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if (! is_string($value)) {
            return null;
        }

        $value = str_replace(['\\', '`'], ['\\\\', '\\`'], $value);

        return "`{$value}`";
    }

    private function where(array $where): ?string
    {
        if (($where['type'] ?? null) === 'Nested') {
            $query = $where['query'] ?? null;

            if (! $query || ! method_exists($query, 'rawWheres')) {
                return null;
            }

            $nested = $this->filters($query->rawWheres());

            return $nested === '' ? null : $nested;
        }

        $field = $this->field($where['column'] ?? '');

        if (! $field || ! $this->schema->filterable($field)) {
            return null;
        }

        $type = $this->schema->field($field)['type'] ?? null;
        $array = str_ends_with((string) $type, '[]');
        $json = str_starts_with($where['type'] ?? '', 'Json');
        $scalarType = str_replace('[]', '', (string) $type);
        $supportedTypes = ['string', 'int32', 'int64', 'float', 'bool'];

        // Array membership and scalar equality are different operations in Statamic.
        if ($array !== $json || ! in_array($scalarType, $supportedTypes, true)) {
            return null;
        }

        return match ($where['type'] ?? null) {
            'Basic' => $this->basic($field, $type, $where),
            'In' => $this->in($field, $type, $where['values'] ?? [], false),
            'NotIn' => $this->in($field, $type, $where['values'] ?? [], true),
            'Between' => $this->between($field, $type, $where['values'] ?? [], false),
            'NotBetween' => $this->between($field, $type, $where['values'] ?? [], true),
            'JsonContains' => $this->contains($field, $type, $where['values'] ?? [], false),
            'JsonDoesntContain' => $this->contains($field, $type, $where['values'] ?? [], true),
            'JsonOverlaps' => $this->in($field, $type, $where['values'] ?? [], false),
            'JsonDoesntOverlap' => $this->in($field, $type, $where['values'] ?? [], true),
            default => null,
        };
    }

    private function basic(string $field, ?string $type, array $where): ?string
    {
        $comparison = $where['operator'] ?? '';
        $operators = [
            '=' => ':=',
            '<>' => ':!=',
            '!=' => ':!=',
            '>' => ':>',
            '>=' => ':>=',
            '<' => ':<',
            '<=' => ':<=',
        ];

        if (! $operator = $operators[$comparison] ?? null) {
            return null;
        }

        if (in_array($comparison, ['>', '>=', '<', '<='], true) && ! $this->supportsRange($type)) {
            return null;
        }

        if (is_null($value = $this->escape($where['value'] ?? null, $type))) {
            return null;
        }

        return "{$field}{$operator}{$value}";
    }

    private function in(string $field, ?string $type, array $values, bool $negated): ?string
    {
        if ($values === []) {
            return null;
        }

        $values = collect($values)->map(fn ($value) => $this->escape($value, $type));

        if ($values->contains(null)) {
            return null;
        }

        $operator = $negated ? ':!=' : ':=';

        return sprintf('%s%s[%s]', $field, $operator, $values->implode(','));
    }

    private function between(string $field, ?string $type, array $values, bool $negated): ?string
    {
        if (count($values) !== 2 || ! $this->supportsRange($type)) {
            return null;
        }

        [$from, $to] = array_map(fn ($value) => $this->escape($value, $type), $values);

        if (is_null($from) || is_null($to)) {
            return null;
        }

        return $negated
            ? "({$field}:<{$from} || {$field}:>{$to})"
            : "({$field}:>={$from} && {$field}:<={$to})";
    }

    private function contains(string $field, ?string $type, array $values, bool $negated): ?string
    {
        if ($values === []) {
            return null;
        }

        $values = collect($values)->map(fn ($value) => $this->escape($value, $type));

        if ($values->contains(null)) {
            return null;
        }

        $operator = $negated ? ':!=' : ':=';
        $boolean = $negated ? ' || ' : ' && ';

        $filter = $values
            ->map(fn (string $value) => "{$field}{$operator}{$value}")
            ->join($boolean);

        return $values->count() > 1 ? "({$filter})" : $filter;
    }

    private function field(string $field): ?string
    {
        $field = str_replace('->', '.', $field);

        return preg_match('/^[\w*.-]+$/', $field) ? $field : null;
    }

    private function supportsRange(?string $type): bool
    {
        return in_array($type, ['int32', 'int64', 'float'], true);
    }
}
