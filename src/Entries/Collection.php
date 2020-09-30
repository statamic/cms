<?php

namespace Statamic\Entries;

use Statamic\Contracts\Data\Augmentable as AugmentableContract;
use Statamic\Contracts\Entries\Collection as Contract;
use Statamic\Data\ContainsCascadingData;
use Statamic\Data\ExistsAsFile;
use Statamic\Data\HasAugmentedData;
use Statamic\Events\CollectionDeleted;
use Statamic\Events\CollectionSaved;
use Statamic\Events\EntryBlueprintFound;
use Statamic\Facades;
use Statamic\Facades\Blink;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Entry;
use Statamic\Facades\File;
use Statamic\Facades\Search;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\Facades\Structure;
use Statamic\Facades\Taxonomy;
use Statamic\Statamic;
use Statamic\Structures\CollectionStructure;
use Statamic\Support\Arr;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class Collection implements Contract, AugmentableContract
{
    use FluentlyGetsAndSets, ExistsAsFile, HasAugmentedData, ContainsCascadingData;

    protected $handle;
    protected $routes = [];
    protected $mount;
    protected $title;
    protected $template;
    protected $layout;
    protected $sites;
    protected $blueprints = [];
    protected $searchIndex;
    protected $dated = false;
    protected $sortField;
    protected $sortDirection;
    protected $ampable = false;
    protected $revisions = false;
    protected $positions;
    protected $defaultPublishState = true;
    protected $futureDateBehavior = 'public';
    protected $pastDateBehavior = 'public';
    protected $structure;
    protected $structureContents;
    protected $taxonomies = [];

    public function __construct()
    {
        $this->cascade = collect();
    }

    public function id()
    {
        return $this->handle();
    }

    public function handle($handle = null)
    {
        return $this->fluentlyGetOrSet('handle')->args(func_get_args());
    }

    public function routes($routes = null)
    {
        return $this
            ->fluentlyGetOrSet('routes')
            ->getter(function ($routes) {
                return $this->sites()->mapWithKeys(function ($site) use ($routes) {
                    $siteRoute = is_string($routes) ? $routes : ($routes[$site] ?? null);

                    return [$site => $siteRoute];
                });
            })
            ->args(func_get_args());
    }

    public function route($site)
    {
        return $this->routes()->get($site);
    }

    public function dated($dated = null)
    {
        return $this->fluentlyGetOrSet('dated')->args(func_get_args());
    }

    public function orderable()
    {
        return optional($this->structure())->maxDepth() === 1;
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

    public function url($site = null)
    {
        if (! $mount = $this->mount()) {
            return null;
        }

        $site = $site ?? $this->sites()->first();

        return optional($mount->in($site))->url();
    }

    public function uri($site = null)
    {
        if (! $mount = $this->mount()) {
            return null;
        }

        $site = $site ?? $this->sites()->first();

        return optional($mount->in($site))->uri();
    }

    public function showUrl()
    {
        return cp_route('collections.show', $this->handle());
    }

    public function editUrl()
    {
        return cp_route('collections.edit', $this->handle());
    }

    public function deleteUrl()
    {
        return cp_route('collections.destroy', $this->handle());
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

    public function entryBlueprints()
    {
        $blueprints = Blueprint::in('collections/'.$this->handle());

        if ($blueprints->isEmpty()) {
            $blueprints = collect([$this->fallbackEntryBlueprint()]);
        }

        return $blueprints->values()->map(function ($blueprint) {
            return $this->ensureEntryBlueprintFields($blueprint);
        });
    }

    public function entryBlueprint($blueprint = null, $entry = null)
    {
        $blueprint = is_null($blueprint)
            ? $this->entryBlueprints()->first()
            : $this->entryBlueprints()->keyBy->handle()->get($blueprint);

        $blueprint = $blueprint ? $this->ensureEntryBlueprintFields($blueprint) : null;

        if ($blueprint) {
            EntryBlueprintFound::dispatch($blueprint->setParent($entry ?? $this), $entry);
        }

        return $blueprint;
    }

    public function fallbackEntryBlueprint()
    {
        $blueprint = Blueprint::find('default')
            ->setHandle($this->handle())
            ->setNamespace('collections.'.$this->handle());

        $contents = $blueprint->contents();
        $contents['title'] = $this->title();
        $blueprint->setContents($contents);

        return $blueprint;
    }

    public function ensureEntryBlueprintFields($blueprint)
    {
        $blueprint
            ->ensureFieldPrepended('title', ['type' => 'text', 'required' => true])
            ->ensureField('slug', ['type' => 'slug', 'required' => true, 'localizable' => true], 'sidebar');

        if ($this->dated()) {
            $blueprint->ensureField('date', ['type' => 'date', 'required' => true], 'sidebar');
        }

        if ($this->hasStructure()) {
            $blueprint->ensureField('parent', [
                'type' => 'entries',
                'collections' => [$this->handle()],
                'max_items' => 1,
                'listable' => false,
                'localizable' => true,
            ], 'sidebar');
        }

        foreach ($this->taxonomies() as $taxonomy) {
            $blueprint->ensureField($taxonomy->handle(), [
                'type' => 'terms',
                'taxonomies' => [$taxonomy->handle()],
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
                if (! Site::hasMultiple() || ! $sites) {
                    $sites = [Site::default()->handle()];
                }

                return collect($sites);
            })
            ->args(func_get_args());
    }

    public function template($template = null)
    {
        return $this
            ->fluentlyGetOrSet('template')
            ->getter(function ($template) {
                return $template ?? 'default';
            })
            ->args(func_get_args());
    }

    public function layout($layout = null)
    {
        return $this
            ->fluentlyGetOrSet('layout')
            ->getter(function ($layout) {
                return $layout ?? 'layout';
            })
            ->args(func_get_args());
    }

    public function save()
    {
        Facades\Collection::save($this);

        Blink::flush('collection-handles');
        Blink::flushStartingWith("collection-{$this->id()}");

        if ($this->hasStructure()) { // todo: only if the structure changed.
            $this->updateEntryUris();
        }

        CollectionSaved::dispatch($this);

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
            $this->handle,
        ]);
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

    public function fileData()
    {
        $array = Arr::except($this->toArray(), [
            'handle',
            'past_date_behavior',
            'future_date_behavior',
            'default_publish_state',
            'dated',
            'structured',
            'orderable',
            'routes',
        ]);

        $route = is_string($this->routes) ? $this->routes : $this->routes()->filter()->all();

        $array = Arr::removeNullValues(array_merge($array, [
            'route' => $route,
            'amp' => $array['amp'] ?: null,
            'date' => $this->dated ?: null,
            'sort_by' => $this->sortField,
            'sort_dir' => $this->sortDirection,
            'default_status' => $this->defaultPublishState === false ? 'draft' : null,
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

        $array['inject'] = Arr::pull($array, 'cascade');

        if ($this->hasStructure()) {
            $array['structure'] = Arr::removeNullValues([
                'root' => $this->structure()->expectsRoot(),
                'max_depth' => $this->structure()->maxDepth(),
                'tree' => $this->structure()->trees()->map(function ($tree) {
                    return $tree->fileData()['tree'];
                })->all(),
            ]);

            if (! Site::hasMultiple()) {
                $array['structure']['tree'] = $array['structure']['tree'][Site::default()->handle()];
            }
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

    public function defaultPublishState($state = null)
    {
        return $this
            ->fluentlyGetOrSet('defaultPublishState')
            ->getter(function ($state) {
                return $this->revisionsEnabled() ? false : $state;
            })
            ->args(func_get_args());
    }

    public function toArray()
    {
        return [
            'title' => $this->title,
            'handle' => $this->handle,
            'routes' => $this->routes,
            'dated' => $this->dated,
            'past_date_behavior' => $this->pastDateBehavior(),
            'future_date_behavior' => $this->futureDateBehavior(),
            'default_publish_state' => $this->defaultPublishState,
            'amp' => $this->ampable,
            'sites' => $this->sites,
            'template' => $this->template,
            'layout' => $this->layout,
            'cascade' => $this->cascade->all(),
            'blueprints' => $this->blueprints,
            'search_index' => $this->searchIndex,
            'orderable' => $this->orderable(),
            'structured' => $this->hasStructure(),
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
                if (! config('statamic.revisions.enabled') || ! Statamic::pro()) {
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
                return Blink::once("collection-{$this->id()}-structure", function () use ($structure) {
                    if (! $structure && $this->structureContents) {
                        $structure = $this->structure = $this->makeStructureFromContents();
                    }

                    return $structure;
                });
            })
            ->setter(function ($structure) {
                if ($structure) {
                    $structure->collection($this);
                }
                $this->structureContents = null;
                Blink::forget("collection-{$this->id()}-structure");

                return $structure;
            })
            ->args(func_get_args());
    }

    public function structureContents(array $contents = null)
    {
        return $this
            ->fluentlyGetOrSet('structureContents')
            ->setter(function ($contents) {
                Blink::forget("collection-{$this->id()}-structure");
                $this->structure = null;

                return $contents;
            })
            ->args(func_get_args());
    }

    protected function makeStructureFromContents()
    {
        $structure = (new CollectionStructure)
            ->collection($this)
            ->expectsRoot($this->structureContents['root'] ?? false)
            ->maxDepth($this->structureContents['max_depth'] ?? null);

        $trees = $this->structureContents['tree'];

        if (! Site::hasMultiple()) {
            $trees = [Site::default()->handle() => $trees];
        }

        foreach ($trees as $site => $contents) {
            $tree = $structure->makeTree($site)->tree($contents);
            $structure->addTree($tree);
        }

        return $structure;
    }

    public function structureHandle()
    {
        if (! $this->hasStructure()) {
            return null;
        }

        return $this->structure()->handle();
    }

    public function hasStructure()
    {
        return $this->structure !== null || $this->structureContents !== null;
    }

    public function delete()
    {
        $this->queryEntries()->get()->each->delete();

        Facades\Collection::delete($this);

        CollectionDeleted::dispatch($this);

        return true;
    }

    public function mount($page = null)
    {
        return $this
            ->fluentlyGetOrSet('mount')
            ->getter(function ($mount) {
                if (! $mount) {
                    return null;
                }

                return Blink::once("collection-{$this->id()}-mount-{$mount}", function () use ($mount) {
                    return Entry::find($mount);
                });
            })
            ->args(func_get_args());
    }

    public function taxonomies($taxonomies = null)
    {
        return $this
            ->fluentlyGetOrSet('taxonomies')
            ->getter(function ($taxonomies) {
                $key = "collection-{$this->id()}-taxonomies-".md5(json_encode($taxonomies));

                return Blink::once($key, function () use ($taxonomies) {
                    return collect($taxonomies)->map(function ($taxonomy) {
                        return Taxonomy::findByHandle($taxonomy);
                    })->filter();
                });
            })
            ->args(func_get_args());
    }

    public function deleteFile()
    {
        File::delete($this->path());
        File::delete(dirname($this->path()).'/'.$this->handle);
    }

    public static function __callStatic($method, $parameters)
    {
        return Facades\Collection::{$method}(...$parameters);
    }

    public function __toString()
    {
        return $this->handle();
    }

    public function augmentedArrayData()
    {
        return [
            'title' => $this->title(),
            'handle' => $this->handle(),
        ];
    }
}
