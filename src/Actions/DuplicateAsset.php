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
                $duplicatePath = str_replace($item->filename(), "{$item->filename()}-02", $item->path());

                $assetData = Arr::except(
                    $item->data(),
                    $item->blueprint()->fields()->all()->reject->shouldBeDuplicated()->keys()->all()
                );

                $assetData['duplicated_from'] = $item->id();

                Storage::disk($item->container()->diskHandle())->copy($item->path(), $duplicatePath);

                $asset = AssetAPI::make()
                    ->container($item->container()->handle())
                    ->path($duplicatePath)
                    ->data($assetData);

                $asset->save();
            });
    }
}
