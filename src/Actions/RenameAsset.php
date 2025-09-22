<?php

namespace Statamic\Actions;

use Statamic\Contracts\Assets\Asset;
use Statamic\Rules\AvailableAssetFilename;

class RenameAsset extends Action
{
    public static function title()
    {
        return __('Rename');
    }

    public function visibleTo($item)
    {
        return $item instanceof Asset;
    }

    public function visibleToBulk($items)
    {
        return false;
    }

    public function authorize($user, $asset)
    {
        return $user->can('rename', $asset);
    }

    public function buttonText()
    {
        /** @translation */
        return 'Rename Asset|Rename :count Assets';
    }

    public function confirmationText()
    {
        /** @translation */
        return 'Are you sure you want to rename this asset?|Are you sure you want to rename these :count assets?';
    }

    public function run($assets, $values)
    {
        $ids = $assets->each->rename($values['filename'], true)->map->id()->all();

        return [
            'ids' => $ids,
        ];
    }

    protected function fieldItems()
    {
        $asset = $this->items->first();

        return [
            'filename' => [
                'type' => 'text',
                'display' => __('Filename'),
                'validate' => ['required', new AvailableAssetFilename($asset)],
                'classes' => 'mousetrap',
                'focus' => true,
                'default' => $value = $asset->filename(),
                'placeholder' => $value,
                'debounce' => false,
                'autoselect' => true,
            ],
        ];
    }
}
