<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Statamic\Facades\Action;
use Statamic\Facades\Entry;
use Statamic\Http\Controllers\CP\ActionController;

class EntryActionController extends ActionController
{
    protected function getSelectedItems($items, $context)
    {
        return $items->map(function ($item) {
            return Entry::find($item);
        });
    }

    protected function getItemData($entry)
    {
        $collection = $entry->collection();

        $blueprint = $entry->blueprint();

        [$values] = $this->extractFromFields($entry, $blueprint);

        return [
            'values' => $values,
            'listActions' => Action::for($entry, ['collection' => $collection->handle(), 'view' => 'publishForm']),
        ];
    }

    protected function extractFromFields($entry, $blueprint)
    {
        // The values should only be data merged with the origin data.
        // We don't want injected collection values, which $entry->values() would have given us.
        $target = $entry;
        $values = $target->data();
        while ($target->hasOrigin()) {
            $target = $target->origin();
            $values = $target->data()->merge($values);
        }
        $values = $values->all();

        if ($entry->hasStructure()) {
            $values['parent'] = array_filter([optional($entry->parent())->id()]);
        }

        if ($entry->collection()->dated()) {
            $datetime = substr($entry->date()->toDateTimeString(), 0, 16);
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
