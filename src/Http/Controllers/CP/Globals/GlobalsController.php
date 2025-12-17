<?php

namespace Statamic\Http\Controllers\CP\Globals;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Statamic\Contracts\Globals\GlobalSet as GlobalSetContract;
use Statamic\CP\Column;
use Statamic\CP\PublishForm;
use Statamic\Facades\Blueprint;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Rules\Handle;
use Statamic\Support\Str;

class GlobalsController extends CpController
{
    public function index()
    {
        $columns = [
            Column::make('title')->label(__('Title')),
            Column::make('handle')->label(__('Handle')),
        ];

        $globals = GlobalSet::all()->filter(function ($set) {
            return User::current()->can('view', $set);
        })->tap(function ($globals) {
            $this->authorizeIf($globals->isEmpty(), 'create', GlobalSetContract::class);
        })->map(function ($set) {
            $localized = $set->inSelectedSite();

            if (! $localized && User::current()->cant('edit', $set)) {
                return null;
            }

            return [
                'id' => $set->id(),
                'handle' => $set->handle(),
                'title' => $set->title(),
                'configurable' => User::current()->can('edit', $set),
                'edit_url' => $localized ? $localized->editUrl() : $set->editUrl(),
                'configure_url' => $set->editUrl(),
            ];
        })->filter()->sortBy('title')->values();

        return Inertia::render('globals/Index', [
            'globals' => $globals,
            'columns' => $columns,
            'actionUrl' => cp_route('globals.actions.run'),
            'createUrl' => cp_route('globals.create'),
            'canCreate' => User::current()->can('create', GlobalSetContract::class),
        ]);
    }

    public function edit($set)
    {
        if (! $set = GlobalSet::find($set)) {
            return $this->pageNotFound();
        }

        $this->authorize('edit', $set, 'You are not authorized to edit this global set.');

        $values = [
            'title' => $set->title(),
            'blueprint' => optional($set->blueprint())->handle(),
            'sites' => Site::all()->map(function ($site) use ($set) {
                return [
                    'name' => $site->name(),
                    'handle' => $site->handle(),
                    'enabled' => $set->sites()->contains($site->handle()),
                    'origin' => $set->origins()->get($site->handle()),
                ];
            })->values(),
        ];

        return PublishForm::make($this->editFormBlueprint($set))
            ->title(__('Configure Global Set'))
            ->values($values)
            ->asConfig()
            ->submittingTo(cp_route('globals.update', $set->handle()));
    }

    public function update(Request $request, $set)
    {
        if (! $set = GlobalSet::find($set)) {
            return $this->pageNotFound();
        }

        $this->authorize('update', $set);

        $fields = $this->editFormBlueprint($set)->fields()->addValues($request->all());

        $fields->validate();

        $values = $fields->process()->values()->all();

        $set
            ->title($values['title'])
            ->blueprint($values['blueprint']);

        if (Site::multiEnabled()) {
            $sites = collect($values['sites'])
                ->filter(fn ($site) => $site['enabled'])
                ->mapWithKeys(fn ($site) => [$site['handle'] => $site['origin']]);

            $set->sites($sites);
        }

        $set->save();

        return response('', 204);
    }

    public function create()
    {
        $this->authorize('create', GlobalSetContract::class);

        return Inertia::render('globals/Create', [
            'submitUrl' => cp_route('globals.store'),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('store', GlobalSetContract::class);

        $data = $request->validate([
            'title' => 'required',
            'handle' => ['nullable', new Handle],
        ]);

        $handle = $data['handle'] ?? Str::snake($data['title']);

        if (GlobalSet::find($handle)) {
            $error = __('A Global Set with that handle already exists.');

            if ($request->wantsJson()) {
                throw new \Exception($error);
            }

            return back()->withInput()->with('error', $error);
        }

        $global = GlobalSet::make($handle)->title($data['title']);
        $global->save();

        $global->in(Site::default()->handle())->save();

        session()->flash('message', __('Global Set created'));

        return ['redirect' => $global->editUrl()];
    }

    protected function editFormBlueprint($set)
    {
        $fields = [
            'name' => [
                'display' => __('Name'),
                'fields' => [
                    'title' => [
                        'type' => 'text',
                        'instructions' => __('statamic::messages.globals_configure_title_instructions'),
                        'validate' => 'required',
                    ],
                ],
            ],
            'content_model' => [
                'display' => __('Content Model'),
                'fields' => [
                    'blueprint' => [
                        'display' => __('Blueprint'),
                        'instructions' => __('statamic::messages.globals_blueprint_instructions'),
                        'type' => 'blueprints',
                        'options' => [
                            [
                                'handle' => 'default',
                                'title' => __('Edit Blueprint'),
                                'edit_url' => cp_route('blueprints.globals.edit', $set->handle()),
                            ],
                        ],
                    ],
                ],
            ],
        ];

        if (Site::multiEnabled()) {
            $fields['sites'] = [
                'display' => __('Sites'),
                'fields' => [
                    'sites' => [
                        'type' => 'global_set_sites',
                        'required' => true,
                    ],
                ],
            ];
        }

        return Blueprint::make()->setContents(collect([
            'tabs' => [
                'main' => [
                    'sections' => collect($fields)->map(function ($section) {
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
