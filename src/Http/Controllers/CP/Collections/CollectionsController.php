<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Illuminate\Http\Request;
use Statamic\Contracts\Entries\Collection as CollectionContract;
use Statamic\CP\Column;
use Statamic\Facades\Action;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Scope;
use Statamic\Facades\Site;
use Statamic\Facades\Structure;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Structures\CollectionStructure;
use Statamic\Support\Str;

class CollectionsController extends CpController
{
    public function index()
    {
        $this->authorize('index', CollectionContract::class, 'You are not authorized to view any collections.');

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
                'deleteable' => User::current()->can('delete', $collection)
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
        $this->authorize('view', $collection, 'You are not authorized to view this collection.');

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
        $tree = $structure->in($site->handle());

        return view('statamic::collections.show', array_merge($viewData, [
            'structure' => $structure,
            'expectsRoot' => $structure->expectsRoot(),
            'localizations' => $structure->sites()->map(function ($handle) use ($structure, $tree) {
                $localized = $structure->in($handle);
                $exists = $localized !== null;
                if (!$exists) {
                    return null;
                }
                return [
                    'handle' => $handle,
                    'name' => Site::get($handle)->name(),
                    'active' => $handle === $tree->locale(),
                    'exists' => $exists,
                    'url' => $exists ? $localized->editUrl() : null,
                ];
            })->filter()->all()
        ]));
    }

    public function create()
    {
        $this->authorize('create', CollectionContract::class, 'You are not authorized to create collections.');

        return view('statamic::collections.create');
    }

    public function fresh($collection)
    {
        $this->authorize('view', $collection, 'You are not authorized to view this collection.');

        return view('statamic::collections.fresh');
    }

    public function edit($collection)
    {
        $this->authorize('edit', $collection, 'You are not authorized to edit this collection.');

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
            'blueprints' => $collection->entryBlueprints()->map->handle()->all(),
            'taxonomies' => $collection->taxonomies()->map->handle()->all(),
            'default_publish_state' => $collection->defaultPublishState(),
            'template' => $collection->template(),
            'layout' => $collection->layout(),
            'route' => $collection->route(),
            'amp' => $collection->ampable(),
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
        $this->authorize('store', CollectionContract::class, 'You are not authorized to create collections.');

        $data = $request->validate([
            'title' => 'required',
            'handle' => 'nullable|alpha_dash'
        ]);

        $handle = $request->handle ?? snake_case($request->title);

        if (Collection::find($handle)) {
            throw new \Exception('Collection already exists');
        }

        $collection = Collection::make($handle);

        $collection->title($request->title)
            ->pastDateBehavior('public')
            ->futureDateBehavior('private');

        $collection->save();

        session()->flash('success', __('Collection created'));

        return [
            'redirect' => route('statamic.cp.collections.show', $handle)
        ];
    }

    public function update(Request $request, $collection)
    {
        $this->authorize('update', $collection, 'You are not authorized to edit this collection.');

        $fields = $this->editFormBlueprint()->fields()->addValues($request->all());

        $fields->validate();

        $values = $fields->process()->values()->all();

        $collection
            ->title($values['title'])
            ->route($values['route'])
            ->dated($values['dated'])
            ->template($values['template'])
            ->layout($values['layout'])
            ->defaultPublishState($values['default_publish_state'])
            ->sortDirection($values['sort_direction'])
            ->ampable($values['amp'])
            ->entryBlueprints($values['blueprints'])
            ->mount($values['mount'] ?? null)
            ->taxonomies($values['taxonomies'] ?? []);

        if ($futureDateBehavior = array_get($values, 'future_date_behavior')) {
            $collection->futureDateBehavior($futureDateBehavior);
        }

        if ($pastDateBehavior = array_get($values, 'past_date_behavior')) {
            $collection->pastDateBehavior($pastDateBehavior);
        }

        if (! $values['structured']) {
            $collection->structure(null);
        } else {
            $collection->structure($this->makeStructure($collection, $values['max_depth'], $values['expects_root']));
        }

        $collection->save();

        return $collection->toArray();
    }

    protected function makeStructure($collection, $maxDepth, $expectsRoot)
    {
        if (! $structure = $collection->structure()) {
            $structure = new CollectionStructure;

            // todo: make multiple trees based on the existing multisite nature of the collection
            $trees = [
                Site::default()->handle() => $collection->queryEntries()->get('id')->map(function ($entry) {
                    return ['entry' => $entry->id()];
                })->all()
            ];

            foreach ($trees as $site => $contents) {
                $tree = $structure->makeTree($site)->tree($contents);
                $structure->addTree($tree);
            }
        }

        return $structure
            ->maxDepth($maxDepth)
            ->expectsRoot($expectsRoot);
    }

