<?php

namespace Statamic\Query\Scopes\Filters\Fields;

use Statamic\Facades;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class Entries extends FieldtypeFilter
{
    public function fieldItems()
    {
        return [
            'field' => [
                'type' => 'select',
                'options' => [
                    'id' => __('ID'),
                    'title' => __('Title'),
                ],
                'default' => 'title',
            ],
            'operator' => [
                'type' => 'select',
                'options' => [
                    'like' => __('Contains'),
                    '=' => __('Is'),
                    '!=' => __('Isn\'t'),
                    'null' => __('Empty'),
                    'not-null' => __('Not empty'),
                ],
                'default' => 'like',
            ],
            'value' => [
                'type' => 'text',
                'placeholder' => __('Value'),
                'if' => [
                    'operator' => 'contains_any like, =, !=',
                ],
                'required' => false,
            ],
        ];
    }

    public function apply($query, $handle, $values)
    {
        $config = $this->fieldtype->field()->config();
        $maxItems = $config['max_items'] ?? 0;
        $operator = $values['operator'];
        $value = $values['value'];

        if (in_array($operator, ['null', 'not-null'])) {
            match ($operator) {
                'null' => $query->whereNull($handle),
                'not-null' => $query->whereNotNull($handle),
            };

            return;
        }

        if ($operator === 'like') {
            $value = Str::ensureLeft($value, '%');
            $value = Str::ensureRight($value, '%');
        }

        if ($values['field'] == 'id') {
            $maxItems === 1
                ? $query->where($handle, $operator, $value)
                : $query->whereJsonContains($handle, $value);

            return;
        }

        $ids = Facades\Entry::query()
            ->when($config['collections'] ?? null, fn ($query) => $query->whereIn('collection', $config['collections']))
            ->where($values['field'], $operator, $value)
            ->get(['id'])
            ->map(fn ($entry) => $entry->id())
            ->all();

        if (empty($ids)) {
            $maxItems === 1
                ? $query->where($handle, -1)
                : $query->whereJsonContains($handle, [-1]);

            return;
        }

        if ($maxItems === 1) {
            $query->whereIn($handle, $ids);

            return;
        }

        $query->where(function ($subquery) use ($handle, $ids) {
            foreach ($ids as $count => $id) {
                $subquery->{$count == 0 ? 'whereJsonContains' : 'orWhereJsonContains'}($handle, [$id]);
            }
        });
    }

    public function badge($values)
    {
        $field = $this->fieldtype->field()->display();
        $selectedField = $values['field'];
        $operator = $values['operator'];
        $translatedField = Arr::get($this->fieldItems(), "field.options.{$selectedField}");
        $translatedOperator = Arr::get($this->fieldItems(), "operator.options.{$operator}");
        $value = $values['value'];

        return $field.' '.$translatedField.' '.strtolower($translatedOperator).' '.$value;
    }
}
