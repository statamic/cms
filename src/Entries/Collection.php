<?php

namespace Statamic\Entries;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;
use Statamic\Contracts\Data\Augmentable as AugmentableContract;
use Statamic\Contracts\Entries\Collection as Contract;
use Statamic\Data\ContainsCascadingData;
use Statamic\Data\ExistsAsFile;
use Statamic\Data\HasAugmentedData;
use Statamic\Events\CollectionCreated;
use Statamic\Events\CollectionCreating;
use Statamic\Events\CollectionDeleted;
use Statamic\Events\CollectionDeleting;
use Statamic\Events\CollectionSaved;
use Statamic\Events\CollectionSaving;
use Statamic\Events\EntryBlueprintFound;
use Statamic\Facades;
use Statamic\Facades\Blink;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Entry;
use Statamic\Facades\File;
use Statamic\Facades\Search;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\Facades\Taxonomy;
use Statamic\Statamic;
use Statamic\Structures\CollectionStructure;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\Support\Traits\FluentlyGetsAndSets;

use function Statamic\trans as __;

class Collection implements Arrayable, ArrayAccess, AugmentableContract, Contract
{
    use ContainsCascadingData, ExistsAsFile, FluentlyGetsAndSets, HasAugmentedData;

    protected $handle;
    protected $routes = [];
    private $cachedRoutes = null;
    protected $mount;
    protected $title;
    protected $icon;
    protected $template;
    protected $layout;
    protected $sites;
    protected $propagate = false;
    protected $blueprints = [];
    protected $searchIndex;
    protected $dated = false;
    protected $sortField;
    protected $sortDirection;
    protected $revisions = false;
    protected $positions;
    protected $defaultPublishState = true;
    protected $originBehavior = 'select';
    protected $futureDateBehavior = 'public';
    protected $pastDateBehavior = 'public';
    protected $structure;
    protected $structureContents;
    protected $taxonomies = [];
    protected $requiresSlugs = true;
    protected $titleFormats = [];
    protected $previewTargets = [];
    protected $autosave;
    protected $withEvents = true;

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
        if (func_num_args() === 0) {
            return $this->handle;
        }

        $this->handle = $handle;

