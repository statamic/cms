<?php

namespace Statamic\Fieldtypes;

use Statamic\API;
use Statamic\API\Arr;
use Statamic\API\Str;
use Statamic\API\Term;
use Statamic\CP\Column;
use Statamic\Data\Taxonomies\TermCollection;

class Taxonomy extends Relationship
{
    protected $statusIcons = false;

    public function augment($value, $entry = null)
    {
        $taxonomy = null;
        $collection = $entry->collection();

        if ($this->usingSingleTaxonomy()) {
            $taxonomy = API\Taxonomy::findByHandle($this->taxonomies()[0]);
        }

        return (new TermCollection(Arr::wrap($value)))
            ->map(function ($slug) use ($taxonomy, $collection) {
                if (! $taxonomy) {
                    [$taxonomy, $slug] = explode('::', $slug, 2);
                    $taxonomy = API\Taxonomy::findByHandle($taxonomy);
                }

                return Term::make($slug)
                    ->taxonomy($taxonomy)
                    ->collection($collection);
            });
    }

    public function process($data)
    {
        $data = parent::process($data);

        if ($this->usingSingleTaxonomy()) {
            $taxonomy = $this->taxonomies()[0];
            $data = collect($data)->map(function ($id) use ($taxonomy) {
                return explode('::', $id, 2)[1];
            })->all();
        }

        return $data;
    }

    public function preProcess($data)
    {
        $data = parent::preProcess($data);

        if ($this->usingSingleTaxonomy()) {
            $taxonomy = $this->taxonomies()[0];
            $data = collect($data)->map(function ($id) use ($taxonomy) {
                if (! Str::contains($id, '::')) {
                    $id = "{$taxonomy}::{$id}";
                }

                return $id;
            })->all();
        }

        return $data;
    }

    protected function getBaseSelectionsUrlParameters()
    {
        return [
            'taxonomies' => $this->taxonomies(),
        ];
    }

    protected function toItemArray($id)
    {
        if ($this->usingSingleTaxonomy() && !Str::contains($id, '::')) {
            $id = "{$this->taxonomies()[0]}::{$id}";
        }

        if ($term = Term::find($id)) {
            return $term->toArray();
        }

        return $this->invalidItemArray($id);
    }

    protected function getColumns()
    {
        $columns = [Column::make('title')];

        if (! $this->usingSingleTaxonomy()) {
            $columns[] = Column::make('taxonomy');
        }

        return $columns;
    }

    protected function getIndexQuery($request)
    {
        $query = Term::query();

        if ($taxonomies = $request->taxonomies) {
            $query->whereIn('taxonomy', $taxonomies);
        }

        if ($search = $request->search) {
            $query->where('title', 'like', '%'.$search.'%');
        }

        // if ($site = $request->site) {
        //     $query->where('site', $site);
        // }

        return $query;
    }

    protected function taxonomies()
    {
        return Arr::wrap($this->config('taxonomy'));
    }

    protected function usingSingleTaxonomy()
    {
        return count($this->taxonomies()) === 1;
    }
}
