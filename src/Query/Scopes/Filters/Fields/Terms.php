<?php

namespace Statamic\Query\Scopes\Filters\Fields;

use Statamic\Facades;
use Statamic\Support\Str;

class Terms extends FieldtypeFilter
{
    public function fieldItems()
    {
        return [
            'term' => [
                'type' => 'select',
                'options' => $this->options()->all(),
                'placeholder' => __('Contains'),
                'clearable' => true,
            ],
        ];
    }

    public function apply($query, $handle, $values)
    {
        $term = $values['term'];

        $term = Str::ensureLeft($term, '%');
        $term = Str::ensureRight($term, '%');

        $query->where($handle, 'like', $term);
    }

    public function badge($values)
    {
        $field = $this->fieldtype->field()->display();

        $id = $this->fieldtype->usingSingleTaxonomy()
            ? $this->fieldtype->taxonomies()[0].'::'.$values['term']
            : $values['term'];

        $term = Facades\Term::find($id)->title();

        return $field.': '.$term;
    }

    protected function options()
    {
        return collect($this->fieldtype->taxonomies())
            ->map(function ($handle) {
                return Facades\Taxonomy::find($handle);
            })
            ->filter()
            ->flatMap(function ($taxonomy) {
                return $taxonomy->queryTerms()->get();
            })
            ->mapWithKeys(function ($term) {
                $value = $this->fieldtype->usingSingleTaxonomy() ? $term->slug() : $term->id();

                return [$value => $term->title()];
            });
    }
}
