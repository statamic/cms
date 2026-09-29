<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Illuminate\Http\Request;
use Statamic\Facades\Blink;
use Statamic\Facades\Entry;

use function Statamic\trans as __;

trait MakesEntriesFromSubmittedValues
{
    protected function makeEntryFromSubmittedValues(Request $request, $collection, $site, array $except)
    {
        $blueprint = $collection->entryBlueprint($request->blueprint);

        if (! $blueprint) {
            throw new \Exception(__('A valid blueprint is required.'));
        }

        $entry = Entry::make()
            ->collection($collection)
            ->blueprint($blueprint->handle())
            ->locale($site->handle());

        $values = $this->processedValues($blueprint, $request);

        if ($collection->dated() && $values->has('date')) {
            $entry->date($blueprint->field('date')->fieldtype()->augment($values->pull('date')));
        }

        return $entry->data($values->except($except));
    }

    protected function mergeSubmittedValuesIntoEntry(Request $request, $collection, $entry, array $except)
    {
        $entry = $entry->fromWorkingCopy();

        if ($handle = $request->blueprint) {
            Blink::forget("entry-{$entry->id()}-blueprint");
            $entry->blueprint($handle);
        }

        $blueprint = $entry->blueprint();
        $values = $this->processedValues($blueprint, $request);

        if ($collection->dated() && $values->has('date')) {
            $entry->date($blueprint->field('date')->fieldtype()->augment($values->pull('date')));
        }

        return $entry->merge($values->except($except));
    }

    private function processedValues($blueprint, $request)
    {
        $values = $request->input('values', []);

        // Only the fields the format references get submitted, and processing them
        // fills in the rest, which would wipe out what the entry already has.
        return $blueprint
            ->fields()
            ->addValues($values)
            ->process()
            ->values()
            ->only(array_keys($values));
    }
}
