<?php

namespace Statamic\Fieldtypes;

use Statamic\CP\Column;
use Statamic\Facades\GraphQL;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\User;
use Statamic\GraphQL\Types\TaxonomyType;

class Taxonomies extends Relationship
{
    protected $canEdit = false;
    protected $canCreate = false;
    protected $canSearch = false;
    protected $statusIcons = false;
    protected $icon = 'fieldtype-taxonomy';

    protected function authorizeItemData($id): bool
    {
        return $this->authorizeViewable(Taxonomy::findByHandle($id));
    }

    protected function toItemArray($id, $site = null)
    {
        if ($taxonomy = Taxonomy::findByHandle($id)) {
            return [
                'title' => $taxonomy->title(),
                'id' => $taxonomy->handle(),
            ];
        }

        return $this->invalidItemArray($id);
    }

    public function getIndexItems($request)
    {
        return Taxonomy::all()
            ->filter(fn ($taxonomy) => User::current()->can('view', $taxonomy))
            ->sortBy('title')
            ->map(function ($taxonomy) {
                return [
                    'id' => $taxonomy->handle(),
                    'title' => $taxonomy->title(),
                    'terms' => $taxonomy->queryTerms()->count(),
                ];
            })->values();
    }

    protected function getColumns()
    {
        return [
            Column::make('title'),
            Column::make('terms'),
        ];
    }

    protected function augmentValue($value)
    {
        return Taxonomy::findByHandle($value);
    }

    public function toGqlType()
    {
        $type = GraphQL::type(TaxonomyType::NAME);

        if ($this->config('max_items') !== 1) {
            $type = GraphQL::listOf($type);
        }

        return $type;
    }
}
