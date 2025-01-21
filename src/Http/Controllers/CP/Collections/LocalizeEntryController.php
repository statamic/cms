<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Illuminate\Http\Request;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;

class LocalizeEntryController extends CpController
{
    public function __invoke(Request $request, $collection, $entry)
    {
        $this->authorize('edit', $entry);

        $request->validate(['site' => 'required']);

        $localized = $entry->makeLocalization($site = $request->site);

        $localized->store(['user' => User::fromUser($request->user())]);

        return [
            'handle' => $site,
            'url' => $localized->editUrl(),
        ];
    }
}
