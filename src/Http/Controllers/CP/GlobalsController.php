<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\Facades\Site;
use Statamic\Facades\Helper;
use Statamic\Facades\GlobalSet;
use Illuminate\Http\Request;
use Statamic\Facades\User;
use Statamic\Events\Data\PublishBlueprintFound;
use Statamic\Contracts\Globals\GlobalSet as GlobalSetContract;

class GlobalsController extends CpController
{
    public function index()
    {
        $globals = GlobalSet::all()->filter(function ($set) {
            return User::current()->can('view', $set);
        })->tap(function ($globals) {
            $this->authorizeIf($globals->isEmpty(), 'create', GlobalSetContract::class);
        })->map(function ($set) {
            $localized = $set->in(Site::selected()->handle());

            return [
                'id' => $set->id(),
                'handle' => $set->handle(),
                'title' => $set->title(),
                'deleteable' => User::current()->can('delete', $set),
                'edit_url' => $localized->editUrl(),
                'delete_url' => $set->deleteUrl(),
            ];
        })->values();

        return view('statamic::globals.index', [
            'globals' => $globals
        ]);
    }

    public function edit(Request $request, $id)
    {
        $site = $request->site ?? Site::selected()->handle();

        if (! $set = GlobalSet::find($id)) {
            return $this->pageNotFound();
        }

        if (! $set = $set->in($site)) {
            return abort(404);
        }

        $this->authorize('edit', $set);

        $blueprint = $set->blueprint();

        event(new PublishBlueprintFound($blueprint, 'globals', $set));

        [$values, $meta] = $this->extractFromFields($set, $blueprint);

        if ($hasOrigin = $set->hasOrigin()) {
            [$originValues, $originMeta] = $this->extractFromFields($set->origin(), $blueprint);
        }

        $user = User::fromUser($request->user());

        $viewData = [
            'reference' => $set->reference(),
            'editing' => true,
            'actions' => [
                'save' => $set->updateUrl(),
            ],
            'values' => $values,
            'meta' => $meta,
            'blueprint' => $blueprint->toPublishArray(),
            'locale' => $set->locale(),
            'localizedFields' => $set->data()->keys()->all(),
            'hasOrigin' => $hasOrigin,
            'originValues' => $originValues ?? null,
            'originMeta' => $originMeta ?? null,
            'localizations' => $set->globalSet()->sites()->map(function ($handle) use ($set) {
                $localized = $set->globalSet()->in($handle);
                $exists = $localized !== null;
                return [
                    'handle' => $handle,
                    'name' => Site::get($handle)->name(),
                    'active' => $handle === $set->locale(),
                    'exists' => $exists,
                    'origin' => $exists ? !$localized->hasOrigin() : null,
                    'url' => $exists ? $localized->editUrl() : null,
                ];
            })->all(),
            'canEdit' => $user->can('edit', $set),
            'canConfigure' => $user->can('configure', $set),
            'canDelete' => $user->can('delete', $set),
        ];

        if ($request->wantsJson()) {
            return $viewData;
        }

        if ($request->has('created')) {
            session()->now('success', __('Global Set created'));
        }

        return view('statamic::globals.edit', array_merge($viewData, [
            'set' => $set
        ]));
    }

    public function update(Request $request, $id, $handle)
    {
        $site = $request->site ?? Site::selected()->handle();

        if (! $set = GlobalSet::find($id)) {
            return $this->pageNotFound();
        }

        if (! $set = $set->in($site)) {
            abort(404);
        }

        $this->authorize('edit', $set);

        $fields = $set->blueprint()->fields()->addValues($request->all());

        $fields->validate();

        $values = $fields->process()->values();

        if ($set->hasOrigin()) {
            $values = $values->only($request->input('_localized'));
        }

        $set->data($values);

        $set->save();

        return response('', 204);
    }

    public function updateMeta(Request $request, $set)
    {
        if (! $set = GlobalSet::find($set)) {
            return $this->pageNotFound();
        }

        $this->authorize('create', $set);

        $request->validate([
            'title' => 'required',
            'handle' => 'nullable|alpha_dash',
            'blueprint' => 'nullable'
        ]);

        $set
            ->title($request->title)
            ->blueprint($request->blueprint)
            ->handle($request->handle ?? snake_case($request->title))
            ->save();

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
            'blueprint' => 'string|nullable'
        ]);

        $handle = $request->handle ?? snake_case($request->title);

        $sites = [Site::default()->handle()]; // TODO: site picker

        $global = GlobalSet::make()
            ->handle($handle)
            ->title($data['title'])
            ->blueprint($data['blueprint'] ?? null)
            ->sites($sites);

        $origin = null;
        foreach ($sites as $i => $site) {
            $variables = $global->makeLocalization($site);
            $variables->origin($origin);
            if ($i === 0) {
                $origin = $variables;
            }
            $global->addLocalization($variables);
        }

        $global->save();

        session()->flash('message', __('Global Set created'));

        return [
            'redirect' => $global->editUrl()
        ];
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

    protected function extractFromFields($set, $blueprint)
    {
        $fields = $blueprint
            ->fields()
            ->addValues($set->values()->all())
            ->preProcess();

        return [$fields->values()->all(), $fields->meta()->all()];
    }
}
