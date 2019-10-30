<?php

namespace Statamic\Actions;

use Statamic\Facades\User;
use Statamic\Facades\AssetContainer;
use Statamic\Contracts\Assets\Asset;

class MoveAsset extends Action
{
    protected static $title = 'Move';

    public function filter($item)
    {
        return $item instanceof Asset;
    }

    public function authorize($asset)
    {
        return User::current()->can('move', $asset);
    }

    public function run($assets, $values)
    {
        $assets->each->move($values['folder']);
    }

    protected function fieldItems()
    {
        $options = AssetContainer::find($this->context['container'])
            ->assetFolders()
            ->mapWithKeys(function ($folder) {
                return [$folder->path() => $folder->path()];
            })
            ->prepend('/', '/')
            ->all();

        return [
            'folder' => [
                'type' => 'select',
                'options' => $options,
                'validate' => 'required',
            ]
        ];
    }
}
