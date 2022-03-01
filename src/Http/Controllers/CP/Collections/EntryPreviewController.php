<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Illuminate\Http\Request;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Facades\Entry;
use Statamic\Http\Controllers\CP\PreviewController;

class EntryPreviewController extends PreviewController
{
    public function create(Request $request, $collection, $site)
    {
        $this->authorize('create', [EntryContract::class, $collection]);

        $fields = $collection->entryBlueprint($request->blueprint)
            ->fields()
            ->addValues($preview = $request->preview)
            ->process();

        $values = array_except($fields->values()->all(), ['slug']);

        $entry = Entry::make()
            ->slug($preview['slug'] ?? 'slug')
            ->collection($collection)
            ->locale($site->handle())
            ->data($values);

        if ($collection->dated()) {
            $entry->date($preview['date'] ?? now()->format('Y-m-d-Hi'));
        }

        return $this->tokenizeAndReturn($request, $entry);
    }
}
