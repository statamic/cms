<?php

namespace Statamic\Data\Taxonomies;

use Statamic\API;
use Statamic\API\Arr;
use Statamic\API\Stache;
use Statamic\API\Blueprint;
use Statamic\API\Collection;
use Statamic\Data\ExistsAsFile;
use Statamic\FluentlyGetsAndSets;
use Illuminate\Contracts\Support\Responsable;
use Statamic\Contracts\Data\Taxonomies\Taxonomy as Contract;

class Taxonomy implements Contract, Responsable
{
    use FluentlyGetsAndSets, ExistsAsFile;

    protected $handle;
    protected $title;
    protected $termBlueprint;
    protected $sites = [];
    protected $collection;
    protected $defaultStatus = 'published';

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

    public function path()
    {
        return vsprintf('%s/%s.yaml', [
            rtrim(Stache::store('taxonomies')->directory(), '/'),
            $this->handle
        ]);
    }

    public function termBlueprint($blueprint = null)
    {
        return $this
            ->fluentlyGetOrSet('termBlueprint')
            ->getter(function ($blueprint) {
                return $this->ensureTermBlueprintFields(
                    $blueprint ? Blueprint::find($blueprint) : $this->fallbackTermBlueprint()
                );
            })
            ->args(func_get_args());
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
        return Blueprint::find(config('statamic.theming.blueprints.default'));
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
        $query = API\Term::query()->where('taxonomy', $this->handle());

        if ($this->collection) {
            $query->where('collection', $this->collection->handle());
        }

        return $query;
    }

    public function save()
    {
        API\Taxonomy::save($this);

        return true;
    }

    public function fileData()
    {
        return Arr::except($this->toArray(), [
            'handle',
        ]);
    }

    public function defaultStatus($status = null)
    {
        return $this->fluentlyGetOrSet('defaultStatus')->args(func_get_args());
    }

    public function toArray()
    {
        return [
            'title' => $this->title,
            'handle' => $this->handle,
            'term_blueprint' => $this->termBlueprint,
        ];
    }

    public function sites($sites = null)
    {
        return $this
            ->fluentlyGetOrSet('sites')
            ->getter(function ($sites) {
                return collect($sites);
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

                return $enabled;
            })
            ->args(func_get_args());
    }

    public function uri()
    {
        return '/' . $this->handle;
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
        return 'taxonomy'; // todo: get it from the collection
    }

    public function layout()
    {
        return config('statamic.theming.views.layout');
    }

    public static function __callStatic($method, $parameters)
    {
        return API\Taxonomy::{$method}(...$parameters);
    }
}
