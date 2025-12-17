<?php

namespace Statamic\Http\Controllers\CP\Navigation;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Statamic\Contracts\Structures\Nav as NavContract;
use Statamic\CP\Column;
use Statamic\CP\PublishForm;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Action;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Nav;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Rules\Handle;
use Statamic\Support\Arr;

class NavigationController extends CpController
{
    public function index()
    {
        $this->authorize('index', NavContract::class, __('You are not authorized to view navs.'));

        $columns = [
            Column::make('title')->label(__('Title')),
        ];

        $navs = Nav::all()->filter(function ($nav) {
            return User::current()->can('configure navs')
                || ($nav->sites()->contains(Site::selected()->handle()) && User::current()->can('view', $nav));
        })->map(function ($structure) {
            return [
                'id' => $structure->handle(),
                'title' => $structure->title(),
                'show_url' => $structure->showUrl(),
                'edit_url' => $structure->editUrl(),
                'available_in_selected_site' => Site::hasMultiple()
                    ? $structure->sites()->contains(Site::selected()->handle())
                    : true,
            ];
        })->values();

        return Inertia::render('navigation/Index', [
            'navs' => $navs->all(),
            'columns' => $columns,
            'actionUrl' => cp_route('navigation.actions.run'),
            'canCreate' => User::current()->can('create', NavContract::class),
            'createUrl' => cp_route('navigation.create'),
        ]);
    }

    public function edit($nav)
    {
        $nav = Nav::find($nav);

        $this->authorize('configure', $nav, __('You are not authorized to configure navs.'));

        $values = [
            'title' => $nav->title(),
            'handle' => $nav->handle(),
            'collections' => $nav->collections()->map->handle()->all(),
            'root' => $nav->expectsRoot(),
            'sites' => $nav->trees()->keys()->all(),
            'max_depth' => $nav->maxDepth(),
            'select_across_sites' => $nav->canSelectAcrossSites(),
        ];

        return PublishForm::make($this->editFormBlueprint($nav))
            ->title(__('Configure Navigation'))
            ->values($values)
            ->asConfig()
            ->submittingTo($nav->showUrl());
    }

    public function show(Request $request, $nav)
    {
        throw_unless($nav = Nav::find($nav), NotFoundHttpException::class);

        $site = $request->site ?? Site::selected()->handle();

        if (! $nav->existsIn($site)) {
            if ($nav->trees()->isNotEmpty()) {
                return redirect($nav->trees()->first()->showUrl());
            }

            $nav->makeTree($site)->save();
        }

        $this->authorize('view', $nav->in($site), __('You are not authorized to view navs.'));

        return Inertia::render('navigation/Show', [
            'title' => $nav->title(),
            'handle' => $nav->handle(),
            'pagesUrl' => cp_route('navigation.tree.index', $nav->handle()),
            'submitUrl' => cp_route('navigation.tree.update', $nav->handle()),
            'editUrl' => $nav->editUrl(),
            'blueprintUrl' => cp_route('blueprints.navigation.edit', $nav->handle()),
            'site' => $site,
            'sites' => $this->getAuthorizedTreesForNav($nav)->map(function ($tree) {
                return [
                    'handle' => $tree->locale(),
                    'name' => $tree->site()->name(),
                    'url' => $tree->showUrl(),
                ];
            })->values()->all(),
            'collections' => $nav->collections()->map->handle()->all(),
            'initialMaxDepth' => $nav->maxDepth(),
            'expectsRoot' => $nav->expectsRoot(),
            'blueprint' => $nav->blueprint()->toPublishArray(),
            'initialItemActions' => Action::for($nav, ['view' => 'form']),
            'itemActionUrl' => cp_route('navigation.actions.run'),
            'canEdit' => User::current()->can('edit', $nav),
            'canSelectAcrossSites' => $nav->canSelectAcrossSites(),
            'canEditBlueprint' => User::current()->can('configure fields'),
        ]);
    }

    private function getAuthorizedTreesForNav($nav)
    {
        return $nav
            ->trees()
            ->filter(fn ($tree) => User::current()->can('view', Site::get($tree->locale())));
    }

