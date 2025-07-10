<?php

namespace Statamic\Http\Controllers\CP\Globals;

use Illuminate\Http\Request;
use Statamic\Contracts\Globals\GlobalSet as GlobalSetContract;
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
                'deleteable' => User::current()->can('delete', $set),
                'configurable' => User::current()->can('edit', $set),
                'edit_url' => $localized ? $localized->editUrl() : $set->editUrl(),
                'configure_url' => $set->editUrl(),
                'delete_url' => $set->deleteUrl(),
            ];
        })->filter()->values();

        if ($globals->isEmpty()) {
            return view('statamic::globals.empty');
        }

        return view('statamic::globals.index', [
            'globals' => $globals,
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

        return view('statamic::globals.create');
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

    public function destroy($set)
    {
        if (! $set = GlobalSet::find($set)) {
            return $this->pageNotFound();
        }

        $this->authorize('delete', $set);

        $set->delete();

        return response('', 204);
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
                        'type' => 'html',
                        'instructions' => __('statamic::messages.globals_blueprint_instructions'),
                        'html' => ''.
                            '<div class="text-xs">'.
                            '   <a href="'.cp_route('globals.blueprint.edit', $set->handle()).'" class="text-blue">'.__('Edit').'</a>'.
                            '</div>',
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
                        'mode' => 'select',
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
