<?php

namespace Statamic\Query\Scopes\Filters\Fields;

use Statamic\Facades;
use Statamic\Support\Arr;

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
                'placeholder' => __('Field'),
                'default' => 'id',
            ],
            'operator' => [
                'type' => 'select',
                'placeholder' => __('Select Operator'),
                'options' => [
                    'like' => __('Contains'),
                    '=' => __('Is'),
                    '!=' => __('Isn\'t'),
                ],
                'default' => 'like',
            ],
            'value' => [
                'type' => 'text',
                'placeholder' => __('Value'),
                'if' => [
                    'operator' => 'not empty',
                ],
            ],
        ];
    }

    public function apply($query, $handle, $values)
    {
        if ($values['field'] == 'id') {
            $query->where($handle, $values['operator'], $values['value']);

            return;
        }

        $config = $this->fieldtype->field()->config();

        $ids = Facades\Entry::query()
            ->when($config['collections'], fn ($query) => $query->whereIn('collection', $config['collections']))
            ->where($values['field'], $values['operator'], $values['value'])
            ->get(['id'])
            ->map(fn ($entry) => $entry->id())
            ->all();

        if (($config['max_items'] ?? 0) == 1) {
            $query->whereIn($handle, $ids);

            return;
        }

        if (empty($ids)) {
            $query->where($handle, -1);
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
