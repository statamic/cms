<?php

namespace Statamic\Fieldtypes;

use Statamic\CP\Column;
use Statamic\Facades;
use Statamic\Facades\Site;
use Statamic\Facades\Term;
use Statamic\Http\Resources\CP\Taxonomies\Terms as TermsResource;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\Taxonomies\TermCollection;

class Taxonomy extends Relationship
{
    protected $statusIcons = false;
    protected $taggable = true;

    protected function configFieldItems(): array
    {
        return array_merge(parent::configFieldItems(), [
            'taxonomies' => [
                'display' => __('Taxonomies'),
                'type' => 'taxonomies',
                'mode' => 'select',
            ],
        ]);
    }

    public function augment($value)
    {
        $terms = $this->getTermsForAugmentation($value);

        return $this->config('max_items') === 1 ? $terms->first() : $terms;
    }

    public function shallowAugment($value)
    {
        $terms = $this->getTermsForAugmentation($value);

        $terms = collect($terms)->map->toShallowAugmentedCollection();

        return $this->config('max_items') === 1 ? $terms->first() : $terms;
    }

    private function getTermsForAugmentation($value)
    {
        $handle = $taxonomy = null;

        if ($this->usingSingleTaxonomy()) {
            $handle = $this->taxonomies()[0];
            $taxonomy = Facades\Taxonomy::findByHandle($handle);
        }

        return (new TermCollection(Arr::wrap($value)))
            ->map(function ($value) use ($handle, $taxonomy) {
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

                $entry = $this->field->parent();

                if ($entry && $this->field->handle() === $taxonomy->handle()) {
                    $term->collection($entry->collection());
                }

                $locale = $entry
                    ? $entry->locale()
                    : Site::current()->locale();

                return $term->in($locale);
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

    public function getIndexItems($request)
    {
        $query = $this->getIndexQuery($request);

        if ($sort = $this->getSortColumn($request)) {
            $query->orderBy($sort, $this->getSortDirection($request));
        }

        return $query->paginate();
    }

    public function getResourceCollection($request, $items)
    {
        return (new TermsResource($items))
            ->blueprint($this->getBlueprint($request))
            ->columnPreferenceKey("taxonomies.{$this->getFirstTaxonomyFromRequest($request)->handle()}.columns");
    }

    protected function getBlueprint($request)
    {
        return $this->getFirstTaxonomyFromRequest($request)->termBlueprint();
    }

    protected function getFirstTaxonomyFromRequest($request)
    {
        return $request->taxonomies
            ? Facades\Taxonomy::findByHandle($request->taxonomies[0])
            : Facades\Taxonomy::all()->first();
    }

    public function getSortColumn($request)
    {
        $column = $request->get('sort');

        if (! $column && ! $request->search) {
            $column = 'title'; // todo: get from taxonomy or config
        }

        return $column;
    }

    public function getSortDirection($request)
    {
        $order = $request->get('order', 'asc');

        if (! $request->sort && ! $request->search) {
            // $order = 'asc'; // todo: get from taxonomy or config
        }

        return $order;
    }

    protected function getBaseSelectionsUrlParameters()
    {
        return [
            'taxonomies' => $this->taxonomies(),
        ];
    }

    protected function toItemArray($id)
    {
        if ($this->usingSingleTaxonomy() && ! Str::contains($id, '::')) {
            $id = "{$this->taxonomies()[0]}::{$id}";
        }

        if (! $term = Term::find($id)) {
            return $this->invalidItemArray($id);
        }

        return [
            'id' => $id,
            'title' => $term->value('title'),
            'published' => $term->published(),
            'private' => $term->private(),
            'edit_url' => $term->editUrl(),
        ];
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

        if ($request->exclusions) {
            $query->whereNotIn('id', $request->exclusions);
        }

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
