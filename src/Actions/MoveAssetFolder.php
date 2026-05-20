<?php

namespace Statamic\Actions;

use Statamic\Contracts\Assets\AssetFolder;

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
        return [
            'folder' => [
                'display' => __('Folder'),
                'type' => 'asset_folder',
                'container' => $this->context['container'],
                'mode' => 'select',
                'max_items' => 1,
                'validate' => 'required',
            ],
        ];
    }
}
