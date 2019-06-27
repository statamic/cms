<?php

namespace Statamic\Data\Taxonomies;

use Statamic\API;
use Statamic\API\Str;
use Statamic\API\Data;
use Statamic\API\File;
use Statamic\API\Path;
use Statamic\API\Site;
use Statamic\API\YAML;
use Statamic\API\Entry;
use Statamic\API\Config;
use Statamic\API\Stache;
use Statamic\API\Fieldset;
use Statamic\Data\Routable;
use Statamic\Data\Augmentable;
use Statamic\Data\ContainsData;
use Statamic\Data\ExistsAsFile;
use Statamic\FluentlyGetsAndSets;
use Statamic\Data\Content\Content;
use Statamic\Data\Services\TermsService;
use Statamic\API\Taxonomy as TaxonomyAPI;
use Statamic\Data\Content\ContentCollection;
use Illuminate\Contracts\Support\Responsable;
use Statamic\Data\Content\HasLocalizedSlugsInData;
use Statamic\Contracts\Data\Taxonomies\Term as TermContract;
use Statamic\Contracts\Data\Augmentable as AugmentableContract;
use Statamic\Contracts\Data\Taxonomies\Taxonomy as TaxonomyContract;

class Term implements TermContract, Responsable, AugmentableContract
{
    use ContainsData, Routable, ExistsAsFile, FluentlyGetsAndSets, Augmentable;

    protected $taxonomy;
    protected $template;
    protected $layout;
    protected $collection;

    public function id()
    {
        return $this->taxonomyHandle() . '::' . $this->slug();
    }

    public function taxonomy($taxonomy = null)
    {
        return $this->fluentlyGetOrSet('taxonomy')->args(func_get_args());
    }

    public function taxonomyHandle()
    {
        return $this->taxonomy->handle();
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
        $route = $this->taxonomy()->route();

        if ($this->collection) {
            $route = $this->collection()->url() . '/' . $route;
        }

        return $route;
    }

    public function routeData()
    {
        return array_merge($this->values(), [
            'id' => $this->id(),
            'slug' => $this->slug(),
        ]);
    }

    public function values()
    {
        return $this->data();
    }

    public function blueprint()
    {
        return $this->taxonomy->termBlueprint();
    }

    public function toArray()
    {
        return array_merge($this->values(), [
            'id' => $this->id(),
            'slug' => $this->slug(),
            'title' => $this->title(),
            'taxonomy' => $this->taxonomyHandle(),
        ]);
    }

    public function fileData()
    {
        return $this->data();
    }

    public function toCacheableArray()
    {
        return [
            'taxonomy' => $this->taxonomyHandle(),
            'slug' => $this->slug(),
            'path' => $this->initialPath() ?? $this->path(),
            'data' => $this->data(),
        ];
    }

    public function published()
    {
        return true;
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
                return $template ?? $this->taxonomy()->template();
            })
            ->args(func_get_args());
    }

    public function layout($layout = null)
    {
        return $this
            ->fluentlyGetOrSet('layout')
            ->getter(function ($layout) {
                return $layout ?? $this->taxonomy()->layout();
            })
            ->args(func_get_args());
    }

    public function augmentedArrayData()
    {
        return array_merge($this->values(), [
            'id' => $this->id(),
            'slug' => $this->slug(),
            'uri' => $this->uri(),
            'url' => $this->url(),
            'title' => $this->title(),
            'entries' => $this->entries(),
        ]);
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
        return cp_route('taxonomies.terms.edit', [$this->taxonomyHandle(), $this->slug()]);
    }

    public function save()
    {
        API\Term::save($this);

        return true;
    }
}
