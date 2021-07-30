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

        $entryField = (new Field('entry', [
            'type' => 'entries',
            'max_items' => 1,
            'create' => false,
        ]));

        if (Str::startsWith($value, 'entry::')) {
            $entryField->setValue(Str::after($value, 'entry::'));
        }

        $entryFieldtype = $entryField->fieldtype();

        $collections = $this->config('collections');

        if (empty($collections)) {
            $site = Site::current()->handle();

            $collections = Blink::once('routable-collection-handles-'.$site, function () use ($site) {
                return Facades\Collection::all()->reject(function ($collection) use ($site) {
                    return is_null($collection->route($site));
                })->map->handle()->values();
            });
        }

        return [
            'showFirstChildOption' => $this->showFirstChildOption(),
            'entry' => [
                'config' => array_merge($entryFieldtype->config(), ['collections' => $collections]),
                'meta' => $entryFieldtype->preload(),
            ],
        ];
    }

    protected function showFirstChildOption()
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
