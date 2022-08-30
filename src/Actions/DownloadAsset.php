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
        return false;
    }

    public function authorize($authed, $asset)
    {
        return $authed->can('view', $asset);
    }

    public function run($items, $values)
    {
        $asset = $items->first();

        return [
            'message' => false,
            'callback' => ['downloadUrl', $asset->absoluteUrl()],
        ];
    }
}
