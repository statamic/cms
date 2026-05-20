<?php

namespace Statamic\Actions;

use Statamic\Contracts\Assets\Asset;

class MoveAsset extends Action
{
    public static function title()
    {
        return __('Move');
    }

    public function visibleTo($item)
    {
        return $item instanceof Asset;
    }

    public function authorize($user, $asset)
    {
        return $user->can('move', $asset);
    }

    public function buttonText()
    {
        /** @translation */
        return 'Move Asset|Move :count Assets';
    }

    public function confirmationText()
    {
        /** @translation */
        return 'Are you sure you want to move this asset?|Are you sure you want to move these :count assets?';
    }

    public function run($assets, $values)
    {
        $ids = $assets->each->move($values['folder'])->map->id()->all();

        return [
            'ids' => $ids,
        ];
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