    public function update(Request $request, $nav)
    {
        $nav = Nav::find($nav);

        $this->authorize('update', $nav, __('You are not authorized to configure navs.'));

        $fields = $this->editFormBlueprint($nav)->fields()->addValues($request->all());

        $fields->validate();

        $values = $fields->process()->values()->all();

        $nav
            ->title($values['title'])
            ->expectsRoot($values['root'])
            ->collections($values['collections'])
            ->maxDepth($values['max_depth']);

        $existingSites = $nav->trees()->keys()->all();

        if ($sites = Arr::get($values, 'sites')) {
            foreach ($sites as $site) {
                $tree = $nav->in($site) ?? $nav->makeTree($site);
                $tree->save();
            }

            foreach (array_diff($existingSites, $sites) as $site) {
                $nav->in($site)->delete();
            }

            $nav->canSelectAcrossSites($values['select_across_sites']);
        }

        $nav->save();

        return [
            'title' => $nav->title(),
        ];
    }

    public function create()
    {
        $this->authorize('create', NavContract::class, __('You are not authorized to configure navs.'));

        return Inertia::render('navigation/Create', [
            'submitUrl' => cp_route('navigation.store'),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('store', NavContract::class, __('You are not authorized to create navs.'));

        $values = $request->validate([
            'title' => 'required',
            'handle' => ['required', new Handle],
        ]);

        if (Nav::find($values['handle'])) {
            $error = __('A navigation with that handle already exists.');

            if ($request->wantsJson()) {
                throw new \Exception($error);
            }

            return back()->withInput()->with('error', $error);
        }

        $structure = Nav::make()
            ->title($values['title'])
            ->handle($values['handle']);

        $structure->makeTree(Site::default()->handle())->save();

        $structure->save();

        return ['redirect' => $structure->showUrl()];
    }

    public function editFormBlueprint($nav)
    {
        $contents = [
            'name' => [
                'display' => __('Name'),
                'fields' => [
                    'title' => [
                        'display' => __('Title'),
                        'instructions' => __('statamic::messages.navigation_configure_title_instructions'),
                        'type' => 'text',
                        'validate' => 'required',
                    ],
                ],
            ],
            'options' => [
                'display' => __('Options'),
                'fields' => [
                    'blueprint' => [
                        'display' => __('Blueprint'),
                        'instructions' => __('statamic::messages.navigation_configure_blueprint_instructions'),
                        'type' => 'blueprints',
                        'options' => [
                            [
                                'handle' => 'default',
                                'title' => __('Edit Blueprint'),
                                'edit_url' => cp_route('blueprints.navigation.edit', $nav->handle()),
                            ],
                        ],
                    ],
                    'collections' => [
                        'display' => __('Collections'),
                        'instructions' => __('statamic::messages.navigation_configure_collections_instructions'),
                        'type' => 'collections',
                        'mode' => 'select',
                    ],
                    'root' => [
                        'display' => __('Expect a root page'),
                        'instructions' => __('statamic::messages.expect_root_instructions'),
                        'type' => 'toggle',
                    ],
                    'max_depth' => [
                        'display' => __('Max Depth'),
                        'instructions' => __('statamic::messages.max_depth_instructions'),
                        'type' => 'integer',
                        'validate' => 'min:0',
                    ],
                ],
            ],
        ];

        if (Site::multiEnabled()) {
            $contents['options']['fields']['sites'] = [
                'display' => __('Sites'),
                'type' => 'sites',
                'mode' => 'select',
                'required' => true,
            ];

            $contents['options']['fields']['select_across_sites'] = [
                'display' => __('Select Across Sites'),
                'instructions' => __('statamic::messages.navigation_configure_select_across_sites'),
                'type' => 'toggle',
            ];
        }

        return Blueprint::make()->setContents(collect([
            'tabs' => [
                'main' => [
                    'sections' => collect($contents)->map(function ($section) {
                        return [
                            'display' => $section['display'],
                            'fields' => collect($section['fields'])->map(function ($field, $handle) {
                                return [
                                    'handle' => $handle,
                                    'field' => $field,
                                ];
                            })->values()->all(),
                        ];
                    })->values()->all(),
                ],
            ],
        ])->all());
    }
}
