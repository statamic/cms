<?php

namespace Statamic\Actions;

use Statamic\Actions\Concerns\MakesZips;
use Statamic\Contracts\Assets\AssetFolder;
use Statamic\Support\Str;

class DownloadAssetFolder extends Action
{
    use MakesZips;

    protected $confirm = false;

    public static function title()
    {
        return __('Download');
    }

    public function visibleTo($item)
    {
        return $item instanceof AssetFolder;
    }

    public function authorize($user, $folder)
    {
        return $user->can('view', $folder);
    }

    public function download($items, $values)
    {
        $folder = $items->first();
        $assets = $folder->assets(true);

        return $this->makeZipResponse("{$folder->basename()}.zip", $assets->mapWithKeys(function ($asset) use ($folder) {
            return [Str::after($asset->path(), $folder->path().'/') => $asset->stream()];
        }));
    }
}
