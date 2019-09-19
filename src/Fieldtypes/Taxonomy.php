<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\Facades\Term;
use Statamic\CP\Column;
use Statamic\Taxonomies\TermCollection;

class Taxonomy extends Relationship
{
    protected $statusIcons = false;
    protected $taggable = true;

    public function augment($value, $entry = null)
    {
        $handle = $taxonomy = null;
        $collection = optional($entry)->collection();

        if ($this->usingSingleTaxonomy()) {
            $handle = $this->taxonomies()[0];
            $taxonomy = Facades\Taxonomy::findByHandle($handle);
        }

        return (new TermCollection(Arr::wrap($value)))
            ->map(function ($value) use ($handle, $taxonomy, $collection) {
                if ($taxonomy) {
                    $slug = $value;
                    $id = "{$handle}::{$slug}";
                } else {
                    if (! Str::contains($value, '::')) {
                        throw new \Exception("Ambigious taxonomy term value [$value]. Field [{$this->field->handle()}] is configured with multiple taxonomies.");
                    }
                    $id = $value;
                    [$handle, $slug] = explode('::', $id, 2);
                    $taxonomy = Facades\Taxonomy::findByHandle($handle);
                }

                $term = Term::find($id) ?? Term::make($slug)->taxonomy($taxonomy);

                return $term->collection($collection);
            });
    }

    public function process($data)
    {
        $data = parent::process($data);

        if ($this->usingSingleTaxonomy()) {
            $taxonomy = $this->taxonomies()[0];
            $data = collect($data)->map(function ($id) use ($taxonomy) {
                if (! Str::contains($id, '::')) {
                    $id = $this->createTermFromString($id, $taxonomy);
                }

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

    protected function createTermFromString($string, $taxonomy)
    {
        // The entered string will be treated as the term's title. If it's the same
        // as the slug, an actual term object/file won't need to be created.
        if ($string === ($slug = Str::slug($string))) {
            return "{$taxonomy}::{$slug}";
        }

        $term = Facades\Term::make()
            ->slug($slug)
            ->taxonomy(Facades\Taxonomy::findByHandle($taxonomy))
            ->set('title', $string);

        $term->save();

        return $term->id();
    }
}
