<?php

namespace Statamic\Actions;

use Statamic\Contracts\Assets\AssetFolder;
use Statamic\Listeners\UpdateAssetReferences;
use Statamic\Rules\AlphaDashSpace;

use function Statamic\trans as __;

class RenameAssetFolder extends Action
{
    protected $icon = 'folder-edit';

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
        // Update the references to the assets in all the folders together, rather than one folder at a time.
        return UpdateAssetReferences::batch(fn () => $folders->each->rename($values['name'], true));
    }

    protected function fieldItems()
    {
        return [
            'name' => [
                'type' => 'text',
                'validate' => ['required', 'string', new AlphaDashSpace],
                'classes' => 'mousetrap',
                'focus' => true,
                'default' => $value = $this->items->containsOneItem() ? $this->items->first()->basename() : null,
                'placeholder' => $value,
                'debounce' => false,
                'autoselect' => true,
            ],
        ];
    }
}
