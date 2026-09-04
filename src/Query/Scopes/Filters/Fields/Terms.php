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
            : Str::before($term, '::');

        $leaf = str_contains($slug, '/') ? Str::afterLast($slug, '/') : $slug;
        $fallback = array_values(array_unique(array_filter([$term, $slug, $leaf])));

        if (! $handle || ! ($taxonomy = Facades\Taxonomy::findByHandle($handle)) || ! $taxonomy->hierarchical()) {
            return $fallback;
        }

        if (! $page = $taxonomy->structure()->tree()->find($leaf)) {
            return $fallback;
        }

        $values = collect();
        $parentPath = implode('/', $taxonomy->structure()->ancestorsOf($page));

        $walk = function ($page, $path) use (&$walk, $values) {
            $slug = $page->id();
            $full = $path === '' ? $slug : $path.'/'.$slug;
            $values->push($slug, $full);
            $page->pages()->all()->each(fn ($child) => $walk($child, $full));
        };

        $walk($page, $parentPath);

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
