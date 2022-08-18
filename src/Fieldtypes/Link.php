<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades;
use Statamic\Support\Str;
use Statamic\Facades\Site;
use Statamic\Fields\Field;
use Statamic\Support\Arr;
use Statamic\Facades\Blink;
use Statamic\Fields\Fieldtype;
use Statamic\Facades\Entry;
use Statamic\Query\OrderedQueryBuilder;
use Statamic\Contracts\Entries\Collection;
use Facades\Statamic\Routing\ResolveRedirect;

class Link extends Fieldtype
{
    protected $categories = ['relationship'];

    protected function configFieldItems(): array
    {
        return [
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
        ];
    }

    public function augment($value)
    {
        $redirect = ResolveRedirect::resolve($value, $this->field->parent());

        if (Site::hasMultiple() && Str::startsWith($value, 'entry::')) {
            $site = Site::current()->handle();
            if (($parent = $this->field()->parent()) && $parent instanceof Localization) {
                $site = $parent->locale();
            }

            $selectedEntry = Str::after($value, 'entry::');

            $idForCurrentSite = (new OrderedQueryBuilder(Entry::query(), $selectedEntry))
                ->where('id', $selectedEntry)
                ->get()
                ->map(function ($entry) use ($site) {
                    return optional($entry->in($site))->id();
                })
                ->filter()
                ->first();

            $entryForSite = (new OrderedQueryBuilder(Entry::query(), $idForCurrentSite))
                ->where('id', $idForCurrentSite)
                ->where('status', 'published')
                ->first();

            if ($entryForSite instanceof Statamic\Entries\Entry) {
                $redirect = $entryForSite->url();
            }
        }

        return $redirect === 404 ? null : $redirect;
    }

    public function preload()
    {
        $value = $this->field->value();

        $showAssetOption = $this->showAssetOption();

        $selectedEntry = Str::startsWith($value, 'entry::') ? Str::after($value, 'entry::') : null;

        $selectedAsset = Str::startsWith($value, 'asset::') ? Str::after($value, 'asset::') : null;

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
}
