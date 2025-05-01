<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Illuminate\Http\Request;
use Statamic\Contracts\Entries\Collection as CollectionContract;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\CP\Column;
use Statamic\Exceptions\SiteNotFoundException;
use Statamic\Facades\Action;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Scope;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Rules\Handle;
use Statamic\Statamic;
use Statamic\Structures\CollectionStructure;
use Statamic\Support\Arr;
use Statamic\Support\Str;

use function Statamic\trans as __;

class CollectionsController extends CpController
{
    public function index(Request $request)
    {
        $this->authorize('index', CollectionContract::class, __('You are not authorized to view collections.'));

        $columns = [
            Column::make('title')->label(__('Title')),
            Column::make('entries')->label(__('Entries'))->numeric(true),
        ];

        if ($request->wantsJson()) {
            return [
                'data' => $this->collections(),
                'meta' => [
                    'columns' => $columns,
                ],
            ];
        }

        return view('statamic::collections.index', [
            'collections' => $this->collections(),
            'columns' => $columns,
        ]);
    }

    private function collections()
    {
        return Collection::all()->filter(function ($collection) {
            return User::current()->can('configure collections')
                || User::current()->can('view', $collection)
                && $collection->sites()->contains(Site::selected()->handle());
        })->map(function ($collection) {
            return [
                'id' => $collection->handle(),
                'title' => $collection->title(),
                'entries' => $collection->queryEntries()->where('site', Site::selected())->orderBy('date', 'desc')->limit(5)->get(),
                'entries_count' => $collection->queryEntries()->where('site', Site::selected())->count(),
                'published_entries_count' => $collection->queryEntries()->where('site', Site::selected())->where('status', 'published')->count(),
                'draft_entries_count' => $collection->queryEntries()->where('site', Site::selected())->where('status', 'draft')->count(),
                'scheduled_entries_count' => $collection->queryEntries()->where('site', Site::selected())->where('status', 'scheduled')->count(),
                'blueprints' => $collection->entryBlueprints()->reject->hidden()->values(),
                'columns' => [
                    ['label' => 'Title', 'field' => 'title', 'visible' => true],
                    ['label' => 'Date', 'field' => 'date', 'visible' => true]
                ],
                'dated' => $collection->dated(),
                'edit_url' => $collection->editUrl(),
                'delete_url' => $collection->deleteUrl(),
                'entries_url' => cp_route('collections.show', $collection->handle()),
                'create_entry_url' => $collection->createEntryUrl(Site::selected()),
                'url' => $collection->absoluteUrl(Site::selected()->handle()),
                'blueprints_url' => cp_route('collections.blueprints.index', $collection->handle()),
                'scaffold_url' => cp_route('collections.scaffold', $collection->handle()),
                'deleteable' => User::current()->can('delete', $collection),
                'editable' => User::current()->can('edit', $collection),
                'blueprint_editable' => User::current()->can('configure fields'),
                'available_in_selected_site' => $collection->sites()->contains(Site::selected()->handle()),
                'actions' => Action::for($collection),
                'actions_url' => cp_route('collections.actions.run', ['collection' => $collection->handle()]),
            ];
        })->sortBy('title')->values();
    }

