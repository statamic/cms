<?php

namespace Statamic\Fieldtypes\Link;

use Statamic\Contracts\Data\Localization;
use Statamic\Facades;
use Statamic\Facades\Blink;
use Statamic\Facades\Site;
use Statamic\Fields\Field;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class EntryLinkType extends LinkType
{
    protected ?string $icon = 'collections';

    public function title(): string
    {
        return __('Entry');
    }

    public function configFieldItems(): array
    {
        return [
            'collections' => [
                'display' => __('Collections'),
                'instructions' => __('statamic::fieldtypes.link.config.collections'),
                'type' => 'collections',
                'mode' => 'select',
                'width' => '50',
            ],
            'select_across_sites' => [
                'display' => __('Select Across Sites'),
                'instructions' => __('statamic::fieldtypes.entries.config.select_across_sites'),
                'type' => 'toggle',
                'width' => '50',
            ],
        ];
    }

    public function resolve(string $id, $parent = null, bool $localize = false): mixed
    {
        if (! $entry = Facades\Entry::find($id)) {
            return null;
        }

        if (! $localize) {
            return $entry;
        }

        $site = $parent instanceof Localization
            ? $parent->locale()
            : Site::current()->handle();

        return $entry->in($site) ?? $entry;
    }

    public function fieldtype(Field $field): array
    {
        return [
            'type' => 'entries',
            'max_items' => 1,
            'create' => false,
            'select_across_sites' => $field->get('select_across_sites', false),
            'collections' => $this->collections($field),
        ];
    }

    private function collections(Field $field): array
    {
        $collections = $field->get('collections');

        if (empty($collections)) {
            $site = Site::current()->handle();

            $collections = Blink::once('routable-collection-handles-'.$site, function () use ($site) {
                return Facades\Collection::all()->reject(function ($collection) use ($site) {
                    return is_null($collection->route($site));
                })->map->handle()->values()->all();
            });
        }

        return Arr::wrap($collections);
    }
}
