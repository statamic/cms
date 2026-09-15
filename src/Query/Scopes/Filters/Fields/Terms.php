<?php

namespace Statamic\Query\Scopes\Filters\Fields;

use Statamic\Facades;
use Statamic\Support\Arr;
use Statamic\Support\Str;

use function Statamic\trans as __;

class Terms extends FieldtypeFilter
{
    public function fieldItems()
    {
        return [
            'operator' => [
                'type' => 'select',
                'options' => [
                    'like' => __('Contains'),
                    'null' => __('Empty'),
                    'not-null' => __('Not empty'),
                ],
                'default' => 'like',
            ],
            'term' => [
                'type' => 'terms',
                'placeholder' => __('Term'),
                'clearable' => true,
                'mode' => 'select',
                'max_items' => 1,
                'taxonomies' => $this->fieldtype->taxonomies(),
                'if' => [
                    'operator' => 'contains_any like',
                ],
            ],
        ];
    }

    public function apply($query, $handle, $values)
    {
        $operator = $values['operator'];

        match ($operator) {
            'like' => $this->applyContains($query, $handle, $values['term']),
            'null' => $query->whereNull($handle),
            'not-null' => $query->whereNotNull($handle),
        };
    }

    private function applyContains($query, $handle, $term): void
    {
        $values = $this->expandTermFilterValues($term);

        if ($this->fieldtype->config('max_items') === 1) {
            $query->where(function ($query) use ($handle, $values) {
                foreach ($values as $value) {
                    $query->orWhere($handle, $value)
                        ->orWhere($handle, 'like', '%'.$value.'%');
                }
            });

            return;
        }

        $query->where(function ($query) use ($handle, $values) {
            foreach ($values as $value) {
                $query->orWhereJsonContains($handle, $value);
            }
        });
    }

    private function expandTermFilterValues(string $term): array
    {
        $slug = Str::after($term, '::');
        $handle = $this->fieldtype->usingSingleTaxonomy()
            ? $this->fieldtype->taxonomies()[0]
            : (Str::contains($term, '::') ? Str::before($term, '::') : null);

        // Single taxonomy fields store bare slugs, multiple taxonomy fields store
        // prefixed ones. Expanded values have to be in the same shape or they'll
        // match another taxonomy's identically slugged term, or nothing at all.
        $prefix = $this->fieldtype->usingSingleTaxonomy() || ! $handle ? '' : $handle.'::';

        $fallback = array_values(array_filter([$prefix.$slug]));

        if (! $handle || ! ($taxonomy = Facades\Taxonomy::findByHandle($handle)) || ! $taxonomy->hierarchical()) {
            return $fallback;
        }

        if (! $page = $taxonomy->structure()->tree()->find($slug)) {
            return $fallback;
        }

        $values = collect();

        $walk = function ($page) use (&$walk, $values, $prefix) {
            $values->push($prefix.$page->id());
            $page->pages()->all()->each(fn ($child) => $walk($child));
        };

        $walk($page);

        return $values->merge($fallback)->unique()->values()->all();
    }

    public function badge($values)
    {
        $field = $this->fieldtype->field()->display();
        $operator = $values['operator'];

        if (in_array($operator, ['null', 'not-null'])) {
            $translatedOperator = Arr::get($this->fieldItems(), "operator.options.{$operator}");

            return $field.' '.strtolower($translatedOperator);
        }

        $id = $this->fieldtype->usingSingleTaxonomy()
            ? $this->fieldtype->taxonomies()[0].'::'.$values['term']
            : $values['term'];

        $term = Facades\Term::find($id)->in(Facades\Site::selected()->handle())->title();

        return $field.': '.$term;
    }

    public function isComplete($values): bool
    {
        $values = Arr::removeNullValues($values);

        if (! $operator = Arr::get($values, 'operator')) {
            return false;
        }

        if (in_array($operator, ['null', 'not-null'])) {
            return true;
        }

        return Arr::has($values, 'term');
    }
}
