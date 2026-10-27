<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Statamic\Facades\Entry;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;

class LocalizeEntryController extends CpController
{
    public function __invoke(Request $request, $collection, $entry)
    {
        $this->authorize('edit', $entry);

        $request->validate(['site' => 'required']);

        $site = $request->site;

        // The lock stops concurrent requests from both finding nothing and
        // both creating a localization.
        return Cache::lock("localize-entry:{$entry->id()}:{$site}", 10)->block(5, function () use ($request, $entry, $site) {
            $existing = Entry::query()
                ->where('origin', $entry->id())
                ->where('site', $site)
                ->first();

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
