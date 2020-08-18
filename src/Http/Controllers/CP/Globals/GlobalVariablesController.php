<?php

namespace Statamic\Http\Controllers\CP\Globals;

use Illuminate\Http\Request;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;

class GlobalVariablesController extends CpController
{
    public function edit(Request $request, $id)
    {
        $site = $request->site ?? Site::selected()->handle();

        if (! $set = GlobalSet::find($id)) {
            return $this->pageNotFound();
        }

        if (! $variables = $set->in($site)) {
            return abort(404);
        }

        $this->authorize('edit', $variables);

        $blueprint = $variables->blueprint();

        [$values, $meta] = $this->extractFromFields($variables, $blueprint);

        if ($hasOrigin = $variables->hasOrigin()) {
            [$originValues, $originMeta] = $this->extractFromFields($variables->origin(), $blueprint);
        }

        $user = User::fromUser($request->user());

        $viewData = [
            'reference' => $variables->reference(),
            'editing' => true,
            'actions' => [
                'save' => $variables->updateUrl(),
                'editBlueprint' => cp_route('globals.blueprint.edit', $set->handle()),
            ],
            'values' => $values,
            'meta' => $meta,
            'blueprint' => $blueprint->toPublishArray(),
            'locale' => $variables->locale(),
            'localizedFields' => $variables->data()->keys()->all(),
            'isRoot' => $variables->isRoot(),
            'hasOrigin' => $hasOrigin,
            'originValues' => $originValues ?? null,
            'originMeta' => $originMeta ?? null,
            'localizations' => $variables->globalSet()->localizations()->map(function ($localized) use ($variables) {
                return [
                    'handle' => $localized->locale(),
                    'name' => $localized->site()->name(),
                    'active' => $localized->locale() === $variables->locale(),
                    'origin' => ! $localized->hasOrigin(),
                    'url' => $localized->editUrl(),
                ];
            })->values()->all(),
            'canEdit' => $user->can('edit', $variables),
            'canConfigure' => $user->can('configure', $variables),
            'canDelete' => $user->can('delete', $variables),
        ];

        if ($request->wantsJson()) {
            return $viewData;
        }

        if ($request->has('created')) {
            session()->now('success', __('Global Set created'));
        }

        return view('statamic::globals.edit', array_merge($viewData, [
            'set' => $set,
            'variables' => $variables,
        ]));
    }

    public function update(Request $request, $handle)
    {
        $site = $request->site ?? Site::selected()->handle();

        if (! $set = GlobalSet::findByHandle($handle)) {
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

    protected function extractFromFields($set, $blueprint)
    {
        $fields = $blueprint
            ->fields()
            ->addValues($set->values()->all())
            ->preProcess();

        return [$fields->values()->all(), $fields->meta()->all()];
    }
}
