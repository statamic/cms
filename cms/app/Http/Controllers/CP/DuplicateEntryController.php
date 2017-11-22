<?php

namespace Statamic\Http\Controllers;

use Statamic\API\Entry;
use Illuminate\Http\Request;

class DuplicateEntryController extends CpController
{
    public function store($collection, Request $request)
    {
        $this->authorize("collections:{$collection}:create");

        if (! $entry = Entry::find($request->id)) {
            throw new \Exception('Entry does not exist.');
        }

        $data = $entry->data();
        unset($data['id']);

        $slug = $this->getDuplicateSlug($entry->slug(), $collection);

        $duplicateEntry = Entry::create($slug)
            ->collection($collection)
            ->order($entry->order())
            ->published(false)
            ->ensureId()
            ->get();

        $id = $duplicateEntry->id();

        foreach ($entry->locales() as $locale) {
            $localized = $entry->dataForLocale($locale);
            $localized['id'] = $id;

            $duplicateEntry->dataForLocale($locale, $localized);
        }

        $duplicateEntry->save();

        $this->success(t('entry_created'));

        return ['redirect' => $duplicateEntry->editUrl()];
    }

    /**
     * Get the slug for the duplicate entry.
     *
     * @param  string  $slug        The slug to build a new slug from.
     * @param  string  $collection  The collection handle.
     * @param  integer $attempt     The current attempt at building a slug since this used recursively.
     * @return string
     */
    private function getDuplicateSlug($slug, $collection, $attempt = 1)
    {
        // If it's not the first attempt, we'll erase the previously suffixed number and dash.
        if ($attempt > 1) {
            $slug = substr($slug, 0, -strlen($attempt-1) - 1);
        }

        $slug .= '-' . $attempt;

        // If the slug we've just built already exists, we'll try again, recursively.
        if (Entry::slugExists($slug, $collection)) {
            $slug = $this->getDuplicateSlug($slug, $collection, $attempt + 1);
        }

        return $slug;
    }
}
