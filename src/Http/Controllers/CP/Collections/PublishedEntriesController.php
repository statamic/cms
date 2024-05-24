<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Illuminate\Http\Request;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Resources\CP\Entries\Entry as EntryResource;

class PublishedEntriesController extends CpController
{
    use ExtractsFromEntryFields;

    public function store(Request $request, $collection, $entry)
    {
        $this->authorize('publish', $entry);

        return $this->performAction($entry, 'publish', [
            'message' => $request->message,
            'user' => User::fromUser($request->user()),
        ]);
    }

    public function destroy(Request $request, $collection, $entry)
    {
        $this->authorize('publish', $entry);

        return $this->performAction($entry, 'unpublish', [
            'message' => $request->message,
            'user' => User::fromUser($request->user()),
        ]);
    }

    protected function performAction($entry, string $action, array $options)
    {
        if (! method_exists($entry, $action)) {
            return;
        }

        $entrySaved = $entry->$action($options);

        if ($entrySaved) {
            $entry = $entrySaved;
            $entrySaved = true;
        }

        [$values] = $this->extractFromFields($entry, $entry->blueprint());
        $resource = (new EntryResource($entry->fresh()))->additional(['saved' => $entrySaved])->resolve();

        return [
            'data' => array_merge($resource['data'], [
                'values' => $values,
            ]),
        ];
    }
}
