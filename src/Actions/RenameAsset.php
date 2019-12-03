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
