<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Illuminate\Http\Request;
use Statamic\Contracts\Entries\Collection as CollectionContract;
use Statamic\CP\Column;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Scope;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Structures\CollectionStructure;
use Statamic\Support\Str;

class CollectionsController extends CpController
{
    public function index()
    {
        $this->authorize('index', CollectionContract::class, __('You are not authorized to view collections.'));

        $collections = Collection::all()->filter(function ($collection) {
            return User::current()->can('view', $collection);
        })->map(function ($collection) {
            return [
                'id' => $collection->handle(),
                'title' => $collection->title(),
                'entries' => \Statamic\Facades\Entry::query()->where('collection', $collection->handle())->count(),
                'edit_url' => $collection->editUrl(),
                'delete_url' => $collection->deleteUrl(),
                'entries_url' => cp_route('collections.show', $collection->handle()),
                'scaffold_url' => cp_route('collections.scaffold', $collection->handle()),
                'deleteable' => User::current()->can('delete', $collection),
            ];
        })->values();

        return view('statamic::collections.index', [
            'collections' => $collections,
            'columns' => [
                Column::make('title')->label(__('Title')),
                Column::make('entries')->label(__('Entries')),
            ],
        ]);
    }

    public function show(Request $request, $collection)
    {
        $this->authorize('view', $collection, __('You are not authorized to view this collection.'));

        $blueprints = $collection->entryBlueprints()->map(function ($blueprint) {
            return [
                'handle' => $blueprint->handle(),
                'title' => $blueprint->title(),
            ];
        });

        $site = $request->site ? Site::get($request->site) : Site::selected();

        $viewData = [
            'collection' => $collection,
            'blueprints' => $blueprints,
            'site' => $site->handle(),
            'filters' => Scope::filters('entries', [
                'collection' => $collection->handle(),
                'blueprints' => $blueprints->pluck('handle')->all(),
            ]),
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
            'structureSites' => $collection->sites()->map(function ($site) use ($structure) {
                $tree = $structure->in($site);

                return [
                    'handle' => $tree->locale(),
                    'name' => $tree->site()->name(),
                    'url' => $tree->showUrl(),
                ];
            })->values()->all(),
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
            'blueprints' => $collection->entryBlueprints()->map->handle()->reject(function ($handle) {
                return $handle == 'entry_link';
            })->all(),
            'links' => $collection->entryBlueprints()->map->handle()->contains('entry_link'),
            'taxonomies' => $collection->taxonomies()->map->handle()->all(),
            'default_publish_state' => $collection->defaultPublishState(),
            'template' => $collection->template(),
            'layout' => $collection->layout(),
            'amp' => $collection->ampable(),
            'sites' => $collection->sites()->all(),
            'routes' => $collection->routes()->all(),
            'mount' => optional($collection->mount())->id(),
        ];

        $fields = ($blueprint = $this->editFormBlueprint())
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

        $data = $request->validate([
            'title' => 'required',
            'handle' => 'nullable|alpha_dash',
        ]);

        $handle = $request->handle ?? Str::snake($request->title);

        if (Collection::find($handle)) {
            throw new \Exception(__('Collection already exists'));
        }

        $collection = Collection::make($handle);

        $collection->title($request->title)
            ->pastDateBehavior('public')
            ->futureDateBehavior('private');

        $collection->save();

        session()->flash('success', __('Collection created'));

        return [
            'redirect' => route('statamic.cp.collections.show', $handle),
        ];
    }

