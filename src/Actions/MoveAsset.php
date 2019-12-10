<?php

namespace Statamic\Actions;

use Statamic\Facades\AssetContainer;
use Statamic\Contracts\Assets\Asset;

class MoveAsset extends Action
{
    protected static $title = 'Move';

    public function filter($item)
    {
        return $item instanceof Asset;
    }

    public function authorize($user, $asset)
    {
        return $user->can('move', $asset);
    }

    public function buttonText()
    {
        return [
            'single' => 'Move Asset',
            'plural' => 'Move :count Assets'
        ];
    }

    public function confirmationText()
    {
        return [
            'single' => 'Are you sure you want to move this asset?',
            'plural' => 'Are you sure you want to move these :count assets?'
        ];
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
