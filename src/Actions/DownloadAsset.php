<?php

namespace Statamic\Actions;

use Statamic\Contracts\Assets\Asset;
use STS\ZipStream\ZipStreamFacade as Zip;

class DownloadAsset extends Action
{
    protected $confirm = false;

    public static function title()
    {
        return __('Download');
    }

    public function visibleTo($item)
    {
        return $item instanceof Asset;
    }

    public function visibleToBulk($items)
    {
        if ($items->whereInstanceOf(Asset::class)->count() !== $items->count()) {
            return false;
        }

        return true;
    }

    public function authorize($authed, $asset)
    {
        return $authed->can('view', $asset);
    }

    public function download($items, $values)
    {
        if ($items->count() > 1) {
            $zip = Zip::create('assets.zip');
            $items->each(function ($asset) use ($zip) {
                $zip->addRaw($asset->contents(), $asset->basename());
            });
            return $zip->response();
        }

        return $items->first()->download();
    }
}
