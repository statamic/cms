<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Illuminate\Http\Request;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Facades\Entry;
use Statamic\Http\Controllers\CP\PreviewController;

class EntryPreviewController extends PreviewController
{
    use ResolvesEntryUri;

    public function create(Request $request, $collection, $site)
    {
        $this->authorize('create', [EntryContract::class, $collection]);

        $blueprint = $collection->entryBlueprint($request->blueprint);

        $values = $blueprint
            ->fields()
            ->addValues($preview = $request->preview)
            ->process()
            ->values()
            ->except(['slug']);

        $entry = Entry::make()
            ->slug($preview['slug'] ?? 'slug')
            ->collection($collection)
            ->blueprint($request->blueprint)
            ->locale($site->handle());

        if ($collection->dated()) {
            $date = $values->pull('date');
            $entry->date(($date ? $blueprint->field('date')?->fieldtype()->augment($date) : null) ?? now());
        }

        $entry->data($values->all());

        // An entry that hasn't been saved isn't in the tree, so it has no URI
        // of its own. Give it the one it will have under its parent page.
        if ($structure = $collection->structure()) {
            $entry->setSupplement('live_preview_uri', $this->entryUri($entry, $structure->in($site->handle()), $request->parent));
        }

        return $this->tokenizeAndReturn($request, $entry);
    }
}
