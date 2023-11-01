<?php

namespace Statamic\Fieldtypes;

use Facades\Statamic\Routing\ResolveRedirect;
use Statamic\Contracts\Assets\Asset as AssetContract;
use Statamic\Contracts\Data\Localization;
use Statamic\Contracts\Entries\Collection;
use Statamic\Contracts\Entries\Entry;
use Statamic\Facades;
use Statamic\Facades\Asset;
use Statamic\Facades\Blink;
use Statamic\Facades\Site;
use Statamic\Fields\ArrayableString;
use Statamic\Fields\Field;
use Statamic\Fields\Fieldtype;
use Statamic\Structures\Page;
use Statamic\Support\Str;

class Link extends Fieldtype
{
    protected $categories = ['relationship'];

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Behavior'),
                'fields' => [
                    'collections' => [
                        'display' => __('Collections'),
                        'instructions' => __('statamic::fieldtypes.link.config.collections'),
                        'type' => 'collections',
                        'mode' => 'select',
                    ],
                    'container' => [
                        'display' => __('Container'),
                        'instructions' => __('statamic::fieldtypes.link.config.container'),
                        'type' => 'asset_container',
                        'mode' => 'select',
                        'max_items' => 1,
                    ],
                ],
            ],
        ];
    }

    public function augment($value)
    {
        if (! $value) {
            return null;
        }

        if (is_null($item = $this->resolve($value))) {
            return null;
        }

        // $redirect = ResolveRedirect::resolve($value, $this->field->parent(), true);

        return new ArrayableLink($item);
    }

    public function preload()
    {
        $value = $this->field->value();

        $showAssetOption = $this->showAssetOption();

        $selectedEntry = $value && Str::startsWith($value, 'entry::') ? Str::after($value, 'entry::') : null;

        $selectedAsset = $value && Str::startsWith($value, 'asset::') ? Str::after($value, 'asset::') : null;

        $url = ($value !== '@child' && ! $selectedEntry && ! $selectedAsset) ? $value : null;

        $entryFieldtype = $this->nestedEntriesFieldtype($selectedEntry);

        $assetFieldtype = $showAssetOption ? $this->nestedAssetsFieldtype($selectedAsset) : null;

        return [
            'initialUrl' => $url,
            'initialSelectedEntries' => $selectedEntry ? [$selectedEntry] : [],
            'initialSelectedAssets' => $selectedAsset ? [$selectedAsset] : [],
            'initialOption' => $this->initialOption($value, $selectedEntry, $selectedAsset),
            'showFirstChildOption' => $this->showFirstChildOption(),
            'showAssetOption' => $showAssetOption,
            'entry' => [
                'config' => $entryFieldtype->config(),
                'meta' => $entryFieldtype->preload(),
            ],
            'asset' => $showAssetOption ? [
                'config' => $assetFieldtype->config(),
                'meta' => $assetFieldtype->preload(),
            ] : null,
        ];
    }

    private function initialOption($value, $entry, $asset)
    {
        if (! $value) {
            return $this->field->isRequired() ? 'url' : null;
        }

        if ($value === '@child') {
            return 'first-child';
        } elseif ($entry) {
            return 'entry';
        } elseif ($asset) {
            return 'asset';
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

    private function nestedAssetsFieldtype($value): Fieldtype
    {
        $assetField = (new Field('entry', [
            'type' => 'assets',
            'max_files' => 1,
            'mode' => 'list',
        ]));

        $assetField->setValue($value);

        $assetField->setConfig(array_merge(
            $assetField->config(),
            ['container' => $this->config('container')]
        ));

        return $assetField->fieldtype();
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

    private function showAssetOption()
    {
        return $this->config('container') !== null;
    }

    private function resolve($value): string|Entry|AssetContract|null
    {
        if ($value === '@child') {
            return $this->firstChildUrl($this->field->parent());
        }

        if (Str::startsWith($value, 'entry::')) {
            return $this->findEntry(
                Str::after($value, 'entry::'),
                $this->field->parent(),
                true
            );
        }

        if (Str::startsWith($value, 'asset::')) {
            return Asset::find(Str::after($value, 'asset::'));
        }

        return $value;
    }

    private function findEntry($id, $parent, $localize)
    {
        if (! ($entry = Facades\Entry::find($id))) {
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

    private function firstChildUrl($parent)
    {
        if (! $parent || ! $parent instanceof Entry) {
            throw new \Exception("Cannot resolve a page's child redirect without providing a page.");
        }

        if (! $parent instanceof Page && $parent instanceof Entry) {
            $parent = $parent->page();
        }

        $children = $parent->isRoot()
            ? $parent->structure()->in($parent->locale())->pages()->all()->slice(1, 1)
            : $parent->pages()->all();

        if ($children->isEmpty()) {
            return 404;
        }

        return $children->first()->url();
    }
}

class ArrayableLink extends ArrayableString
{
    public function value()
    {
        if (is_string($this->value)) {
            return $this->value;
        }

        return $this->value->url();
    }

    public function toArray()
    {
        if (is_string($this->value)) {
            return ['url' => $this->value];
        }

        return $this->value->toAugmentedArray();
    }
}
