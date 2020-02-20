<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Statamic\Support\Str;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Facades\Scope;
use Statamic\CP\Column;
use Statamic\Facades\Action;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Structure;
use Illuminate\Http\Request;
use Statamic\Facades\Collection;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Contracts\Entries\Collection as CollectionContract;

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

        $values = $collection->toArray();

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

        $collection = $this->updateCollection($collection, $values = $fields->process()->values()->all());

        if ($futureDateBehavior = array_get($values, 'future_date_behavior')) {
            $collection->futureDateBehavior($futureDateBehavior);
        }

        if ($pastDateBehavior = array_get($values, 'past_date_behavior')) {
            $collection->pastDateBehavior($pastDateBehavior);
        }

        $collection->save();

        return $collection->toArray();
    }

    public function destroy($collection)
    {
        $this->authorize('delete', $collection, 'You are not authorized to delete this collection.');

        $collection->delete();
    }

    protected function updateCollection($collection, $data)
    {
        return $collection
            ->title($data['title'])
            ->route($data['route'])
            ->dated($data['dated'])
            ->template($data['template'])
            ->layout($data['layout'])
            ->structure($structure = array_get($data, 'structure'))
            ->orderable($structure ? false : $data['orderable'])
            ->defaultPublishState($data['default_publish_state'])
            ->sortDirection($data['sort_direction'])
            ->ampable($data['amp'])
            ->entryBlueprints($data['blueprints'])
            ->mount($data['mount'] ?? null)
            ->taxonomies($data['taxonomies'] ?? []);
    }

    protected function ensureStructureExists($structure)
    {
        if (! $structure) {
            return;
        }

        if (Structure::findByHandle($structure)) {
            return;
        }

        Structure::make()
            ->handle($handle = Str::snake($structure))
            ->title($structure)
            ->tap(function ($structure) {
                $structure->addTree($structure->makeTree(Site::default()->handle()));
            })->save();

        return $handle;
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
                    ],
                    'handle' => [
                        'type' => 'text',
                        'display' => __('Collection Handle'),
                        'instructions' => __('statamic::messages.collection_configure_handle_instructions'),
                        'validate' => 'required|alpha_dash',
                    ],
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
                    'orderable' => [
                        'type' => 'toggle',
                        'instructions' => __('statamic::messages.collections_orderable_instructions'),
                        'if' => ['structure' => 'empty']
                    ],
                    'sort_direction' => [
                        'type' => 'select',
                        'instructions' => __('statamic::messages.collections_sort_direction_instructions'),
                        'options' => [
                            'asc' => 'Ascending',
                            'desc' => 'Descending'
                        ],
                        'if' => ['structure' => 'empty']
                    ],
                    'structure' => [
                        'type' => 'structures',
                        'max_items' => 1,
                        'instructions' => __('statamic::messages.collections_structure_instructions'),
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
                    ],
                    'taxonomies' => [
                        'type' => 'taxonomies',
                        'instructions' => __('statamic::messages.collections_taxonomies_instructions'),
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
