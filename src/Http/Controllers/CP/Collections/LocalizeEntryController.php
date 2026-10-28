<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;

class LocalizeEntryController extends CpController
{
    public function __invoke(Request $request, $collection, $entry)
    {
        $this->authorize('edit', $entry);

        $request->validate(['site' => 'required']);

        $site = $request->site;

        // The lock is keyed on the root entry, so concurrent requests from any
        // localization in the origin chain wait for each other.
        return Cache::lock("localize-entry:{$entry->root()->id()}:{$site}", 10)->block(5, function () use ($request, $entry, $site) {
            // Look through the whole origin chain, since the origin of a
            // localization can be any existing localization.
            $existing = $entry->in($site);

            if ($existing) {
                return [
                    'handle' => $site,
                    'url' => $existing->editUrl(),
                ];
            }

            $localized = $entry->makeLocalization($site);

            $localized->store(['user' => User::fromUser($request->user())]);

            return [
                'handle' => $site,
                'url' => $localized->editUrl(),
            ];
        });
    }
}
