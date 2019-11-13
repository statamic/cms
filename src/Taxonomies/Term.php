<?php

namespace Statamic\Taxonomies;

use ArrayAccess;
use Statamic\Facades;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\Facades\Data;
use Statamic\Facades\File;
use Statamic\Facades\Path;
use Statamic\Facades\Site;
use Statamic\Facades\YAML;
use Statamic\Facades\Entry;
use Statamic\Facades\Config;
use Statamic\Facades\Stache;
use Statamic\Facades\Fieldset;
use Statamic\Routing\Routable;
use Statamic\Data\HasOrigin;
use Statamic\Data\Augmentable;
use Statamic\Data\ContainsData;
use Statamic\Data\ExistsAsFile;
use Statamic\Facades\Blueprint;
use Statamic\Revisions\Revisable;
use Statamic\Data\Content\Content;
use Statamic\Data\Services\TermsService;
use Statamic\Facades\Taxonomy as TaxonomyAPI;
use Illuminate\Contracts\Support\Responsable;
use Statamic\Support\Traits\FluentlyGetsAndSets;
use Statamic\Contracts\Taxonomies\Term as TermContract;
use Statamic\Contracts\Data\Augmentable as AugmentableContract;
use Statamic\Contracts\Taxonomies\Taxonomy as TaxonomyContract;

class Term implements TermContract, Responsable, AugmentableContract, ArrayAccess
{
    use ContainsData, Routable, ExistsAsFile, FluentlyGetsAndSets, Augmentable, Revisable, HasOrigin;

    protected $taxonomy;
    protected $blueprint;
    protected $template;
    protected $layout;
    protected $locale;
    protected $collection;

    public function __construct()
    {
        $this->data = collect();
        $this->supplements = collect();
    }

    public function id()
    {
        return $this->taxonomyHandle() . '::' . $this->slug();
    }

    public function locale($locale = null)
    {
        return $this->fluentlyGetOrSet('locale')->args(func_get_args());
    }

    public function taxonomy($taxonomy = null)
    {
        return $this
            ->fluentlyGetOrSet('taxonomy')
            ->setter(function ($taxonomy) {
                return $taxonomy instanceof \Statamic\Contracts\Taxonomies\Taxonomy ? $taxonomy->handle() : $taxonomy;
            })
            ->getter(function ($taxonomy) {
                return $taxonomy ? Taxonomy::findByHandle($taxonomy) : null;
            })
            ->args(func_get_args());
    }

    public function taxonomyHandle()
    {
        return $this->taxonomy()->handle();
    }

    public function path()
    {
        return vsprintf('%s/%s/%s.yaml', [
            rtrim(Stache::store('terms')->directory(), '/'),
            $this->taxonomyHandle(),
            $this->slug(),
        ]);
    }

    public function route()
    {
        $route = '/' . str_replace('_', '-', $this->taxonomyHandle()) . '/{slug}';

        if ($this->collection) {
            $route = $this->collection()->url() . $route;
        }

        return $route;
    }

    public function routeData()
    {
        return $this->values()->merge([
            'id' => $this->id(),
            'slug' => $this->slug(),
        ])->all();
    }

    public function blueprint()
    {
        return $this->fluentlyGetOrSet('blueprint')
            ->getter(function ($blueprint) {
                return $blueprint
                    ? $this->taxonomy()->ensureTermBlueprintFields(Blueprint::find($blueprint))
                    : $this->taxonomy()->termBlueprint();
            })
            ->args(func_get_args());
    }

    public function fileData()
    {
        $array = array_merge($this->data()->all(), [
            'origin' => optional($this->origin)->id(),
            'published' => $this->published === false ? false : null,
        ]);

        if ($this->blueprint) {
            $array['blueprint'] = $this->blueprint;
        }

        return $array;
    }

    public function toCacheableArray()
    {
        return [
            'taxonomy' => $this->taxonomyHandle(),
            'locale' => $this->locale,
            'origin' => $this->hasOrigin() ? $this->origin()->id() : null,
            'slug' => $this->slug(),
            'path' => $this->initialPath() ?? $this->path(),
            'data' => $this->data(),
        ];
    }

