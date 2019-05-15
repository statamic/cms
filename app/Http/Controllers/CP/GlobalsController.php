<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\Site;
use Statamic\API\Helper;
use Statamic\API\GlobalSet;
use Illuminate\Http\Request;
use Statamic\Fields\Validation;
use Statamic\Events\Data\PublishBlueprintFound;
use Statamic\Contracts\Data\Globals\GlobalSet as GlobalSetContract;

class GlobalsController extends CpController
{
    public function index()
    {
        $globals = GlobalSet::all()->filter(function ($set) {
            return user()->can('view', $set);
        })->tap(function ($globals) {
            $this->authorizeIf($globals->isEmpty(), 'create', GlobalSetContract::class);
        })->map(function ($set) {
            $localized = $set->in(Site::selected()->handle());

            return [
                'id' => $set->id(),
                'handle' => $set->handle(),
                'title' => $set->title(),
                'deleteable' => user()->can('delete', $set),
                'edit_url' => $localized->editUrl(),
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

        $fields = $blueprint
            ->fields()
            ->addValues($set->values())
            ->preProcess();

        $values = $fields->values();

        $viewData = [
            'reference' => $set->reference(),
            'editing' => true,
            'actions' => [
                'save' => $set->updateUrl(),
            ],
            'values' => $values,
            'meta' => $fields->meta(),
            'blueprint' => $blueprint->toPublishArray(),
            'readOnly' => $request->user()->cant('edit', $set),
            'locale' => $set->locale(),
            'localizedFields' => array_keys($set->data()),
            'hasOrigin' => $hasOrigin = $set->hasOrigin(),
            'originValues' => $hasOrigin ? $set->origin()->data() : [],
            'localizations' => $set->globalSet()->sites()->map(function ($handle) use ($set) {
                $localized = $set->globalSet()->in($handle);
                $exists = $localized !== null;
                return [
                    'handle' => $handle,
                    'name' => Site::get($handle)->name(),
                    'active' => $handle === $set->locale(),
                    'exists' => $exists,
                    'origin' => $exists ? !$localized->hasOrigin() : null,
                    // 'published' => $exists ? $localized->published() : false,
                    'url' => $exists ? $localized->editUrl() : null,
                ];
            })->all()
        ];

        if ($request->wantsJson()) {
            return $viewData;
        }

        if ($request->has('created')) {
            session()->now('success', __('Globals created'));
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

        $fields = $set->blueprint()->fields()->addValues($request->all())->process();

        $validation = (new Validation)->fields($fields);

        $request->validate($validation->rules());

        $values = $fields->values();

        if ($set->hasOrigin()) {
            $values = array_only($values, $request->input('_localized'));
        }

        $set->data($values);

        $set->save();

        return $set->toArray();
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

        $global = GlobalSet::make()
            ->handle($handle)
            ->title($data['title'])
            ->blueprint($data['blueprint'] ?? null)
            ->sites([
                $site = Site::default()->handle() // TODO: site picker
            ])->in($site, function () {
                //
            })->save();

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
}
