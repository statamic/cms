<?php

namespace Statamic\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Statamic\Contracts\Assets\Asset;
use Statamic\Facades\Asset as AssetAPI;

class DuplicateAsset extends Action
{
    public static function title()
    {
        return __('Duplicate');
    }

    public function visibleTo($item)
    {
        return $item instanceof Asset;
    }

    public function visibleToBulk($items)
    {
        return $this->visibleTo($items->first());
    }

    public function run($items, $values)
    {
        collect($items)
            ->each(function ($item) {
                if ($item instanceof Asset) {
                    $duplicatePath = str_replace($item->filename(), "{$item->filename()}-02", $item->path());

                    $assetMeta = $item->meta();
                    $assetMeta['data'] = Arr::except($assetMeta['data'], config('statamic.duplicator.ignored_fields.assets'));

                    if (config('statamic.duplicator.fingerprint') === true) {
                        $assetMeta['is_duplicate'] = true;
                    }

                    $asset = AssetAPI::make()
                        ->container($item->container()->handle())
                        ->path($duplicatePath)
                        ->writeMeta($assetMeta);

                    Storage::disk($item->container()->diskHandle())->copy($item->path(), $duplicatePath);
                }
            });
    }
}
