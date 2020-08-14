<?php

namespace Statamic\Query\Scopes\Filters\Fields;

use Statamic\Facades;
use Statamic\Support\Str;

class Taxonomy extends FieldtypeFilter
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

        $taxonomy = $this->fieldtype->field()->handle();
        $slug = $values['term'];
        $id = "{$taxonomy}::{$slug}";
        $term = Facades\Term::find($id)->title();

        return $field.': '.$term;
    }

    protected function options()
    {
        return Facades\Taxonomy::find($this->fieldtype->field()->handle())
            ->queryTerms()
            ->get()
            ->mapWithKeys(function ($term) {
                return [$term->slug() => $term->title()];
            });
    }
}
