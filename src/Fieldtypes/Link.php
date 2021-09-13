<?php

namespace Statamic\Fieldtypes;

use Facades\Statamic\Routing\ResolveRedirect;
use Statamic\Contracts\Entries\Collection;
use Statamic\Contracts\Entries\Entry;
use Statamic\Facades;
use Statamic\Facades\Blink;
use Statamic\Facades\Site;
use Statamic\Fields\Field;
use Statamic\Fields\Fieldtype;
use Statamic\Support\Str;

class Link extends Fieldtype
{
    protected function configFieldItems(): array
    {
        return [
            'collections' => [
                'display' => __('Collections'),
                'instructions' => __('statamic::fieldtypes.link.config.collections'),
                'type' => 'collections',
                'mode' => 'select',
            ],
        ];
    }

    public function augment($value)
    {
        $redirect = ResolveRedirect::resolve($value, $this->field->parent());

        return $redirect === 404 ? null : $redirect;
    }

    public function preload()
    {
        $value = $this->field->value();

        $selectedEntry = Str::startsWith($value, 'entry::') ? Str::after($value, 'entry::') : null;

        $url = ($value !== '@child' && ! $selectedEntry) ? $value : null;

        $entryFieldtype = $this->nestedEntriesFieldtype($selectedEntry);

        return [
            'initialUrl' => $url,
            'initialSelectedEntries' => $selectedEntry ? [$selectedEntry] : [],
            'initialOption' => $this->initialOption($value, $selectedEntry),
            'showFirstChildOption' => $this->showFirstChildOption(),
            'entry' => [
                'config' => $entryFieldtype->config(),
                'meta' => $entryFieldtype->preload(),
            ],
        ];
    }

    private function initialOption($value, $entry)
    {
        if (! $value) {
            return $this->field->isRequired() ? 'url' : null;
        }

        if ($value === '@child') {
            return 'first-child';
        } elseif ($entry) {
            return 'entry';
        }

        return 'url';
    }

    private function nestedEntriesFieldtype($value): Fieldtype
    {
        $entryField = (new Field('entry', [
            'type' => 'entries',
            'max_items' => 1,
            'create' => false,
        ]));

        $entryField->setValue($value);

        $entryField->setConfig(array_merge(
            $entryField->config(),
            ['collections' => $this->collections()]
        ));

        return $entryField->fieldtype();
    }

    private function collections()
    {
        $collections = $this->config('collections');

        if (empty($collections)) {
            $site = Site::current()->handle();

            $collections = Blink::once('routable-collection-handles-'.$site, function () use ($site) {
                return Facades\Collection::all()->reject(function ($collection) use ($site) {
                    return is_null($collection->route($site));
                })->map->handle()->values()->all();
            });
        }

        return $collections;
    }

    private function showFirstChildOption()
    {
        $parent = $this->field()->parent();

        if ($parent instanceof Entry) {
            $collection = $parent->collection();
        } elseif ($parent instanceof Collection) {
            $collection = $parent;
        } else {
            return false;
        }

        return $collection->hasStructure() && $collection->structure()->maxDepth() !== 1;
    }
}
