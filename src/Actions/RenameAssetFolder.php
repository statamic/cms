<?php

namespace Statamic\Actions;

use Statamic\Contracts\Assets\AssetFolder;

class RenameAssetFolder extends Action
{
    public static function title()
    {
        return __('Rename');
    }

    public function visibleTo($item)
    {
        return $item instanceof AssetFolder;
    }

    public function authorize($user, $folder)
    {
        return $user->can('rename', $folder);
    }

    public function buttonText()
    {
        /** @translation */
        return 'Rename Folder|Rename :count Folders';
    }

    public function confirmationText()
    {
        /** @translation */
        return 'Are you sure you want to rename this folder?|Are you sure you want to rename these :count folders?';
    }

    public function run($folders, $values)
    {
        return $folders->each->rename($values['name'], true);
    }

    protected function fieldItems()
    {
        return [
            'name' => [
                'type' => 'text',
                'validate' => 'required|alpha_dash',
                'classes' => 'mousetrap',
                'focus' => true,
            ],
        ];
    }
}
