<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Statamic\Facades\Str;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Facades\Scope;
use Statamic\CP\Column;
use Statamic\Facades\Action;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Structure;
use Illuminate\Http\Request;
use Statamic\Facades\Collection;
use Statamic\Fields\Validation;
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
                'blueprints' => $collection->entryBlueprints()->map(function ($blueprint) {
                    return [
                        'handle' => $blueprint->handle(),
                        'title' => $blueprint->title(),
                        'edit_url' => $blueprint->editUrl()
                    ];
                }),
                'entries_url' => cp_route('collections.show', $collection->handle()),
                'deleteable' => User::current()->can('delete', $collection)
            ];
        })->values();

        return view('statamic::collections.index', [
            'collections' => $collections,
            'columns' => [
                Column::make('title'),
                Column::make('blueprints')->fieldtype('relationship'),
                Column::make('entries'),
            ],
        ]);
    }

    public function show($collection)
    {
        $this->authorize('view', $collection, 'You are not authorized to view any collections.');

        $blueprints = $collection->entryBlueprints()->map(function ($blueprint) {
            return [
                'handle' => $blueprint->handle(),
                'title' => $blueprint->title(),
            ];
        });

        return view('statamic::collections.show', [
            'collection' => $collection,
            'blueprints' => $blueprints,
            'site' => Site::selected(),
            'filters' => Scope::filters('entries', $context = [
                'collection' => $collection->handle(),
                'blueprints' => $blueprints->pluck('handle')->all(),
            ]),
        ]);
    }

    public function create()
    {
        $this->authorize('create', CollectionContract::class, 'You are not authorized to create collections.');

        return view('statamic::collections.create');
    }

    public function edit($collection)
    {
        $this->authorize('edit', $collection, 'You are not authorized to edit collections.');

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
            'handle' => 'nullable|alpha_dash',
            'template' => 'nullable',
            'layout' => 'nullable',
            'blueprints' => 'array',
            'route' => 'required_with:structure',
            'orderable' => 'boolean',
            'dated' => 'boolean',
            'dateBehavior' => 'nullable',
            'sortDirection' => 'in:asc,desc',
            'amp' => 'boolean',
            'structure' => 'nullable',
            'mount' => 'nullable',
            'taxonomies' => 'array',
        ]);

        $data['structure'] = $this->ensureStructureExists($data['structure'] ?? null);

        $handle = $request->handle ?? snake_case($request->title);

        $collection = $this->updateCollection(Collection::make($handle), $data);

        switch ($data['dateBehavior']) {
            case 'articles':
                $collection
                    ->pastDateBehavior('public')
                    ->futureDateBehavior('private');
                break;

            case 'events':
                $collection
                    ->pastDateBehavior('public')
                    ->futureDateBehavior('private');
                break;
        }

        $collection->save();

        session()->flash('success', __('Collection created'));

        return [
            'redirect' => $collection->hasStructure()
                ? $collection->structure()->showUrl()
                : $collection->showUrl()
        ];
    }

    public function update(Request $request, $collection)
    {
        $this->authorize('update', $collection, 'You are not authorized to edit collections.');

        $validation = (new Validation)->fields(
            $fields = $this->editFormBlueprint()->fields()->addValues($request->all())->process()
        );

        $request->validate($validation->rules());

        $collection = $this->updateCollection($collection, $values = $fields->values());

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
        return Blueprint::makeFromFields([
            'title' => [
                'type' => 'text',
                'validate' => 'required',
                'width' => 50,
            ],
            'handle' => [
                'type' => 'text',
                'validate' => 'required|alpha_dash',
                'width' => 50,
            ],
            'dates' => ['type' => 'section'],
            'dated' => ['type' => 'toggle'],
            'past_date_behavior' => [
                'type' => 'select',
                'display' => __('Past Date Behavior'),
                'instructions' => __('How past dated entries should behave.'),
                'width' => 50,
                'options' => [
                    'public' => 'Public - Always visible.',
                    'unlisted' => 'Unlisted - Hidden from listings, but available at their URLs.',
                    'private' => 'Private - Hidden from listings and trigger 404s when accessed directly.'
                ],
            ],
            'future_date_behavior' => [
                'type' => 'select',
                'display' => __('Future Date Behavior'),
                'instructions' => __('How future dated entries should behave.'),
                'width' => 50,
                'options' => [
                    'public' => 'Public - Always visible.',
                    'unlisted' => 'Unlisted - Hidden from listings, but available at their URLs.',
                    'private' => 'Private - Hidden from listings and trigger 404s when accessed directly.'
                ],
            ],

            'ordering' => ['type' => 'section'],
            'orderable' => [
                'type' => 'toggle',
                'instructions' => __('Whether entries can have a manual order defined. This enables drag and drop reordering.'),
                'width' => 50,
                'if' => ['structure' => 'empty']
            ],
            'sort_direction' => [
                'type' => 'select',
                'instructions' => __('The default sort direction.'),
                'width' => 50,
                'options' => [
                    'asc' => 'Ascending',
                    'desc' => 'Descending'
                ],
                'if' => ['structure' => 'empty']
            ],
            'structure' => [
                'type' => 'structures',
                'max_items' => 1,
                'instructions' => __('Choosing a structure will let your page hierarchy dictate its order and URLs.'),
            ],

            'content_model' => ['type' => 'section'],
            'blueprints' => [
                'type' => 'blueprints',
                'instructions' => __('Entries in this collection may use any of these blueprints.'),
                'validate' => 'min:1',
            ],
            'taxonomies' => [
                'type' => 'taxonomies',
                'instructions' => __('Entries in this collection may be related to terms in these taxonomies. Fields will be automatically added to publish forms.'),
            ],
            'template' => [
                'type' => 'text',
                'instructions' => __('The default template, unless otherwise specified.'),
                'width' => 50
            ],
            'layout' => [
                'type' => 'text',
                'instructions' => __('The default layout, unless otherwise specified.'),
                'width' => 50
            ],
            'default_status' => [
                'type' => 'radio',
                'instructions' => __('The default publish status when creating new entries.'),
                'width' => 100,
                 'options' => [
                    'published' => 'Published',
                    'draft' => 'Draft'
                 ]
            ],

            'routing' => ['type' => 'section'],
            'route' => [
                'type' => 'text',
                'instructions' => __('The route controls the URL pattern all entries in the collection will follow.'),
            ],
            'mount' => [
                'type' => 'relationship',
                'max_items' => 1,
                'instructions' => __('Mounting to an entry will give you shortcuts to add and edit this collection\'s entries from within a Structure. It will also let you use the `mount` variable in your routes, which will be the URL of the entry.'),
            ],
            'amp' => [
                'type' => 'toggle',
                'display' => __('Accelerated Mobile Pages (AMP)'),
                'instructions' => __('Whether AMP pages should be enabled'),
            ],
        ]);
    }
}