    public function show(Request $request, $collection)
    {
        $this->authorize('view', $collection, __('You are not authorized to view this collection.'));

        $site = $request->site ? Site::get($request->site) : Site::selected();

        if ($response = $this->ensureCollectionIsAvailableOnSite($collection, $site)) {
            return $response;
        }

        $blueprints = $collection
            ->entryBlueprints()
            ->reject->hidden()
            ->map(function ($blueprint) {
                return [
                    'handle' => $blueprint->handle(),
                    'title' => __($blueprint->title()),
                ];
            })->values();

        $blueprint = $collection->entryBlueprint();

        $columns = $blueprint
            ->columns()
            ->put('status', Column::make('status')
                ->listable(true)
                ->visible(true)
                ->defaultVisibility(true)
                ->sortable(false))
            ->setPreferred("collections.{$collection->handle()}.columns")
            ->rejectUnlisted()
            ->values();

        $viewData = [
            'collection' => $collection,
            'blueprints' => $blueprints,
            'site' => $site->handle(),
            'columns' => $columns,
            'filters' => Scope::filters('entries', [
                'collection' => $collection->handle(),
                'blueprints' => $blueprints->pluck('handle')->all(),
            ]),
            'sites' => $authorizedSites = $this->getAuthorizedSitesForCollection($collection),
            'createUrls' => $collection->sites()
                ->mapWithKeys(fn ($site) => [$site => cp_route('collections.entries.create', [$collection->handle(), $site])])
                ->all(),
            'canCreate' => User::current()->can('create', [EntryContract::class, $collection]) && $collection->hasVisibleEntryBlueprint(),
            'canChangeLocalizationDeleteBehavior' => count($authorizedSites) > 1 && (count($authorizedSites) == $collection->sites()->count()),
            'actions' => Action::for($collection, ['view' => 'form']),
        ];

        if ($collection->queryEntries()->count() === 0) {
            return view('statamic::collections.empty', $viewData);
        }

        if (! $collection->hasStructure()) {
            return view('statamic::collections.show', $viewData);
        }

        $structure = $collection->structure();

        return view('statamic::collections.show', array_merge($viewData, [
            'structure' => $structure,
            'expectsRoot' => $structure->expectsRoot(),
        ]));
    }

    public function create()
    {
        $this->authorize('create', CollectionContract::class, __('You are not authorized to create collections.'));

        return view('statamic::collections.create');
    }

    public function fresh($collection)
    {
        $this->authorize('view', $collection, __('You are not authorized to view this collection.'));

        return view('statamic::collections.fresh');
    }

