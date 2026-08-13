<?php

namespace Statamic\Http\Resources\CP\Taxonomies;

use Statamic\Fieldtypes\Terms as TermsFieldtype;

class TermsFieldtypeListedTerm extends ListedTerm
{
    private TermsFieldtype $fieldtype;

    public function fieldtype(TermsFieldtype $fieldtype): self
    {
        $this->fieldtype = $fieldtype;

        return $this;
    }

    public function toArray($request)
    {
        $arr = parent::toArray($request);

        if (! in_array($this->fieldtype->config('mode'), ['select', 'typeahead'])) {
            return $arr;
        }

        if ($hint = $this->fieldtype->getItemHint($this->resource)) {
            $arr['hint'] = $hint;
        }

        if ($this->fieldtype->hierarchicalTaxonomy()) {
            $term = $this->resource;
            $ancestorSlugs = $term->ancestors()->map->slug();

            $arr['depth'] = $term->depth() ?? 1;
            $arr['path'] = $ancestorSlugs->push($term->slug())->implode('/');
        }

        return $arr;
    }
}
