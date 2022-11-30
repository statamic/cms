<?php

namespace Statamic\Actions;

use Illuminate\Support\Facades\Storage;
use Statamic\Contracts\Assets\Asset;
use Statamic\Facades\Asset as Assets;

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

    public function run($items, $values)
    {
        $items->each(function ($item) {
            $path = $this->generatePath($item);

            Storage::disk($item->container()->diskHandle())->copy($item->path(), $path);

            $data = $item->data()
                ->except($item->blueprint()->fields()->all()->reject->shouldBeDuplicated()->keys())
                ->merge(['duplicated_from' => $item->id()])
                ->all();

            $asset = Assets::make()
                ->container($item->container()->handle())
                ->path($path)
                ->data($data);

            $asset->save();
        });
    }

    protected function generatePath(Asset $asset, $attempt = 1)
    {
        $path = str_replace($asset->filename(), "{$asset->filename()}-{$attempt}", $asset->path());

        $id = $asset->container()->handle().'::'.$path;

        if (Assets::find($id)) {
            $path = $this->generatePath($asset, $attempt + 1);
        }

        return $path;
    }

    public function authorize($user, $item)
    {
        return $user->can('store', [Asset::class, $item->container()]);
    }
}
