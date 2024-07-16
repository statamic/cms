<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Statamic\Facades\Action;
use Statamic\Facades\Entry;
use Statamic\Http\Controllers\CP\ActionController;
use Statamic\Http\Resources\CP\Entries\Entry as EntryResource;

class EntryActionController extends ActionController
{
    use ExtractsFromEntryFields;

    protected function getSelectedItems($items, $context)
    {
        return $items->map(function ($item) {
            return Entry::find($item);
        });
    }

    protected function getItemData($entry, $context): array
    {
        $entry = $entry->fresh();

        $blueprint = $entry->blueprint();

        [$values] = $this->extractFromFields($entry, $blueprint);

        return array_merge((new EntryResource($entry))->resolve()['data'], [
            'values' => $values,
            'itemActions' => Action::for($entry, $context),
        ]);
    }
}
