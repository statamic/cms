<?php

namespace Statamic\Actions;

use Statamic\Contracts\Assets\Asset;
use Statamic\Facades\User;

class RenameAsset extends Action
{
    protected static $title = 'Rename';

    public function filter($item)
    {
        return $item instanceof Asset;
    }

    public function authorize($asset)
    {
        return User::current()->can('rename', $asset);
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
            ]
        ];
    }
}
