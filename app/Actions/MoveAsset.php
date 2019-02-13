<?php

namespace Statamic\Actions;

use Statamic\API;
use Statamic\API\AssetContainer;

class MoveAsset extends Action
{
    protected static $title = 'Move';

    public function visibleTo($key, $context)
    {
        return $key === 'asset-browser';
    }

    public function run($items, $values)
    {
        $items->each->move($values['folder']);
    }

    public function fieldItems()
    {
        $options = AssetContainer::find($this->context['container'])
            ->assetFolders()
            ->mapWithKeys(function ($folder) {
                return [$folder->path() => $folder->title()];
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