    public function destroy($collection)
    {
        $this->authorize('delete', $collection, 'You are not authorized to delete this collection.');

        $collection->delete();
    }

    protected function editFormBlueprint()
    {
        return Blueprint::makeFromSections([
            'name' => [
                'display' => __('Name'),
                'fields' => [
                    'title' => [
                        'type' => 'text',
                        'instructions' => __('statamic::messages.collection_configure_title_instructions'),
                        'validate' => 'required',
                    ]
                ]
            ],
            'dates' => [
                'display' => __('Dates & Behaviors'),
                'fields' => [
                    'dated' => [
                        'type' => 'toggle',
                        'display' => __('Enable Publish Dates'),
                        'instructions' => 'Publish dates can be used to schedule and expire content.'
                    ],
                    'past_date_behavior' => [
                        'type' => 'select',
                        'display' => __('Past Date Behavior'),
                        'instructions' => __('statamic::messages.collections_past_date_behavior_instructions'),
                        'options' => [
                            'public' => 'Public - Always visible',
                            'unlisted' => 'Unlisted - Hidden from listings, URLs visible',
                            'private' => 'Private - Hidden from listings, URLs 404'
                        ],
                    ],
                    'future_date_behavior' => [
                        'type' => 'select',
                        'display' => __('Future Date Behavior'),
                        'instructions' => __('statamic::messages.collections_future_date_behavior_instructions'),
                        'options' => [
                            'public' => 'Public - Always visible',
                            'unlisted' => 'Unlisted - Hidden from listings, URLs visible',
                            'private' => 'Private - Hidden from listings, URLs 404'
                        ],
                    ],
                ],
            ],
            'ordering' => [
                'fields' => [
                    'structured' => [
                        'display' => __('Orderable'),
                        'type' => 'toggle',
                        'instructions' => __('statamic::messages.collections_orderable_instructions'),
                    ],
                    'sort_direction' => [
                        'type' => 'select',
                        'instructions' => __('statamic::messages.collections_sort_direction_instructions'),
                        'options' => [
                            'asc' => 'Ascending',
                            'desc' => 'Descending'
                        ],
                    ],
                    'max_depth' => [
                        'type' => 'integer',
                        'display' => 'Max depth',
                        'instructions' => 'The maximum number of levels deep a page may be nested. Leave blank for no limit.',
                        'validate' => 'min:0',
                        'if' => ['structured' => true],
                    ],
                    'expects_root' => [
                        'type' => 'toggle',
                        'display' => 'Expect a root page',
                        'instructions' => 'The first page in the tree should be considered the "root" or "home" page.',
                        'if' => ['structured' => true],
                    ],
                ],
            ],
            'content_model' => [
                'display' => 'Content Model',
                'fields' => [
                    'blueprints' => [
                        'type' => 'blueprints',
                        'instructions' => __('statamic::messages.collections_blueprint_instructions'),
                        'validate' => 'array',
                        'mode' => 'select',
                    ],
                    'taxonomies' => [
                        'type' => 'taxonomies',
                        'instructions' => __('statamic::messages.collections_taxonomies_instructions'),
                        'mode' => 'select',
                    ],
                    'default_publish_state' => [
                        'display' => __('Publish by Default'),
                        'type' => 'toggle',
                        'instructions' => __('statamic::messages.collections_default_publish_state_instructions'),
                    ],
                    'template' => [
                        'type' => 'template',
                        'instructions' => __('statamic::messages.collection_configure_template_instructions'),
                    ],
                    'layout' => [
                        'type' => 'template',
                        'instructions' => __('statamic::messages.collection_configure_layout_instructions'),
                    ],
                ]
            ],
            'routing' => [
                'display' => 'Routing & URLs',
                'fields' => [
                    'route' => [
                        'type' => 'text',
                        'instructions' => __('statamic::messages.collections_route_instructions'),
                    ],
                    'amp' => [
                        'type' => 'toggle',
                        'display' => __('Enable AMP'),
                        'instructions' => __('Enable Accelerated Mobile Pages (AMP). Automatically adds routes and URL for entries in this collection. Learn more in the [documentation](https://statamic.dev/amp).'),
                    ],
                ]
            ]
        ]);
    }
}
