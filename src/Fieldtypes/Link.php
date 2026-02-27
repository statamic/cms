<?php

namespace Statamic\Fieldtypes;

use Facades\Statamic\Routing\ResolveRedirect;
use Statamic\Contracts\Entries\Collection;
use Statamic\Contracts\Entries\Entry;
use Statamic\Facades;
use Statamic\Facades\Blink;
use Statamic\Facades\GraphQL;
use Statamic\Facades\Site;
use Statamic\Fields\Field;
use Statamic\Fields\Fieldtype;
use Statamic\Fieldtypes\Link\ArrayableLink;
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
                    'select_across_sites' => [
                        'display' => __('Select Across Sites'),
                        'instructions' => __('statamic::fieldtypes.entries.config.select_across_sites'),
                        'type' => 'toggle',
                    ],
                ],
            ],
        ];
    }

    public function augment($value)
    {
        $localize = ! $this->canSelectAcrossSites();

        return new ArrayableLink(
            $value
                ? ResolveRedirect::item($value, $this->field->parent(), $localize)
                : null,
            ['select_across_sites' => $this->canSelectAcrossSites()]
        );
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
            'select_across_sites' => $this->canSelectAcrossSites(),
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

    public function toGqlType()
    {
        return [
            'type' => GraphQL::string(),
            'resolve' => function ($item, $args, $context, $info) {
                if (! $augmented = $item->resolveGqlValue($info->fieldName)) {
                    return null;
                }

                $item = $augmented->value();

                return is_object($item) ? $item->url() : $item;
            },
        ];
    }

    protected function getConfiguredCollections()
    {
        return empty($collections = $this->config('collections'))
            ? \Statamic\Facades\Collection::handles()->all()
            : $collections;
    }

    private function canSelectAcrossSites(): bool
    {
        return $this->config('select_across_sites', false);
    }

    private function availableSites()
    {
        if (! Site::hasMultiple()) {
            return [];
        }

        $configuredSites = collect($this->getConfiguredCollections())->flatMap(fn ($collection) => \Statamic\Facades\Collection::find($collection)->sites());

        return Site::authorized()
            ->when(isset($configuredSites), fn ($sites) => $sites->filter(fn ($site) => $configuredSites->contains($site->handle())))
            ->map->handle()
            ->values()
            ->all();
    }
}
