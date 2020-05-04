<?php

namespace Statamic\Taxonomies;

use Illuminate\Contracts\Support\Responsable;
use Statamic\Contracts\Data\Augmentable as AugmentableContract;
use Statamic\Contracts\Taxonomies\Taxonomy as Contract;
use Statamic\Data\ExistsAsFile;
use Statamic\Data\HasAugmentedData;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\Support\Arr;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class Taxonomy implements Contract, Responsable, AugmentableContract
{
    use FluentlyGetsAndSets, ExistsAsFile, HasAugmentedData;

    protected $handle;
    protected $title;
    protected $blueprints = [];
    protected $sites = [];
    protected $collection;
    protected $defaultPublishState = true;
    protected $revisions = false;
    protected $searchIndex;

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

    public function termBlueprints($blueprints = null)
    {
        return $this
            ->fluentlyGetOrSet('blueprints')
            ->getter(function ($blueprints) {
                if (is_null($blueprints)) {
                    return collect([$this->fallbackTermBlueprint()]);
                }

                return collect($blueprints)->map(function ($blueprint) {
                    return Blueprint::find($blueprint);
                });
            })
            ->setter(function ($blueprints) {
                return empty($blueprints) ? null : $blueprints;
            })
            ->args(func_get_args());
    }

    public function termBlueprint()
    {
        return $this->ensureTermBlueprintFields(
            $this->termBlueprints()->first() ?? $this->fallbackTermBlueprint()
        );
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
        return Blueprint::find('default');
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

        return true;
    }

    public function delete()
    {
        $this->queryTerms()->get()->each->delete();

        Facades\Taxonomy::delete($this);

        return true;
    }

    public function fileData()
    {
        return Arr::except($this->toArray(), [
            'handle',
        ]);
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
                return collect(Site::hasMultiple() ? $sites : [Site::default()->handle()]);
            })
            ->args(func_get_args());
    }

    public function revisionsEnabled($enabled = null)
    {
        return $this
            ->fluentlyGetOrSet('revisions')
            ->getter(function ($enabled) {
                if (! config('statamic.revisions.enabled')) {
                    return false;
                }

                return false; // TODO

                return $enabled;
            })
            ->args(func_get_args());
    }

    public function uri()
    {
        return '/'.$this->handle;
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

    public function augmentedArrayData()
    {
        return [
            'title' => $this->title(),
            'handle' => $this->handle(),
        ];
    }
}
