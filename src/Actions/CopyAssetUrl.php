<?php

namespace Statamic\Actions;

use Statamic\Contracts\Assets\Asset;

class CopyAssetUrl extends Action
{
    protected $icon = 'clipboard';

    protected $confirm = false;

    public static function title()
    {
        return __('Copy URL');
    }

    public function visibleTo($item)
    {
        return $item instanceof Asset && $item->absoluteUrl();
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
            'callback' => ['copyToClipboard', $asset->absoluteUrl()],
        ];
    }
}
