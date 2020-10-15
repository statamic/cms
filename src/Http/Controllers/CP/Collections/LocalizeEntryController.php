<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Illuminate\Http\Request;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;

class LocalizeEntryController extends CpController
{
    public function __invoke(Request $request, $collection, $entry)
    {
        $request->validate(['site' => 'required']);

        $localized = $entry->makeLocalization($site = $request->site);

        $this->addToStructure($collection, $entry, $localized);

        $localized->store(['user' => User::fromUser($request->user())]);

        return [
            'handle' => $site,
            'url' => $localized->editUrl(),
        ];
    }

    private function addToStructure($collection, $entry, $localized)
    {
        // If it's orderable (linear - a max depth of 1) then don't add it.
        if ($collection->orderable()) {
            return;
        }

        // Collection not structured? Don't add it.
        if (! $structure = $collection->structure()) {
            return;
        }

        $tree = $structure->in($localized->locale());
        $parent = optional($entry->parent())->in($localized->locale());

        $localized->afterSave(function ($localized) use ($parent, $tree) {
            if (! $parent || $parent->isRoot()) {
                $tree->append($localized);
            } else {
                $tree->appendTo($parent->id(), $localized);
            }

            $tree->save();
        });
    }
}
