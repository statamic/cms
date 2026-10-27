<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\Entry;
use Statamic\Fields\Fieldtype;

/**
 * Prototype: read-only bits of the Settings → AI page that aren't settings, like the "Test connection" button and this month's spending.
 * The `show` config picks which ("connection", "usage", or "prompt" for trying out a saved prompt). All are mocked in the Vue component, and nothing is saved.
 */
class AiStatus extends Fieldtype
{
    protected $selectable = false;
    protected $localizable = false;
    protected $validatable = false;
    protected $defaultable = false;

    // Trying a prompt needs something to try it on: some entries, with their text fields
    public function preload()
    {
        if ($this->config('show') !== 'prompt') {
            return [];
        }

        return [
            'entries' => Entry::query()->get()->map(fn ($entry) => [
                'id' => $entry->id(),
                'title' => $entry->value('title') ?: $entry->slug(),
                'collection' => $entry->collectionHandle(),
                'collection_title' => $entry->collection()->title(),
                'fields' => $entry->blueprint()->fields()->all()
                    ->filter(fn ($field) => in_array($field->type(), ['text', 'textarea', 'markdown']))
                    ->map(fn ($field) => [
                        'handle' => $field->handle(),
                        'display' => $field->display(),
                        'type' => $field->type(),
                        'value' => is_string($value = $entry->value($field->handle())) ? $value : '',
                    ])
                    ->values()
                    ->all(),
            ])
                // The ones with the most text first, as they make the best examples
                ->sortByDesc(fn ($entry) => collect($entry['fields'])->sum(fn ($field) => strlen($field['value'])))
                ->take(50)
                ->values()
                ->all(),
        ];
    }
}
