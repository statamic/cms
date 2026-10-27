<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Illuminate\Http\Request;
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
    }
}
