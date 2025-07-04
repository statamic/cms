<?php

namespace Statamic\Http\Controllers\CP\Addons;

use Illuminate\Http\Request;
use Statamic\CP\PublishForm;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Addon;
use Statamic\Http\Controllers\CP\CpController;

class AddonSettingsController extends CpController
{
    public function __construct()
    {
        $this->middleware(\Illuminate\Auth\Middleware\Authorize::class.':configure addons');
    }

    public function edit(Request $request, $addon)
    {
        /** @var \Statamic\Extend\Addon $addon */
        $addon = Addon::all()->first(fn ($a) => $a->slug() === $addon);

        if (! $addon || ! $addon->hasSettings()) {
            throw new NotFoundHttpException;
        }

        return PublishForm::make($addon->settingsBlueprint())
            ->asConfig()
            ->icon('cog')
            ->title($addon->name())
            ->values($addon->settings()->values()->all())
            ->submittingTo(cp_route('addons.settings.update', $addon->slug()));
    }

    public function update(Request $request, $addon)
    {
        /** @var \Statamic\Extend\Addon $addon */
        $addon = Addon::all()->first(fn ($a) => $a->slug() === $addon);

        if (! $addon || ! $addon->hasSettings()) {
            throw new NotFoundHttpException;
        }

        $values = PublishForm::make($addon->settingsBlueprint())->submit($request->all());

        $saved = $addon->settings()->merge($values)->save();

        return ['saved' => $saved];
    }
}
