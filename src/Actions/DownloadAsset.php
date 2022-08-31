<?php

namespace Statamic\Actions;

use Statamic\Contracts\Assets\Asset;

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

    public function run($items, $values)
    {
        if ($items->count() > 1) {
            $encodedAssetIds = $items->map(function ($item) {
                return base64_encode($item->id());
            })->join(',');

            return [
                'message' => false,
                'callback' => ['streamUrl', cp_route('assets.zips.show', ['encoded_assets' => $encodedAssetIds])],
            ];
        }

        $asset = $items->first();

        return [
            'message' => false,
            'callback' => ['downloadUrl', $asset->absoluteUrl()],
        ];
    }
}
