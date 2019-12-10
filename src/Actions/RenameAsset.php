<?php

namespace Statamic\Actions;

use Statamic\Contracts\Assets\Asset;

class RenameAsset extends Action
{
    protected static $title = 'Rename';

    public function filter($item)
    {
        return $item instanceof Asset;
    }

    public function authorize($user, $asset)
    {
        return $user->can('rename', $asset);
    }

    public function buttonText()
    {
        return [
            'single' => 'Rename Asset',
            'plural' => 'Rename :count Assets'
        ];
    }

        public function confirmationText()
    {
        return [
            'single' => 'Are you sure you want to rename this asset?',
            'plural' => 'Are you sure you want to rename these :count assets?'
        ];
    }

    public function run($assets, $values)
    {
        return $assets->each->rename($values['filename'], true);
    }

    protected function fieldItems()
    {
        return [
            'filename' => [
                'type' => 'text',
                'validate' => 'required', // TODO: Better filename validation
                'classes' => 'mousetrap'
            ]
        ];
    }
}