    public function private()
    {
        return false;
    }

    public function site()
    {
        return Site::current(); // todo
    }

    public function in($site)
    {
        return $this; // todo
    }

    public function toResponse($request)
    {
        return (new \Statamic\Http\Responses\DataResponse($this))->toResponse($request);
    }

    public function template($template = null)
    {
        return $this
            ->fluentlyGetOrSet('template')
            ->getter(function ($template) {
                return $template ?? config('statamic.theming.views.term'); // todo: get the fallback template from the collection
            })
            ->args(func_get_args());
    }

    public function layout($layout = null)
    {
        return $this
            ->fluentlyGetOrSet('layout')
            ->getter(function ($layout) {
                return $layout ?? config('statamic.theming.views.layout');
            })
            ->args(func_get_args());
    }

    public function augmentedArrayData()
    {
        return $this->values()->merge([
            'id' => $this->id(),
            'slug' => $this->slug(),
            'uri' => $this->uri(),
            'url' => $this->url(),
            'title' => $this->title(),
            'entries' => $entryQuery = $this->queryEntries(),
            'entries_count' => $entryQuery->count(),
        ])->merge($this->supplements)->all();
    }

    public function collection($collection = null)
    {
        return $this->fluentlyGetOrSet('collection')->args(func_get_args());
    }

    public function entries()
    {
        return $this->queryEntries()->get();
    }

    protected function queryEntries()
    {
        $entries = $this->collection
            ? $this->collection->queryEntries()
            : Entry::query();

        return $entries->whereTaxonomy($this->id());
    }

    public function title()
    {
        return $this->get('title', $this->slug());
    }

    public function editUrl()
    {
        return $this->cpUrl('taxonomies.terms.edit');
    }

    public function save()
    {
        Facades\Term::save($this);

        return true;
    }

    public function delete()
    {
        Facades\Term::delete($this);

        return true;
    }

    public function reference()
    {
        return "term::{$this->id()}";
    }

    public function updateUrl()
    {
        return $this->cpUrl('taxonomies.terms.update');
    }

    public function publishUrl()
    {
        return $this->cpUrl('taxonomies.terms.published.store');
    }

    public function unpublishUrl()
    {
        return $this->cpUrl('taxonomies.terms.published.destroy');
    }

    public function revisionsUrl()
    {
        return $this->cpUrl('taxonomies.terms.revisions.index');
    }

    public function createRevisionUrl()
    {
        return $this->cpUrl('taxonomies.terms.revisions.store');
    }

    public function restoreRevisionUrl()
    {
        return $this->cpUrl('taxonomies.terms.restore-revision');
    }

    protected function cpUrl($route)
    {
        return cp_route($route, [$this->taxonomyHandle(), $this->slug(), $this->locale()]);
    }

    public function revisionsEnabled()
    {
        return $this->taxonomy()->revisionsEnabled();
    }

    protected function revisionKey()
    {
        return vsprintf('taxonomies/%s/%s/%s', [
            $this->taxonomyHandle(),
            $this->locale(),
            $this->slug()
        ]);
    }

    protected function revisionAttributes()
    {
        return [
            'id' => $this->id(),
            'slug' => $this->slug(),
            'published' => $this->published(),
            'data' => Arr::except($this->data(), ['updated_by', 'updated_at']),
        ];
    }

    public function makeFromRevision($revision)
    {
        $entry = clone $this;

        if (! $revision) {
            return $entry;
        }

        $attrs = $revision->attributes();

        return $entry
            ->published($attrs['published'])
            ->data($attrs['data'])
            ->slug($attrs['slug']);
    }

    protected function getOriginByString($origin)
    {
        //
    }

    public function offsetExists($key)
    {
        return $this->has($key);
    }

    public function offsetGet($key)
    {
        return $this->value($key);
    }

    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    public function offsetUnset($key)
    {
        $this->remove($key);
    }
}
