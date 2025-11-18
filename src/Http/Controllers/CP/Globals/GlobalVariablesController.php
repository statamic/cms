<?php

namespace Statamic\Http\Controllers\CP\Globals;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Statamic\Facades\Action;
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
            return $this->pageNotFound();
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
                'editBlueprint' => cp_route('blueprints.globals.edit', $set->handle()),
            ],
            'values' => $values,
            'meta' => $meta,
            'blueprint' => $blueprint->toPublishArray(),
            'locale' => $variables->locale(),
            'localizedFields' => $variables->data()->keys()->all(),
            'hasOrigin' => $hasOrigin,
            'originValues' => $originValues ?? null,
            'originMeta' => $originMeta ?? null,
            'localizations' => $this->getAuthorizedLocalizationsForVariables($variables)->map(function ($localized) use ($variables) {
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
            'configureUrl' => $set->editUrl(),
            'canDelete' => $user->can('delete', $variables),
            'itemActions' => Action::for($set, ['view' => 'form']),
            'actionUrl' => cp_route('globals.actions.run'),
        ];

        if ($request->wantsJson()) {
            return $viewData;
        }

        if ($request->has('created')) {
            session()->now('success', __('Global Set created'));
        }

        return Inertia::render('globals/Edit', array_merge($viewData, [
            'globalsUrl' => cp_route('globals.index'),
            'title' => $variables->title(),
            'handle' => $variables->handle(),
            'blueprintHandle' => $variables->blueprint()->handle(),
            'canEditBlueprint' => $viewData['actions']['editBlueprint'] ? User::current()->can('configure fields') : false,
        ]));
    }

    public function update(Request $request, $handle)
    {
        $site = $request->site ?? Site::selected()->handle();

        if (! $set = GlobalSet::findByHandle($handle)) {
            return $this->pageNotFound();
        }

        if (! $set = $set->in($site)) {
            return $this->pageNotFound();
        }

        $this->authorize('edit', $set);

        $fields = $set->blueprint()->fields()->addValues($request->all());

        $fields->validate();

        $values = $fields->process()->values();

        if ($set->hasOrigin()) {
            $values = $values->only($request->input('_localized'));
        }

        $set->data($values);

        $save = $set->save();

        return response()->json([
            'saved' => is_bool($save) ? $save : true,
        ]);
    }

    protected function extractFromFields($set, $blueprint)
    {
        $fields = $blueprint
            ->fields()
            ->addValues($set->values()->all())
            ->preProcess();

        return [$fields->values()->all(), $fields->meta()->all()];
    }

    protected function getAuthorizedLocalizationsForVariables($variables)
    {
        return $variables
            ->globalSet()
            ->localizations()
            ->filter(fn ($set) => User::current()->can('edit', $set));
    }
}
