<?php

namespace Statamic\Http\Controllers\CP\Collections;

trait ExtractsFromEntryFields
{
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

            if ($entry->revisionsEnabled() && $parent = $entry->get('parent')) {
                $values['parent'] = [$parent];
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

        $extraValues = [
            'depth' => $entry->page()?->depth(),
        ];

        return [$values->all(), $fields->meta(), $extraValues];
    }
}