    public function update(Request $request, $collection)
    {
        $this->authorize('update', $collection, __('You are not authorized to edit this collection.'));

        $fields = $this->editFormBlueprint()->fields()->addValues($request->all());

        $fields->validate();

        $values = $fields->process()->values()->all();

        $blueprints = collect($values['blueprints']);
        if ($values['links']) {
            $blueprints->push('entry_link')->unique();
        } elseif ($blueprints->contains('entry_link')) {
            $blueprints->diff(['entry_link'])->values();
        }

        $collection
            ->title($values['title'])
            ->routes($values['routes'])
            ->dated($values['dated'])
            ->template($values['template'])
            ->layout($values['layout'])
            ->defaultPublishState($values['default_publish_state'])
            ->sortDirection($values['sort_direction'])
            ->ampable($values['amp'])
            ->entryBlueprints($blueprints->all())
            ->mount($values['mount'] ?? null)
            ->taxonomies($values['taxonomies'] ?? [])
            ->futureDateBehavior(array_get($values, 'future_date_behavior'))
            ->pastDateBehavior(array_get($values, 'past_date_behavior'))
            ->mount(array_get($values, 'mount'));

        if ($sites = array_get($values, 'sites')) {
            $collection->sites($sites);
        }

        if (! $values['structured']) {
            $collection->structure(null);
        } else {
            $collection->structure($this->makeStructure($collection, $values['max_depth'], $values['expects_root'], $values['sites'] ?? null));
        }

        $collection->save();

        return $collection->toArray();
    }

    protected function makeStructure($collection, $maxDepth, $expectsRoot, $sites)
    {
        if (! $structure = $collection->structure()) {
            $structure = (new CollectionStructure)->collection($collection);
        }

        return $structure
            ->maxDepth($maxDepth)
            ->expectsRoot($expectsRoot);
    }

    public function destroy($collection)
    {
        $this->authorize('delete', $collection, __('You are not authorized to delete this collection.'));

        $collection->delete();
    }

    protected function editFormBlueprint()
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
                    ],
                    'max_depth' => [
                        'display' => __('Max Depth'),
                        'instructions' => __('statamic::messages.max_depth_instructions'),
                        'type' => 'integer',
                        'validate' => 'min:0',
                        'if' => ['structured' => true],
                    ],
                    'expects_root' => [
                        'display' => __('Expect a root page'),
                        'instructions' => __('statamic::messages.expect_root_instructions'),
                        'type' => 'toggle',
                        'if' => ['structured' => true],
                    ],
                ],
            ],
            'content_model' => [
                'display' => __('Content Model'),
                'fields' => [
                    'blueprints' => [
                        'display' => __('Blueprints'),
                        'instructions' => __('statamic::messages.collections_blueprint_instructions'),
                        'type' => 'blueprints',
                        'validate' => 'array',
                        'mode' => 'select',
                    ],
                    'links' => [
                        'display' => __('Links'),
                        'instructions' => __('statamic::messages.collections_links_instructions'),
                        'type' => 'toggle',
                    ],
                    'taxonomies' => [
                        'display' => __('Taxonomies'),
                        'instructions' => __('statamic::messages.collections_taxonomies_instructions'),
                        'type' => 'taxonomies',
                        'mode' => 'select',
                    ],
                    'default_publish_state' => [
                        'display' => __('Publish by Default'),
                        'instructions' => __('statamic::messages.collections_default_publish_state_instructions'),
                        'type' => 'toggle',
                    ],
                    'template' => [
                        'display' => __('Template'),
                        'instructions' => __('statamic::messages.collection_configure_template_instructions'),
                        'type' => 'template',
                    ],
                    'layout' => [
                        'display' => __('Layout'),
                        'instructions' => __('statamic::messages.collection_configure_layout_instructions'),
                        'type' => 'template',
                    ],
                ],
            ],
        ];

        if (Site::hasMultiple()) {
            $fields['sites'] = [
                'display' => __('Sites'),
                'fields' => [
                    'sites' => [
                        'type' => 'sites',
                        'mode' => 'select',
                        'required' => true,
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
                    'mount' => [
                        'display' => __('Mount'),
                        'instructions' => __('statamic::messages.collections_mount_instructions'),
                        'type' => 'entries',
                        'max_items' => 1,
                        'create' => false,
                    ],
                    'amp' => [
                        'display' => __('Enable AMP'),
                        'instructions' => __('statamic::messages.collections_amp_instructions'),
                        'type' => 'toggle',
                    ],
                ],
            ],
        ]);

        return Blueprint::makeFromSections($fields);
    }
}
