<?php

namespace Statamic\Taxonomies;

use Illuminate\Contracts\Support\Responsable;
use Statamic\Contracts\Data\Augmentable as AugmentableContract;
use Statamic\Contracts\Taxonomies\Taxonomy as Contract;
use Statamic\Data\ContainsCascadingData;
use Statamic\Data\ContainsSupplementalData;
use Statamic\Data\ExistsAsFile;
use Statamic\Data\HasAugmentedData;
use Statamic\Events\TaxonomyDeleted;
use Statamic\Events\TaxonomySaved;
use Statamic\Events\TermBlueprintFound;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Search;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\Facades\URL;
use Statamic\Statamic;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class Taxonomy implements Contract, Responsable, AugmentableContract
{
    use FluentlyGetsAndSets, ExistsAsFile, HasAugmentedData, ContainsCascadingData, ContainsSupplementalData;

    protected $handle;
    protected $title;
    protected $blueprints = [];
    protected $sites = [];
    protected $collection;
    protected $defaultPublishState = true;
    protected $revisions = false;
    protected $searchIndex;

    public function __construct()
    {
        $this->cascade = collect();
        $this->supplements = collect();
    }

    public function id()
    {
        return $this->handle();
    }

    public function handle($handle = null)
    {
        return $this->fluentlyGetOrSet('handle')->args(func_get_args());
    }

    public function title($title = null)
    {
        return $this
            ->fluentlyGetOrSet('title')
            ->getter(function ($title) {
                return $title ?? ucfirst($this->handle);
            })
            ->args(func_get_args());
    }

    public function showUrl()
    {
        return cp_route('taxonomies.show', $this->handle());
    }

    public function editUrl()
    {
        return cp_route('taxonomies.edit', $this->handle());
    }

    public function deleteUrl()
    {
        return cp_route('taxonomies.destroy', $this->handle());
    }

    public function path()
    {
        return vsprintf('%s/%s.yaml', [
            rtrim(Stache::store('taxonomies')->directory(), '/'),
            $this->handle,
        ]);
    }

    public function termBlueprints()
    {
        $blueprints = Blueprint::in('taxonomies/'.$this->handle());

        if ($blueprints->isEmpty()) {
            $blueprints = collect([$this->fallbackTermBlueprint()]);
        }

        return $blueprints->values()->map(function ($blueprint) {
            return $this->ensureTermBlueprintFields($blueprint);
        });
    }

    public function termBlueprint($blueprint = null, $term = null)
    {
        $blueprint = is_null($blueprint)
            ? $this->termBlueprints()->first()
            : $this->termBlueprints()->keyBy->handle()->get($blueprint);

        $blueprint ? $this->ensureTermBlueprintFields($blueprint) : null;

        if ($blueprint) {
            TermBlueprintFound::dispatch($blueprint->setParent($term ?? $this), $term);
        }

        return $blueprint;
    }

    public function ensureTermBlueprintFields($blueprint)
    {
        $blueprint
            ->ensureFieldPrepended('title', ['type' => 'text', 'required' => true])
            ->ensureField('slug', ['type' => 'slug', 'required' => true], 'sidebar');

        return $blueprint;
    }

    public function fallbackTermBlueprint()
    {
        $blueprint = Blueprint::find('default')
            ->setHandle($this->handle())
            ->setNamespace('taxonomies.'.$this->handle());

        $contents = $blueprint->contents();
        $contents['title'] = $this->title();
        $blueprint->setContents($contents);

        return $blueprint;
    }

    public function sortField()
    {
        return 'title'; // todo
    }

    public function sortDirection()
    {
        return 'asc'; // todo
    }

    public function queryTerms()
    {
        $query = Facades\Term::query()->where('taxonomy', $this->handle());

        if ($this->collection) {
            $query->where('collection', $this->collection->handle());
        }

        return $query;
    }

    public function save()
    {
        Facades\Taxonomy::save($this);

        TaxonomySaved::dispatch($this);

        return true;
    }

    public function delete()
    {
        $this->queryTerms()->get()->each->delete();

        Facades\Taxonomy::delete($this);

        TaxonomyDeleted::dispatch($this);

        return true;
    }

    public function fileData()
    {
        $data = [
            'title' => $this->title,
            'blueprints' => $this->blueprints,
        ];

        if (Site::hasMultiple()) {
            $data['sites'] = $this->sites;
        }

        $data['inject'] = $this->cascade->all();

        return $data;
    }

    public function defaultPublishState($state = null)
    {
        return $this->fluentlyGetOrSet('defaultPublishState')->args(func_get_args());
    }

    public function toArray()
    {
        return [
            'title' => $this->title,
            'handle' => $this->handle,
            'blueprints' => $this->blueprints,
        ];
    }

    public function sites($sites = null)
    {
        return $this
            ->fluentlyGetOrSet('sites')
            ->getter(function ($sites) {
                if (! Site::hasMultiple() || ! $sites) {
                    $sites = [Site::default()->handle()];
                }

                return collect($sites);
            })
            ->args(func_get_args());
    }

    public function revisionsEnabled($enabled = null)
    {
        return $this
            ->fluentlyGetOrSet('revisions')
            ->getter(function ($enabled) {
                if (! config('statamic.revisions.enabled') || ! Statamic::pro()) {
                    return false;
                }

                return false; // TODO

                return $enabled;
            })
            ->args(func_get_args());
    }

    public function url()
    {
        return URL::makeRelative($this->absoluteUrl());
    }

    public function urlWithoutRedirect()
    {
        return $this->url();
    }

    public function absoluteUrl()
    {
        return URL::tidy(Site::current()->absoluteUrl().$this->uri());
    }

    public function uri()
    {
        $site = Site::current();

        $prefix = $this->collection() ? $this->collection()->uri($site->handle()) : '/';

        return URL::tidy($prefix.str_replace('_', '-', '/'.$this->handle));
    }

    public function collection($collection = null)
    {
        return $this->fluentlyGetOrSet('collection')->args(func_get_args());
    }

    public function collections()
    {
        return Collection::all()->filter(function ($collection) {
            return $collection
                ->taxonomies()
                ->keyBy->handle()
                ->has($this->handle);
        })->values();
    }

    public function toResponse($request)
    {
        if (! view()->exists($this->template())) {
            throw new NotFoundHttpException;
        }

        return (new \Statamic\Http\Responses\DataResponse($this))
            ->with([
                'terms' => $termQuery = $this->queryTerms(),
                $this->handle() => $termQuery,
            ])
            ->toResponse($request);
    }

    public function get($key, $fallback = null)
    {
        // todo: Only used in DataResponse, added this method to prevent errors.
        return $fallback;
    }

    public function template()
    {
        $template = $this->handle().'.index';

        if ($collection = $this->collection()) {
            $template = $collection->handle().'.'.$template;
        }

        return $template;
    }

    public function layout()
    {
        return 'layout';
    }

    public function searchIndex($index = null)
    {
        return $this
            ->fluentlyGetOrSet('searchIndex')
            ->getter(function ($index) {
                return $index ? Search::index($index) : null;
            })
            ->args(func_get_args());
    }

    public function hasSearchIndex()
    {
        return $this->searchIndex() !== null;
    }

    public static function __callStatic($method, $parameters)
    {
        return Facades\Taxonomy::{$method}(...$parameters);
    }

    public function __toString()
    {
        return $this->handle();
    }

    public function augmentedArrayData()
    {
        return array_merge([
            'title' => $this->title(),
            'handle' => $this->handle(),
            'uri' => $this->uri(),
            'url' => $this->url(),
            'permalink' => $this->absoluteUrl(),
        ], $this->supplements->all());
    }
}
