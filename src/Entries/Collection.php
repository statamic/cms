<?php

namespace Statamic\Entries;

use Statamic\Facades;
use Statamic\Support\Arr;
use Statamic\Facades\File;
use Statamic\Facades\Site;
use Statamic\Facades\Entry;
use Statamic\Facades\Search;
use Statamic\Facades\Stache;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Structure;
use Statamic\Data\ContainsData;
use Statamic\Data\ExistsAsFile;
use Statamic\Support\Traits\FluentlyGetsAndSets;
use Statamic\Contracts\Entries\Collection as Contract;

class Collection implements Contract
{
    use ContainsData, FluentlyGetsAndSets, ExistsAsFile;

    protected $handle;
    protected $route;
    protected $mount;
    protected $mountedEntry;
    protected $title;
    protected $template;
    protected $layout;
    protected $sites;
    protected $blueprints = [];
    protected $searchIndex;
    protected $dated = false;
    protected $orderable = false;
    protected $sortField;
    protected $sortDirection;
    protected $ampable = false;
    protected $revisions = false;
    protected $positions = [];
    protected $defaultStatus = 'published';
    protected $futureDateBehavior = 'public';
    protected $pastDateBehavior = 'public';
    protected $structure;
    protected $taxonomies = [];

    public function id()
    {
        return $this->handle();
    }

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

    public function sortField($field = null)
    {
        return $this
            ->fluentlyGetOrSet('sortField')
            ->getter(function ($sortField) {
                if ($sortField) {
                    return $sortField;
                } elseif ($this->orderable()) {
                    return 'order';
                } elseif ($this->dated()) {
                    return 'date';
                }
                return 'title';
            })
            ->args(func_get_args());
    }

    public function sortDirection($dir = null)
    {
        return $this
            ->fluentlyGetOrSet('sortDirection')
            ->getter(function ($sortDirection) {
                if ($sortDirection) {
                    return $sortDirection;
                }

                // If a custom sort field has been defined but no direction, we'll default
                // to ascending. Otherwise, if it was a dated collection, it might end
                // up with a field in descending order which would be confusing.
                if ($this->sortField) {
                    return 'asc';
                }

                if ($this->orderable()) {
                    return 'asc';
                } elseif ($this->dated()) {
                    return 'desc';
                }

                return 'asc';
            })
            ->args(func_get_args());
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

    public function createEntryUrl($site = null)
    {
        $site = $site ?? $this->sites()->first();

        return cp_route('collections.entries.create', [$this->handle(), $site]);
    }

    public function queryEntries()
    {
        return Facades\Entry::query()->where('collection', $this->handle());
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

        if ($this->hasStructure()) {
            $blueprint->ensureField('parent', [
                'type' => 'relationship',
                'collections' => [$this->handle()],
                'max_items' => 1,
                'listable' => false,
            ], 'sidebar');
        }

        foreach ($this->taxonomies() as $taxonomy) {
            $blueprint->ensureField($taxonomy->handle(), [
                'type' => 'taxonomy',
                'taxonomy' => $taxonomy->handle(),
                'display' => $taxonomy->title(),
                'mode' => 'select',
            ], 'sidebar');
        }

        return $blueprint;
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
        Facades\Collection::save($this);

        optional($this->structure())->updateEntryUris();

        return $this;
    }

    public function updateEntryUris()
    {
        Facades\Collection::updateEntryUris($this);

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

    public function appendEntryPosition($id)
    {
        $position = collect($this->positions)->keys()->sort()->last() + 1;

        return $this->setEntryPosition($id, $position);
    }

    public function removeEntryPosition($id)
    {
        unset($this->positions[$this->getEntryPosition($id)]);

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
            'future_date_behavior',
            'dated',
        ]);

        $array = Arr::removeNullValues(array_merge($array, [
            'entry_order' => $this->getEntryOrder(),
            'amp' => $array['amp'] ?: null,
            'date' => $this->dated ?: null,
            'orderable' => $array['orderable'] ?: null,
            'sort_by' => $this->sortField,
            'sort_dir' => $this->sortDirection,
            'default_status' => $this->defaultStatus,
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


    public function defaultStatus($status = null)
    {
        return $this->fluentlyGetOrSet('defaultStatus')->args(func_get_args());
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
            'default_status' => $this->defaultStatus(),
            'amp' => $this->ampable,
            'sites' => $this->sites,
            'template' => $this->template,
            'layout' => $this->layout,
            'data' => $this->data,
            'blueprints' => $this->blueprints,
            'search_index' => $this->searchIndex,
            'orderable' => $this->orderable,
            'structure' => $this->structure,
            'mount' => $this->mount,
            'taxonomies' => $this->taxonomies,
            'revisions' => $this->revisions,
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

    public function structure($structure = null)
    {
        return $this
            ->fluentlyGetOrSet('structure')
            ->getter(function ($structure) {
                return is_string($structure) ? Structure::findByHandle($structure) : $structure;
            })
            ->args(func_get_args());
    }

    public function structureHandle()
    {
        if (! $this->hasStructure()) {
            return null;
        }

        return is_string($this->structure) ? $this->structure : $this->structure->handle();
    }

    public function hasStructure()
    {
        return $this->structure !== null;
    }

    public function delete()
    {
        $this->queryEntries()->get()->each->delete();

        Facades\Collection::delete($this);

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

    public function taxonomies($taxonomies = null)
    {
        return $this
            ->fluentlyGetOrSet('taxonomies')
            ->getter(function ($taxonomies) {
                return collect($taxonomies)->map(function ($taxonomy) {
                    return Taxonomy::findByHandle($taxonomy);
                })->filter();
            })
            ->args(func_get_args());
    }

    public function deleteFile()
    {
        File::delete($this->path());
        File::delete(dirname($this->path()) . '/' . $this->handle);
    }

    public static function __callStatic($method, $parameters)
    {
        return Facades\Collection::{$method}(...$parameters);
    }
}
