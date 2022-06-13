<?php

namespace Statamic\Http\Controllers\CP\Globals;

use Illuminate\Http\Request;
use Statamic\Contracts\Globals\GlobalSet as GlobalSetContract;
use Statamic\Facades\Blueprint;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Support\Arr;
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
                'edit_url' => $localized ? $localized->editUrl() : $set->editUrl(),
                'delete_url' => $set->deleteUrl(),
            ];
        })->filter()->values();

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
                    'enabled' => $enabled = $set->existsIn($site->handle()),
                    'origin' => $enabled ? optional($set->in($site->handle())->origin())->locale() : null,
                ];
            })->values(),
        ];

        $fields = ($blueprint = $this->editFormBlueprint($set))
            ->fields()
            ->addValues($values)
            ->preProcess();

        return view('statamic::globals.configure', [
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
            'set' => $set,
            'breadcrumb' => $this->breadcrumb($set),
        ]);
    }

    private function breadcrumb(GlobalSetContract $set)
    {
        if ($localized = $set->inSelectedSite()) {
            return [
                'title' => $localized->title(),
                'url' => $localized->editUrl(),
            ];
        }

        return [
            'title' => __('Globals'),
            'url' => cp_route('globals.index'),
        ];
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

        if (Site::hasMultiple()) {
            $sites = collect(Arr::get($values, 'sites'));

            foreach ($sites->filter->enabled as $site) {
                $vars = $set->in($site['handle']) ?? $set->makeLocalization($site['handle']);
                $vars->origin($site['origin']);
                $set->addLocalization($vars);
            }

            foreach ($sites->reject->enabled as $site) {
                if ($set->existsIn($site['handle'])) {
                    $set->removeLocalization($set->in($site['handle']));
                }
            }
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
            'handle' => 'nullable|alpha_dash',
        ]);

        $handle = $data['handle'] ?? Str::snake($data['title']);

        $global = GlobalSet::make($handle)->title($data['title']);

        $global->addLocalization($global->makeLocalization(Site::default()->handle()));

        $global->save();

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

        if (Site::hasMultiple()) {
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

        return Blueprint::makeFromSections($fields);
    }
}
