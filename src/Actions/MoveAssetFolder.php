<?php

namespace Statamic\Actions;

use Statamic\Contracts\Assets\AssetFolder;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Blink;

class MoveAssetFolder extends Action
{
    public static function title()
    {
        return __('Move');
    }

    public function visibleTo($item)
    {
        return $item instanceof AssetFolder;
    }

    public function authorize($user, $folder)
    {
        return $user->can('move', $folder);
    }

    public function buttonText()
    {
        /** @translation */
        return 'Move Folder|Move :count Folders';
    }

    public function confirmationText()
    {
        /** @translation */
        return 'Are you sure you want to move this folder?|Are you sure you want to move these :count folders?';
    }

    public function run($folders, $values)
    {
        $folders->each->move($values['folder']);
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