    public function edit($collection)
    {
        $this->authorize('edit', $collection, __('You are not authorized to edit this collection.'));

        $values = [
            'title' => $collection->title(),
            'handle' => $collection->handle(),
            'dated' => $collection->dated(),
            'past_date_behavior' => $collection->pastDateBehavior(),
            'future_date_behavior' => $collection->futureDateBehavior(),
            'structured' => $collection->hasStructure(),
            'sort_direction' => $collection->sortDirection(),
            'max_depth' => optional($collection->structure())->maxDepth(),
            'expects_root' => optional($collection->structure())->expectsRoot(),
            'show_slugs' => optional($collection->structure())->showSlugs(),
            'require_slugs' => $collection->requiresSlugs(),
            'links' => $collection->entryBlueprints()->map->handle()->contains('link'),
            'taxonomies' => $collection->taxonomies()->map->handle()->all(),
            'revisions' => $collection->revisionsEnabled(),
            'default_publish_state' => $collection->defaultPublishState(),
            'template' => $collection->template(),
            'layout' => $collection->layout(),
            'sites' => $collection->sites()->all(),
            'propagate' => $collection->propagate(),
            'routes' => $collection->routes()->unique()->count() === 1
                ? $collection->routes()->first()
                : $collection->routes()->all(),
            'mount' => optional($collection->mount())->id(),
            'title_formats' => $collection->titleFormats()->unique()->count() === 1
                ? $collection->titleFormats()->first()
                : $collection->titleFormats()->all(),
            'preview_targets' => $collection->basePreviewTargets(),
            'origin_behavior' => $collection->originBehavior(),
        ];

        $fields = ($blueprint = $this->editFormBlueprint($collection))
            ->fields()
            ->addValues($values)
            ->preProcess();

        return view('statamic::collections.edit', [
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
            'collection' => $collection,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('store', CollectionContract::class, __('You are not authorized to create collections.'));

        $request->validate([
            'title' => 'required',
            'handle' => ['nullable', new Handle],
        ]);

        $handle = $request->handle ?? Str::snake($request->title);

        if (Collection::find($handle)) {
            throw new \Exception(__('Collection already exists'));
        }

        $collection = Collection::make($handle);

        $collection->title($request->title)
            ->pastDateBehavior('public')
            ->futureDateBehavior('private');

        if (Site::multiEnabled()) {
            $collection->sites([Site::selected()->handle()]);
        }

        $collection->save();

        session()->flash('success', __('Collection created'));

        return ['redirect' => $collection->showUrl()];
    }

    public function update(Request $request, $collection)
    {
        $this->authorize('update', $collection, __('You are not authorized to edit this collection.'));

        $fields = $this->editFormBlueprint($collection)->fields()->addValues($request->all());

        $fields->validate();

        $values = $fields->process()->values()->all();

        $this->updateLinkBlueprint($values['links'], $collection);

        $collection
            ->title($values['title'])
            ->routes($values['routes'])
            ->dated($values['dated'])
            ->template($values['template'])
            ->layout($values['layout'])
            ->defaultPublishState($values['default_publish_state'])
            ->sortDirection($values['sort_direction'])
            ->mount($values['mount'] ?? null)
            ->revisionsEnabled($values['revisions'] ?? false)
            ->taxonomies($values['taxonomies'] ?? [])
            ->futureDateBehavior(Arr::get($values, 'future_date_behavior'))
            ->pastDateBehavior(Arr::get($values, 'past_date_behavior'))
            ->mount(Arr::get($values, 'mount'))
            ->propagate(Arr::get($values, 'propagate'))
            ->titleFormats($values['title_formats'])
            ->requiresSlugs($values['require_slugs'])
            ->previewTargets($values['preview_targets']);

        if ($sites = Arr::get($values, 'sites')) {
            $collection
                ->sites($sites)
                ->originBehavior($values['origin_behavior']);
        }

        if (! $values['structured']) {
            if ($structure = $collection->structure()) {
                $structure->trees()->each->delete();
            }
            $collection->structure(null);
        } else {
            $collection->structure($this->makeStructure($collection, $values['max_depth'], $values['expects_root'], $values['show_slugs']));
        }

        $collection->save();
    }

    protected function updateLinkBlueprint($shouldExist, $collection)
    {
        $namespace = 'collections.'.$collection->handle();
        $blueprints = Blueprint::in($namespace);
        $alreadyExists = $blueprints->has('link');

        if ($shouldExist && ! $alreadyExists) {
            if ($blueprints->count() === 0) {
                $collection->entryBlueprint()->save();
            }
            $this->createLinkBlueprint($namespace);
        }

        if (! $shouldExist && $alreadyExists) {
            Blueprint::find($namespace.'.link')->delete();
        }
    }

    protected function createLinkBlueprint($namespace)
    {
        Blueprint::make('link')
            ->setNamespace($namespace)
            ->setContents([
                'title' => __('Link'),
                'fields' => [
                    ['handle' => 'title', 'field' => ['type' => 'text']],
                    [
                        'handle' => 'redirect',
                        'field' => [
                            'type' => 'group', 'required' => true, 'width' => '100',
                            'fields' => [
                                ['handle' => 'url', 'field' => ['type' => 'link', 'required' => true, 'width' => '100', 'display' => __('Location')]],
                                ['handle' => 'status', 'field' => ['type' => 'radio', 'inline' => 'true', 'required' => true, 'options' => [301 => __('301 (Permanent)'), 302 => __('302 (Temporary)')], 'width' => '100', 'display' => __('HTTP Status'), 'default' => 302]],
                            ],
                        ],
                    ],
                ],
            ])
            ->save();
    }

    protected function makeStructure($collection, $maxDepth, $expectsRoot, $showSlugs)
    {
        if (! $structure = $collection->structure()) {
            $structure = new CollectionStructure;
        }

        return $structure
            ->maxDepth($maxDepth)
            ->expectsRoot($expectsRoot)
            ->showSlugs($showSlugs);
    }

    public function destroy($collection)
    {
        $this->authorize('delete', $collection, __('You are not authorized to delete this collection.'));

        $collection->delete();
    }

    protected function editFormBlueprint($collection)
    {
        $fields = [
            'name' => [
                'display' => __('Name'),
                'fields' => [
                    'title' => [
                        'display' => __('Title'),
                        'instructions' => __('statamic::messages.collection_configure_title_instructions'),
                        'type' => 'text',
                        'validate' => 'required',
                    ],
                ],
            ],
            'dates' => [
                'display' => __('Dates & Behaviors'),
                'fields' => [
                    'dated' => [
                        'display' => __('Enable Publish Dates'),
                        'instructions' => __('statamic::messages.collection_configure_dated_instructions'),
                        'type' => 'toggle',
                    ],
                    'past_date_behavior' => [
                        'display' => __('Past Date Behavior'),
                        'instructions' => __('statamic::messages.collections_past_date_behavior_instructions'),
                        'type' => 'select',
                        'options' => [
                            'public' => __('statamic::messages.collection_configure_date_behavior_public'),
                            'unlisted' => __('statamic::messages.collection_configure_date_behavior_unlisted'),
                            'private' => __('statamic::messages.collection_configure_date_behavior_private'),
                        ],
                        'if' => [
                            'dated' => true,
                        ],
                        'width' => '50',
                    ],
                    'future_date_behavior' => [
                        'display' => __('Future Date Behavior'),
                        'instructions' => __('statamic::messages.collections_future_date_behavior_instructions'),
                        'type' => 'select',
                        'options' => [
                            'public' => __('statamic::messages.collection_configure_date_behavior_public'),
                            'unlisted' => __('statamic::messages.collection_configure_date_behavior_unlisted'),
                            'private' => __('statamic::messages.collection_configure_date_behavior_private'),
                        ],
                        'if' => [
                            'dated' => true,
                        ],
                        'width' => '50',
                    ],
                ],
            ],
            'ordering' => [
                'display' => __('Ordering'),
                'fields' => [
                    'structured' => [
                        'display' => __('Orderable'),
                        'instructions' => __('statamic::messages.collections_orderable_instructions'),
                        'type' => 'toggle',
                    ],
                    'sort_direction' => [
                        'display' => __('Sort Direction'),
                        'instructions' => __('statamic::messages.collections_sort_direction_instructions'),
                        'type' => 'select',
                        'options' => [
                            'asc' => __('Ascending'),
                            'desc' => __('Descending'),
                        ],
                        'width' => '66',
                    ],
                    'max_depth' => [
                        'display' => __('Max Depth'),
                        'instructions' => __('statamic::messages.max_depth_instructions'),
                        'type' => 'integer',
                        'validate' => 'min:0',
                        'if' => ['structured' => true],
                        'width' => '33',
                    ],
                    'expects_root' => [
                        'display' => __('Expect a root page'),
                        'instructions' => __('statamic::messages.expect_root_instructions'),
                        'type' => 'toggle',
                        'if' => ['structured' => true],
                        'width' => '50',
                    ],
                    'show_slugs' => [
                        'display' => __('Slugs'),
                        'instructions' => __('statamic::messages.show_slugs_instructions'),
                        'type' => 'toggle',
                        'if' => ['structured' => true],
                        'width' => '50',
                    ],
                ],
            ],
            'content_model' => [
                'display' => __('Content Model'),
                'fields' => [
                    'blueprints' => [
                        'display' => __('Blueprints'),
                        'instructions' => __('statamic::messages.collections_blueprint_instructions'),
                        'type' => 'html',
                        'html' => ''.
                            '<div class="text-xs">'.
                            '   <span class="rtl:ml-4 ltr:mr-4">'.$collection->entryBlueprints()->map(fn ($bp) => __($bp->title()))->join(', ').'</span>'.
                            '   <a href="'.cp_route('collections.blueprints.index', $collection).'" class="text-blue">'.__('Edit').'</a>'.
                            '</div>',
                    ],
                    'default_publish_state' => [
                        'display' => __('Publish by Default'),
                        'instructions' => __('statamic::messages.collections_default_publish_state_instructions'),
                        'type' => 'toggle',
                        'width' => '50',
                    ],
                    'links' => [
                        'display' => __('Links'),
                        'instructions' => __('statamic::messages.collections_links_instructions'),
                        'type' => 'toggle',
                        'width' => '50',
                    ],
                    'template' => [
                        'display' => __('Template'),
                        'instructions' => __('statamic::messages.collection_configure_template_instructions'),
                        'type' => 'template',
                        'placeholder' => __('System default'),
                        'blueprint' => true,
                        'width' => '50',
                    ],
                    'layout' => [
                        'display' => __('Layout'),
                        'instructions' => __('statamic::messages.collection_configure_layout_instructions'),
                        'type' => 'template',
                        'width' => '50',
                    ],
                    'taxonomies' => [
                        'display' => __('Taxonomies'),
                        'instructions' => __('statamic::messages.collections_taxonomies_instructions'),
                        'type' => 'taxonomies',
                        'mode' => 'select',
                        'width' => '50',
                    ],
                    'title_formats' => [
                        'display' => __('Automatic Title Format'),
                        'instructions' => __('statamic::messages.collection_configure_title_format_instructions'),
                        'type' => 'collection_title_formats',
                        'width' => '50',
                    ],
                ],
            ],
        ];

        if (Statamic::pro() && config('statamic.revisions.enabled')) {
            $fields['revisions'] = [
                'display' => __('Revisions'),
                'fields' => [
                    'revisions' => [
                        'type' => 'toggle',
                        'display' => __('Enable Revisions'),
                        'instructions' => __('statamic::messages.collection_revisions_instructions'),
                    ],
                ],
            ];
        }

        if (Site::multiEnabled()) {
            $fields['sites'] = [
                'display' => __('Sites'),
                'fields' => [
                    'sites' => [
                        'type' => 'sites',
                        'mode' => 'select',
                        'required' => true,
                    ],
                    'propagate' => [
                        'type' => 'toggle',
                        'display' => __('Propagate'),
                        'instructions' => __('statamic::messages.collection_configure_propagate_instructions'),
                    ],
                    'origin_behavior' => [
                        'type' => 'select',
                        'display' => __('Origin Behavior'),
                        'instructions' => __('statamic::messages.collection_configure_origin_behavior_instructions'),
                        'default' => 'select',
                        'options' => [
                            'select' => __('statamic::messages.collection_configure_origin_behavior_option_select'),
                            'root' => __('statamic::messages.collection_configure_origin_behavior_option_root'),
                            'active' => __('statamic::messages.collection_configure_origin_behavior_option_active'),
                        ],
                    ],
                ],
            ];
        }

        $fields = array_merge($fields, [
            'routing' => [
                'display' => __('Routing & URLs'),
                'fields' => [
                    'routes' => [
                        'display' => __('Route'),
                        'instructions' => __('statamic::messages.collections_route_instructions'),
                        'type' => 'collection_routes',
                    ],
                    'require_slugs' => [
                        'display' => __('Require Slugs'),
                        'instructions' => __('statamic::messages.collection_configure_require_slugs_instructions'),
                        'type' => 'toggle',
                        'width' => '50',
                    ],
                    'mount' => [
                        'display' => __('Mount'),
                        'instructions' => __('statamic::messages.collections_mount_instructions'),
                        'type' => 'entries',
                        'max_items' => 1,
                        'create' => false,
                        'collections' => Collection::all()->map->handle()->reject(function ($collectionHandle) use ($collection) {
                            return $collectionHandle === $collection->handle();
                        })->values()->all(),
                        'width' => '50',
                    ],
                    'preview_targets' => [
                        'display' => __('Preview Targets'),
                        'instructions' => __('statamic::messages.collections_preview_targets_instructions'),
                        'type' => 'grid',
                        'fields' => [
                            [
                                'handle' => 'label',
                                'field' => [
                                    'display' => __('Label'),
                                    'type' => 'text',
                                ],
                            ],
                            [
                                'handle' => 'format',
                                'field' => [
                                    'display' => __('Format'),
                                    'type' => 'text',
                                    'dir' => 'ltr',
                                ],
                            ],
                            [
                                'handle' => 'refresh',
                                'field' => [
                                    'display' => __('Refresh'),
                                    'type' => 'toggle',
                                    'instructions' => __('statamic::messages.collections_preview_target_refresh_instructions'),
                                    'default' => true,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        return Blueprint::makeFromTabs($fields);
    }

    protected function getAuthorizedSitesForCollection($collection)
    {
        return $collection
            ->sites()
            ->mapWithKeys(fn ($handle) => [$handle => Site::get($handle)])
            ->each(fn ($site, $handle) => throw_unless($site, new SiteNotFoundException($handle)))
            ->filter(fn ($site) => User::current()->can('view', $site))
            ->map(function ($site) {
                return [
                    'handle' => $site->handle(),
                    'name' => $site->name(),
                ];
            })
            ->values()
            ->all();
    }

    protected function ensureCollectionIsAvailableOnSite($collection, $site)
    {
        if (Site::multiEnabled() && ! $collection->sites()->contains($site->handle())) {
            return redirect(cp_route('collections.index'))->with('error', __('Collection is not available on site ":handle".', ['handle' => $site->handle]));
        }
    }
}
