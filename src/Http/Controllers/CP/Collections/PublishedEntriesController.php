<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Illuminate\Http\Request;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Resources\CP\Entries\Entry as EntryResource;

class PublishedEntriesController extends CpController
{
    public function store(Request $request, $collection, $entry)
    {
        $this->authorize('publish', $entry);

        $entry = $entry->publish([
            'message' => $request->message,
            'user' => User::fromUser($request->user()),
        ]);

        $blueprint = $entry->blueprint();

        [$values] = $this->extractFromFields($entry, $blueprint);

        return [
            'data' => array_merge((new EntryResource($entry->fresh()))->resolve()['data'], [
                'values' => $values,
            ]),
        ];
    }

    public function destroy(Request $request, $collection, $entry)
    {
        $this->authorize('publish', $entry);

        $entry = $entry->unpublish([
            'message' => $request->message,
            'user' => User::fromUser($request->user()),
        ]);

        $blueprint = $entry->blueprint();

        [$values] = $this->extractFromFields($entry, $blueprint);

        return [
            'data' => array_merge((new EntryResource($entry->fresh()))->resolve()['data'], [
                'values' => $values,
            ]),
        ];
    }

    protected function extractFromFields($entry, $blueprint)
    {
        // The values should only be data merged with the origin data.
        // We don't want injected collection values, which $entry->values() would have given us.
        $values = collect();
        $target = $entry;
        while ($target) {
            $values = $target->data()->merge($target->computedData())->merge($values);
            $target = $target->origin();
        }
        $values = $values->all();

        if ($entry->hasStructure()) {
            $values['parent'] = array_filter([optional($entry->parent())->id()]);

            if ($entry->revisionsEnabled() && $entry->has('parent')) {
                $values['parent'] = [$entry->get('parent')];
            }
        }

        if ($entry->collection()->dated()) {
            $datetime = substr($entry->date()->toDateTimeString(), 0, 19);
            $datetime = ($entry->hasTime()) ? $datetime : substr($datetime, 0, 10);
            $values['date'] = $datetime;
        }

        $fields = $blueprint
            ->fields()
            ->addValues($values)
            ->preProcess();

        $values = $fields->values()->merge([
            'title' => $entry->value('title'),
            'slug' => $entry->slug(),
            'published' => $entry->published(),
        ]);

        return [$values->all(), $fields->meta()];
    }
}
