<?php

namespace Statamic\Actions;

use Statamic\Actions\Concerns\MakesZips;
use Statamic\Contracts\Assets\Asset;

class DownloadAsset extends Action
{
    use MakesZips;

    protected $confirm = false;

    public static function title()
    {
        return __('Download');
    }

    public function visibleTo($item)
    {
        return $item instanceof Asset;
    }

    public function authorize($authed, $asset)
    {
        return $authed->can('view', $asset);
    }

    public function download($items, $values)
    {
        if ($items->count() == 1) {
            return $items->first()->download();
        }

        return $this->makeZipResponse("{$this->context['container']}.zip", $items->mapWithKeys(function ($asset) {
            return [$asset->basename() => $asset->stream()];
        }));
    }
}
