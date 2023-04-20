<?php

namespace Statamic\Actions;

use Statamic\Contracts\Assets\Asset;

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
        return [
            'filename' => [
                'type' => 'text',
                'validate' => 'required', // TODO: Better filename validation
                'classes' => 'mousetrap',
                'focus' => true,
                'placeholder' => $this->items->containsOneItem() ? $this->items->first()->filename() : null,
            ],
        ];
    }
}
