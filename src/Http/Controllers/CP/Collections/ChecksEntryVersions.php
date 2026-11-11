<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Statamic\Facades\Entry;
use Statamic\Support\Arr;

use function Statamic\trans as __;

/**
 * The publish form saves the whole entry. To keep a form that was opened before
 * someone else saved from silently overwriting their changes, the form sends back
 * the version it was loaded with, and outdated saves are rejected.
 */
trait ChecksEntryVersions
{
    protected function entryVersion($entry): string
    {
        // A query instead of find() so database drivers rebuild the entry from storage
        // rather than returning the object cached when it was saved.
        $entry = Entry::query()->where('id', $entry->id())->first();

        // Computed values are excluded because some drivers persist them. The last
        // modified keys change on every save without changing content.
        $data = Arr::except($entry->fileData(), [...$entry->computedKeys()->all(), 'updated_at', 'updated_by']);

        return sha1(json_encode($this->sortKeysForEntryVersion([
            'slug' => $entry->slug(),
            'date' => $entry->collection()->dated() && (! $entry->hasOrigin() || $entry->hasExplicitDate()) ? $entry->date()->timestamp : null,
            'data' => $data,
            'working_copy' => $entry->revisionsEnabled() ? $entry->workingCopy()?->attributes() : null,
        ]), JSON_THROW_ON_ERROR));
    }

    protected function ensureEntryVersionIsCurrent(Request $request, $entry): void
    {
        // Requests without a version, e.g. from custom forms, are not checked.
        if (! $request->has('_version') || $request->input('_version') === $version = $this->entryVersion($entry)) {
            return;
        }

        $workingCopy = $entry->revisionsEnabled() ? $entry->workingCopy() : null;
        $user = $workingCopy ? $workingCopy->user() : $entry->lastModifiedBy();

        // The current version lets the editor deliberately save over the changes.
        throw new HttpResponseException(response()->json([
            'message' => $user
                ? __('statamic::messages.entry_version_conflict_by_user', ['user' => $user->name()])
                : __('statamic::messages.entry_version_conflict'),
            'version' => $version,
        ], 409));
    }

    /**
     * Storage drivers may return keys in a different order, e.g. JSON columns.
     */
    private function sortKeysForEntryVersion($value)
    {
        if (! is_array($value)) {
            return $value;
        }

        if (! array_is_list($value)) {
            ksort($value);
        }

        return array_map(fn ($item) => $this->sortKeysForEntryVersion($item), $value);
    }
}