        return $this;
    }

    public function routes($routes = null)
    {
        return $this
            ->fluentlyGetOrSet('routes')
            ->getter(function ($routes) {
                if ($this->cachedRoutes !== null) {
                    return $this->cachedRoutes;
                }

                return $this->cachedRoutes = $this->sites()->mapWithKeys(function ($site) use ($routes) {
                    $siteRoute = is_string($routes) ? $routes : ($routes[$site] ?? null);

                    return [$site => $siteRoute];
                });
            })
            ->afterSetter(fn () => $this->cachedRoutes = null)
            ->args(func_get_args());
    }

    public function route($site)
    {
        return $this->routes()->get($site);
    }

    public function requiresSlugs($require = null)
    {
        return $this->fluentlyGetOrSet('requiresSlugs')->args(func_get_args());
    }

    public function titleFormats($formats = null)
    {
        return $this
            ->fluentlyGetOrSet('titleFormats')
            ->setter(function ($format) {
                if (! $format) {
                    $format = [];
                }

                return $format;
            })
            ->getter(function ($formats) {
                return $this->sites()->mapWithKeys(function ($site) use ($formats) {
                    $siteRoute = is_string($formats) ? $formats : ($formats[$site] ?? null);

                    return [$site => $siteRoute];
                });
            })
            ->args(func_get_args());
    }

    public function titleFormat($site)
    {
        return $this->titleFormats()->get($site);
    }

    public function autoGeneratesTitles()
    {
        return $this->titleFormats !== [];
    }

    public function dated($dated = null)
    {
        if (func_num_args() === 0) {
            return $this->dated;
        }

        $this->dated = $dated;

        return $this;
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
                } elseif ($this->orderable() || $this->hasStructure()) {
                    return 'order';
                } elseif ($this->dated()) {
                    return 'date';
                }

                return 'title';
            })
            ->args(func_get_args());
    }

    public function customSortField()
    {
        return $this->sortField;
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

    public function customSortDirection()
    {
        return $this->sortDirection;
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

    public function icon($icon = null)
    {
        return $this
            ->fluentlyGetOrSet('icon')
            ->getter(function ($icon) {
                return $icon ?? 'collections';
            })
            ->args(func_get_args());
    }

    public function absoluteUrl($site = null)
    {
        if (! $mount = $this->mount()) {
            return null;
        }

        $site = $site ?? $this->sites()->first();

        return optional($mount->in($site))->absoluteUrl();
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

    public function hasVisibleEntryBlueprint()
    {
        return $this->entryBlueprints()->reject->hidden()->isNotEmpty();
    }

    public function entryBlueprints()
    {
        $blink = 'collection-entry-blueprints-'.$this->handle();

        return Blink::once($blink, function () {
            return $this->getEntryBlueprints();
        });
    }

    private function getEntryBlueprints()
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
        if (! $blueprint = $this->getBaseEntryBlueprint($blueprint)) {
            return null;
        }

        $blueprint->setParent($entry ?? $this);

        // Only dispatch the event when there's no entry.
        // When there is an entry, the event is dispatched from the entry.
        if (! $entry) {
            Blink::once(
                'collection-entryblueprintfound-'.$this->handle().'-'.$blueprint->handle(),
                fn () => EntryBlueprintFound::dispatch($blueprint)
            );
        }

        return $blueprint;
    }

    private function getBaseEntryBlueprint($blueprint)
    {
        $blink = 'collection-entry-blueprint-'.$this->handle().'-'.$blueprint;

        return Blink::once($blink, function () use ($blueprint) {
            if (is_null($blueprint)) {
                return $this->entryBlueprints()->reject->hidden()->first() ?? $this->entryBlueprints()->first();
            }

            return $this->entryBlueprints()->keyBy->handle()->get($blueprint)
                ?? $this->entryBlueprints()->keyBy->handle()->get(Str::singular($blueprint));
        });
    }

    public function fallbackEntryBlueprint()
    {
        $blueprint = (clone Blueprint::find('default'))
            ->setHandle(Str::singular($this->handle()))
            ->setNamespace('collections.'.$this->handle());

        $contents = $blueprint->contents();
        $contents['title'] = Str::singular($this->title());
        $blueprint->setContents($contents);

        return $blueprint;
    }

    public function ensureEntryBlueprintFields($blueprint)
    {
        $blueprint->ensureFieldPrepended('title', [
            'type' => ($auto = $this->autoGeneratesTitles()) ? 'hidden' : 'text',
            'required' => ! $auto,
        ]);

        if ($this->requiresSlugs()) {
            $blueprint->ensureField('slug', ['type' => 'slug', 'localizable' => true, 'validate' => 'max:200'], 'sidebar');
        }

        if ($this->dated()) {
            $blueprint->ensureField('date', ['type' => 'date', 'required' => true, 'default' => 'now'], 'sidebar');
        }

        foreach ($this->taxonomies() as $taxonomy) {
            if ($blueprint->hasField($taxonomy->handle())) {
                continue;
            }

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
                if (! Site::multiEnabled() || ! $sites) {
                    $sites = [Site::default()->handle()];
                }

                return collect($sites);
            })
            ->afterSetter(fn () => $this->cachedRoutes = null)
            ->args(func_get_args());
    }

    public function propagate($propagate = null)
    {
        return $this
            ->fluentlyGetOrSet('propagate')
            ->getter(function ($propagate) {
                return $propagate ?? false;
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
                return $layout ?? config('statamic.system.layout', 'layout');
            })
            ->args(func_get_args());
    }

    public function createLabel()
    {
        $key = "messages.{$this->handle()}_collection_create_entry";

        $translation = __($key);

        if ($translation === $key) {
            return __('Create Entry');
        }

        return $translation;
    }

    public function saveQuietly()
    {
        $this->withEvents = false;

        return $this->save();
    }

    public function save()
    {
        $isNew = ! Facades\Collection::handleExists($this->handle);

        $withEvents = $this->withEvents;
        $this->withEvents = true;

        if ($withEvents) {
            if ($isNew && CollectionCreating::dispatch($this) === false) {
                return false;
            }

            if (CollectionSaving::dispatch($this) === false) {
                return false;
            }
        }

        Facades\Collection::save($this);

        Blink::forget('collection-handles');
        Blink::forget('mounted-collections');
        Blink::flushStartingWith("collection-{$this->id()}");

        if ($withEvents) {
            if ($isNew) {
                CollectionCreated::dispatch($this);
            }

            CollectionSaved::dispatch($this);
        }

        return $this;
    }

    public function updateEntryUris($ids = null)
    {
        Facades\Entry::updateUris($this, $ids);

        return $this;
    }

    public function updateEntryOrder($ids = null)
    {
        Facades\Entry::updateOrders($this, $ids);

        return $this;
    }

    public function updateEntryParent($ids = null)
    {
        Facades\Entry::updateParents($this, $ids);

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
        $formerlyToArray = [
            'title' => $this->title,
            'handle' => $this->handle,
            'icon' => $this->icon,
            'routes' => $this->routes,
            'dated' => $this->dated,
            'past_date_behavior' => $this->pastDateBehavior(),
            'future_date_behavior' => $this->futureDateBehavior(),
            'default_publish_state' => $this->defaultPublishState,
            'sites' => $this->sites,
            'propagate' => $this->propagate(),
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
            'title_format' => $this->titleFormats,
            'autosave' => $this->autosave,
        ];

        $array = Arr::except($formerlyToArray, [
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
            'slugs' => $this->requiresSlugs() === true ? null : false,
            'date' => $this->dated ?: null,
            'sort_by' => $this->sortField,
            'sort_dir' => $this->sortDirection,
            'default_status' => $this->defaultPublishState === false ? 'draft' : null,
            'date_behavior' => [
                'past' => $this->pastDateBehavior,
                'future' => $this->futureDateBehavior,
            ],
            'preview_targets' => $this->previewTargetsForFile(),
            'origin_behavior' => ($ob = $this->originBehavior()) === 'select' ? null : $ob,
        ]));

        if (! Site::multiEnabled()) {
            unset($array['sites'], $array['propagate']);
        }

        if ($array['date_behavior'] == ['past' => 'public', 'future' => 'public']) {
            unset($array['date_behavior']);
        }

        $array['inject'] = Arr::pull($array, 'cascade');

        if ($this->hasStructure()) {
            $array['structure'] = $this->structureContents();
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

    public function originBehavior($origin = null)
    {
        return $this
            ->fluentlyGetOrSet('originBehavior')
            ->setter(function ($origin) {
                $origin = $origin ?? 'select';

                if (! in_array($origin, ['select', 'root', 'active'])) {
                    throw new InvalidArgumentException("Invalid origin behavior [$origin]. Must be \"select\", \"root\", or \"active\".");
                }

                return $origin;
            })
            ->args(func_get_args());
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

    public function autosaveInterval($interval = null)
    {
        return $this
            ->fluentlyGetOrSet('autosave')
            ->getter(function ($interval) {
                if (! config('statamic.autosave.enabled') || ! Statamic::pro() || ! $interval) {
                    return null;
                }

                return is_bool($interval) ? config('statamic.autosave.interval') : $interval;
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
                    $structure->handle($this->handle());
                }

                $this->structureContents = null;
                Blink::forget("collection-{$this->id()}-structure");

                return $structure;
            })
            ->args(func_get_args());
    }

    public function structureContents(?array $contents = null)
    {
        return $this
            ->fluentlyGetOrSet('structureContents')
            ->setter(function ($contents) {
                Blink::forget("collection-{$this->id()}-structure");
                $this->structure = null;

                return $contents;
            })
            ->getter(function ($contents) {
                if (! $structure = $this->structure()) {
                    return null;
                }

                return Arr::removeNullValues([
                    'root' => $structure->expectsRoot(),
                    'max_depth' => $structure->maxDepth(),
                    'slugs' => $structure->showSlugs() ?: null,
                ]);
            })
            ->args(func_get_args());
    }

    protected function makeStructureFromContents()
    {
        return (new CollectionStructure)
            ->handle($this->handle())
            ->expectsRoot($this->structureContents['root'] ?? false)
            ->showSlugs($this->structureContents['slugs'] ?? false)
            ->maxDepth($this->structureContents['max_depth'] ?? null);
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

    public function deleteQuietly()
    {
        $this->withEvents = false;

        return $this->delete();
    }

    public function delete()
    {
        $withEvents = $this->withEvents;
        $this->withEvents = true;

        if ($withEvents && CollectionDeleting::dispatch($this) === false) {
            return false;
        }

        $this->queryEntries()->get()->each(function ($entry) use ($withEvents) {
            $entry->deleteDescendants($withEvents);
            $withEvents ? $entry->delete() : $entry->deleteQuietly();
        });

        if ($this->hasStructure()) {
            $this->structure()->trees()->each->delete();
        }

        Facades\Collection::delete($this);

        if ($withEvents) {
            CollectionDeleted::dispatch($this);
        }

        Blink::forget('mounted-collections');

        return true;
    }

    public function truncate()
    {
        $this->queryEntries()->get()->each->delete();

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

                return Blink::store('collection-mounts')->once("{$this->id()}-{$mount}", function () use ($mount) {
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

    public function previewTargets($targets = null)
    {
        return $this
            ->fluentlyGetOrSet('previewTargets')
            ->getter(function () {
                return $this->basePreviewTargets()->merge($this->additionalPreviewTargets());
            })
            ->args(func_get_args());
    }

    public function basePreviewTargets()
    {
        $targets = empty($this->previewTargets)
            ? $this->defaultPreviewTargets()
            : $this->previewTargets;

        return collect($targets)->map(function ($target) {
            return $target + ['refresh' => $target['refresh'] ?? true];
        });
    }

    public function addPreviewTargets($targets)
    {
        Facades\Collection::addPreviewTargets($this->handle, $targets);

        return $this;
    }

    public function additionalPreviewTargets()
    {
        return Facades\Collection::additionalPreviewTargets($this->handle)->map(function ($target) {
            return $target + ['refresh' => $target['refresh'] ?? true];
        });
    }

    private function defaultPreviewTargets()
    {
        return [
            [
                'label' => 'Entry',
                'format' => '{permalink}',
                'refresh' => true,
            ],
        ];
    }

    private function previewTargetsForFile()
    {
        $targets = $this->previewTargets;

        if ($targets === $this->defaultPreviewTargets()) {
            return null;
        }

        return collect($targets)->map(function ($target) {
            if (! $target['format']) {
                return null;
            }

            return [
                'label' => $target['label'],
                'url' => $target['format'],
                'refresh' => $target['refresh'],
            ];
        })->filter()->values()->all();
    }

    public function deleteFile()
    {
        File::delete($this->path());
        File::delete(dirname($this->path()).'/'.$this->handle);
    }

    public function commandPaletteLinksForBlueprints()
    {
        return $this
            ->entryBlueprints()
            ->map(fn ($blueprint) => $blueprint->commandPaletteLink(
                type: 'Collections',
                url: cp_route('blueprints.collections.edit', [$this, $blueprint]),
            ));
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
