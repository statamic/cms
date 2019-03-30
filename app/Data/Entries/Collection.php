<?php

namespace Statamic\Data\Entries;

use Statamic\API;
use Statamic\API\Search;
use Statamic\API\Blueprint;
use Statamic\Data\ContainsData;
use Statamic\FluentlyGetsAndSets;
use Statamic\Contracts\Data\Entries\Collection as Contract;

class Collection implements Contract
{
    use ContainsData, FluentlyGetsAndSets;

    protected $handle;
    protected $route;
    protected $order;
    protected $title;
    protected $template;
    protected $layout;
    protected $sites = [];
    protected $blueprints = [];
    protected $searchIndex;
    protected $ampable = false;

    public function handle($handle = null)
    {
        return $this->fluentlyGetOrSet('handle')->args(func_get_args());
    }

    public function route($route = null)
    {
        return $this->fluentlyGetOrSet('route')->args(func_get_args());
    }

    public function order($order = null)
    {
        return $this
            ->fluentlyGetOrSet('order')
            ->getter(function ($order) {
                return $order ?? 'alphabetical';
            })
            ->setter(function ($order) {
                switch ($order) {
                    case 'numeric':
                    case 'numerical':
                    case 'numbers':
                    case 'numbered':
                        return 'number';
                    default:
                        return $order;
                }
            })
            ->args(func_get_args());
    }

    public function sortField()
    {
        switch ($this->order()) {
            case 'date':
                return 'date';
            case 'number':
                return 'order';
            default:
                return 'title';
        }
    }

    public function sortDirection()
    {
        if ($this->order() === 'date') {
            return 'desc';
        }

        return 'asc';
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

    public function ampable($ampable = null)
    {
        return $this
            ->fluentlyGetOrSet('ampable')
            ->getter(function ($ampable) {
                return config('statamic.amp.enabled') && $ampable;
            })
            ->args(func_get_args());
    }

    public function showUrl()
    {
        return cp_route('collections.show', $this->handle());
    }

    public function editUrl()
    {
        return cp_route('collections.edit', $this->handle());
    }

    public function createEntryUrl()
    {
        return cp_route('collections.entries.create', [$this->handle(), $this->sites()->first()]);
    }

    public function queryEntries()
    {
        return API\Entry::query()->where('collection', $this->handle());
    }

    public function entryBlueprints($blueprints = null)
    {
        return $this
            ->fluentlyGetOrSet('blueprints')
            ->getter(function ($blueprints) {
                return collect($blueprints)->map(function ($blueprint) {
                    return Blueprint::find($blueprint);
                });
            })
            ->args(func_get_args());
    }

    public function entryBlueprint()
    {
        return $this->ensureEntryBlueprintFields(
            $this->entryBlueprints()->first()
                ?? Blueprint::find(config('statamic.theming.blueprints.default'))
        );
    }

    public function ensureEntryBlueprintFields($blueprint)
    {
        $blueprint
            ->ensureFieldPrepended('title', ['type' => 'text', 'required' => true])
            ->ensureField('slug', ['type' => 'slug', 'required' => true], 'sidebar');

        if ($this->order() === 'date') {
            $blueprint->ensureField('date', ['type' => 'date', 'required' => true], 'sidebar');
        }

        return $blueprint;
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

    public function template($template = null)
    {
        return $this
            ->fluentlyGetOrSet('template')
            ->getter(function ($template) {
                return $template ?? config('statamic.theming.views.entry');
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

    public function save()
    {
        API\Collection::save($this);

        return $this;
    }

    public function path()
    {
        return vsprintf('%s/%s.yaml', [
            rtrim(config('statamic.stache.stores.collections.directory'), '/'),
            $this->handle
        ]);
    }

    public function searchIndex($index = null)
    {
        return $this
            ->fluentlyGetOrSet('searchIndex')
            ->getter(function ($index) {
                return $index ?  Search::index($index) : null;
            })
            ->args(func_get_args());
    }

    public function hasSearchIndex()
    {
        return $this->searchIndex() !== null;
    }
}
