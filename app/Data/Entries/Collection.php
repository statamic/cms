<?php

namespace Statamic\Data\Entries;

use Statamic\API;
use Statamic\API\Arr;
use Statamic\API\Site;
use Statamic\API\Entry;
use Statamic\API\Search;
use Statamic\API\Stache;
use Statamic\API\Blueprint;
use Statamic\API\Structure;
use Statamic\Data\ContainsData;
use Statamic\Data\ExistsAsFile;
use Statamic\FluentlyGetsAndSets;
use Statamic\Contracts\Data\Entries\Collection as Contract;

class Collection implements Contract
{
    use ContainsData, FluentlyGetsAndSets, ExistsAsFile;

    protected $handle;
    protected $route;
    protected $mount;
    protected $title;
    protected $template;
    protected $layout;
    protected $sites = [];
    protected $blueprints = [];
    protected $searchIndex;
    protected $dated = false;
    protected $orderable = false;
    protected $ampable = false;
    protected $revisions = false;
    protected $positions = [];
    protected $futureDateBehavior = 'public';
    protected $pastDateBehavior = 'public';
    protected $structure;

    public function handle($handle = null)
    {
        return $this->fluentlyGetOrSet('handle')->args(func_get_args());
    }

    public function route($route = null)
    {
        return $this->fluentlyGetOrSet('route')->args(func_get_args());
    }

    public function dated($dated = null)
    {
        return $this->fluentlyGetOrSet('dated')->args(func_get_args());
    }

    public function orderable($orderable = null)
    {
        return $this->fluentlyGetOrSet('orderable')->args(func_get_args());
    }

    public function sortField()
    {
        if ($this->orderable()) {
            return 'order';
        } elseif ($this->dated()) {
            return 'date';
        }

        return 'title';
    }

    public function sortDirection()
    {
        if ($this->orderable()) {
            return 'asc';
        } elseif ($this->dated()) {
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

    public function url()
    {
        return optional($this->mount())->url();
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
                if (is_null($blueprints)) {
                    return collect([$this->fallbackEntryBlueprint()]);
                }

                return collect($blueprints)->map(function ($blueprint) {
                    return Blueprint::find($blueprint);
                });
            })
            ->args(func_get_args());
    }

    public function entryBlueprint()
    {
        return $this->ensureEntryBlueprintFields(
            $this->entryBlueprints()->first() ?? $this->fallbackEntryBlueprint()
        );
    }

    public function fallbackEntryBlueprint()
    {
        return Blueprint::find(config('statamic.theming.blueprints.default'));
    }

    public function ensureEntryBlueprintFields($blueprint)
    {
        $blueprint
            ->ensureFieldPrepended('title', ['type' => 'text', 'required' => true])
            ->ensureField('slug', ['type' => 'slug', 'required' => true], 'sidebar');

        if ($this->dated()) {
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

        optional($this->structure())->updateEntryUris();

        return $this;
    }

    public function updateEntryUris()
    {
        API\Collection::updateEntryUris($this);

        return $this;
    }

    public function path()
    {
        return vsprintf('%s/%s.yaml', [
            rtrim(Stache::store('collections')->directory(), '/'),
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

    public function getEntryPositions()
    {
        return $this->positions;
    }

    public function setEntryPositions($positions)
    {
        $this->positions = $positions;

        return $this;
    }

    public function setEntryPosition($id, $position)
    {
        Arr::set($this->positions, $position, $id);
        ksort($this->positions);

        return $this;
    }

    public function getEntryPosition($id)
    {
        return array_flip($this->positions)[$id] ?? null;
    }

    public function getEntryOrder($id = null)
    {
        $order = array_values($this->positions);

        if (func_num_args() === 0) {
            return $order;
        }

        $index = array_flip($order)[$id] ?? null;

        return $index === null ? null : $index + 1;
    }

    public function fileData()
    {
        $array = Arr::except($this->toArray(), [
            'handle',
            'past_date_behavior',
            'future_date_behavior'
        ]);

        $array = Arr::removeNullValues(array_merge($array, [
            'entry_order' => $this->getEntryOrder(),
            'amp' => $array['amp'] ?: null,
            'date' => $array['dated'] ?: null,
            'orderable' => $array['orderable'] ?: null,
            'date_behavior' => [
                'past' => $this->pastDateBehavior,
                'future' => $this->futureDateBehavior,
            ],
        ]));

        if (! Site::hasMultiple()) {
            unset($array['sites']);
        }

        if ($array['date_behavior'] == ['past' => 'public', 'future' => 'public']) {
            unset($array['date_behavior']);
        }

        return $array;
    }

    public function futureDateBehavior($behavior = null)
    {
        return $this
            ->fluentlyGetOrSet('futureDateBehavior')
            ->getter(function ($behavior) {
                return $behavior ?? 'public';
            })
            ->args(func_get_args());
    }

    public function toArray()
    {
        return [
            'title' => $this->title,
            'handle' => $this->handle,
            'route' => $this->route,
            'dated' => $this->dated,
            'past_date_behavior' => $this->pastDateBehavior(),
            'future_date_behavior' => $this->futureDateBehavior(),
            'amp' => $this->ampable,
            'sites' => $this->sites,
            'template' => $this->template,
            'layout' => $this->layout,
            'data' => $this->data,
            'blueprints' => $this->blueprints,
            'search_index' => $this->searchIndex,
            'orderable' => $this->orderable,
            'structure' => optional($this->structure())->handle(),
            'mount' => $this->mount,
        ];
    }

    public function pastDateBehavior($behavior = null)
    {
        return $this
            ->fluentlyGetOrSet('pastDateBehavior')
            ->getter(function ($behavior) {
                return $behavior ?? 'public';
            })
            ->args(func_get_args());
    }

    public function revisionsEnabled($enabled = null)
    {
        return $this->fluentlyGetOrSet('revisions')->args(func_get_args());
    }

    public function structure($structure = null)
    {
        return $this
            ->fluentlyGetOrSet('structure')
            ->getter(function ($structure) {
                return is_string($structure) ? Structure::findByHandle($structure) : $structure;
            })
            ->args(func_get_args());
    }

    public function hasStructure()
    {
        return $this->structure !== null;
    }

    public function delete()
    {
        $this->queryEntries()->get()->each->delete();

        API\Collection::delete($this);

        return true;
    }

    public function mount($page = null)
    {
        return $this
            ->fluentlyGetOrSet('mount')
            ->getter(function ($mount) {
                return $this->mountedEntry = $this->mountedEntry ?? Entry::find($mount);
            })
            ->args(func_get_args());
    }
}
