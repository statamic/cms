<?php

namespace Statamic\Structures;

use Statamic\Contracts\Structures\TaxonomyTree as TreeContract;
use Statamic\Contracts\Structures\TaxonomyTreeRepository;
use Statamic\Events\TaxonomyTreeDeleted;
use Statamic\Events\TaxonomyTreeSaved;
use Statamic\Events\TaxonomyTreeSaving;
use Statamic\Facades\Blink;
use Statamic\Facades\Stache;
use Statamic\Facades\Taxonomy;
use Statamic\Support\Str;

class TaxonomyTree extends Tree implements TreeContract
{
    private $structureCache;

    public function idKey()
    {
        return 'term';
    }

    public function entryOrder($reference)
    {
        return ($this->cachedFlattenedPageOrder ??= $this->flattenedPages()->map->id()->flip())->get($reference);
    }

    public function append($entry)
    {
        if (is_null($entry)) {
            return $this;
        }

        $this->tree[] = ['term' => $this->termSlug($entry)];

        return $this;
    }

    public function appendTo($parent, $page)
    {
        if (! is_null($page) && ! is_array($page)) {
            $page = $this->termSlug($page);
        }

        return parent::appendTo($parent, $page);
    }

    private function termSlug($term): string
    {
        if (is_object($term)) {
            return $term->inDefaultLocale()->slug();
        }

        return Str::after($term, $this->handle().'::');
    }

    public function structure()
    {
        if ($this->structureCache) {
            return $this->structureCache;
        }

        return $this->structureCache = Blink::once('taxonomy-tree-structure-'.$this->handle(), function () {
            return Taxonomy::findByHandle($this->handle())->structure();
        });
    }

    public function taxonomy()
    {
        return $this->structure()->taxonomy();
    }

    public function path()
    {
        $path = Stache::store('taxonomy-trees')->directory();

        return "{$path}{$this->handle()}.yaml";
    }

    protected function dispatchSavedEvent()
    {
        TaxonomyTreeSaved::dispatch($this);
    }

    protected function dispatchSavingEvent()
    {
        return TaxonomyTreeSaving::dispatch($this);
    }

    protected function dispatchDeletedEvent()
    {
        TaxonomyTreeDeleted::dispatch($this);
    }

    protected function repository()
    {
        return app(TaxonomyTreeRepository::class);
    }

    public function save()
    {
        $saved = parent::save();

        if ($saved) {
            Blink::forget("taxonomy-structure-tree-{$this->handle()}");
            Blink::forget('taxonomy-structure-term-slugs-'.$this->handle());
        }

        return $saved;
    }
}
