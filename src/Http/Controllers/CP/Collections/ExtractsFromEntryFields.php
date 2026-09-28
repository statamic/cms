<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Statamic\Support\Arr;

trait ExtractsFromEntryFields
{
    protected function extractFromFields($entry, $blueprint)
    {
        // The values should only be data merged with the origin data.
        // We don't want injected collection values, which $entry->values() would have given us.
        $values = collect();
        $target = $entry;
        while ($target) {
            $data = $target->isRoot() ? collect(Arr::removeNullValues($target->data()->all())) : $target->data();
            $values = $data->merge($target->computedData())->merge($values);
            $target = $target->origin();
        }
        $values = $values->all();

        if ($entry->hasStructure()) {
            $values['parent'] = array_filter([optional($entry->parent())->id()]);

            if ($entry->revisionsEnabled() && $parent = $entry->get('parent')) {
                $values['parent'] = [$parent];
            }
        }

        if ($entry->collection()->dated()) {
            $datetime = substr($entry->date()->setTimezone(config('app.timezone'))->toDateTimeString(), 0, 19);
            $values['date'] = $datetime;
        }

        $fields = $blueprint
            ->setParent($entry)
            ->fields()
            ->addValues($values)
            ->preProcess();

        $values = $fields->values()->merge([
            'title' => $entry->value('title'),
            'slug' => $entry->slug(),
            'published' => $entry->published(),
        ]);

        $extraValues = [
            'depth' => $entry->page()?->depth(),
            'children' => $entry->page()?->flattenedPages()->pluck('id')->all(),
        ];

        return [$values->all(), $fields->meta(), $extraValues];
    }
}
