<?php

namespace Statamic\Actions;

use Statamic\Contracts\Assets\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Blink;

class MoveAsset extends Action
{
    public static function title()
    {
        return __('Move');
    }

    public function visibleTo($item)
    {
        return $item instanceof Asset;
    }

    public function authorize($user, $asset)
    {
        return $user->can('move', $asset);
    }

    public function buttonText()
    {
        /** @translation */
        return 'Move Asset|Move :count Assets';
    }

    public function confirmationText()
    {
        /** @translation */
        return 'Are you sure you want to move this asset?|Are you sure you want to move these :count assets?';
    }

    public function run($assets, $values)
    {
        $ids = $assets->each->move($values['folder'])->map->id()->all();

        return [
            'ids' => $ids,
        ];
    }

    protected function fieldItems()
    {
        $options = Blink::once('action-move-asset-folders', function () {
            return AssetContainer::find($this->context['container'])
                ->assetFolders()
                ->mapWithKeys(function ($folder) {
                    return [$folder->path() => $folder->path()];
                })
                ->prepend('/', '/')
                ->all();
        });

        return [
            'folder' => [
                'display' => __('Folder'),
                'type' => 'select',
                'options' => $options,
                'validate' => 'required',
            ],
        ];
    }
}
