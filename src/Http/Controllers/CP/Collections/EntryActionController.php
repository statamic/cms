<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Statamic\Facades\Action;
use Statamic\Facades\Entry;
use Statamic\Http\Controllers\CP\ActionController;

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
        $blueprint = $entry->blueprint();

        [$values] = $this->extractFromFields($entry, $blueprint);

        return [
            'title' => $entry->value('title'),
            'permalink' => $entry->absoluteUrl(),
            'values' => array_merge($values, ['id' => $entry->id()]),
            'itemActions' => Action::for($entry, $context),
        ];
    }
}
