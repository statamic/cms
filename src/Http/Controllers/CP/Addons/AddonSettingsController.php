<?php

namespace Statamic\Http\Controllers\CP\Addons;

use Illuminate\Http\Request;
use Statamic\CP\PublishForm;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Addon;
use Statamic\Http\Controllers\CP\CpController;

class AddonSettingsController extends CpController
{
    public function edit(Request $request, $addon)
    {
        /** @var \Statamic\Addons\Addon $addon */
        $addon = Addon::all()->first(fn ($a) => $a->slug() === $addon);

        if (! $addon || ! $addon->hasSettingsBlueprint()) {
            throw new NotFoundHttpException;
        }

        $this->authorize('editSettings', $addon);

        return PublishForm::make($addon->settingsBlueprint())
            ->asConfig()
            ->icon('cog')
            ->title($addon->name())
            ->values($addon->settings()->rawValues())
            ->submittingTo(cp_route('addons.settings.update', $addon->slug()));
    }

    public function update(Request $request, $addon)
    {
        /** @var \Statamic\Addons\Addon $addon */
        $addon = Addon::all()->first(fn ($a) => $a->slug() === $addon);

        if (! $addon || ! $addon->hasSettingsBlueprint()) {
            throw new NotFoundHttpException;
        }

        $this->authorize('editSettings', $addon);

        $values = PublishForm::make($addon->settingsBlueprint())->submit($request->all());

        $saved = $addon->settings()->values($values)->save();

        return ['saved' => $saved];
    }
}
