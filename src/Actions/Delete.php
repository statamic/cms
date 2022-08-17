<?php

namespace Statamic\Actions;

use Statamic\Contracts;

class Delete extends Action
{
    protected $dangerous = true;

    public static function title()
    {
        return __('Delete');
    }

    public function visibleTo($item)
    {
        switch (true) {
            case $item instanceof Contracts\Entries\Entry && $item->collection()->sites()->count() === 1:
            case $item instanceof Contracts\Taxonomies\Term:
            case $item instanceof Contracts\Assets\Asset:
            case $item instanceof Contracts\Assets\AssetFolder:
            case $item instanceof Contracts\Forms\Form:
            case $item instanceof Contracts\Forms\Submission:
            case $item instanceof Contracts\Auth\User:
                return true;
            default:
                return false;
        }
    }

    public function authorize($user, $item)
    {
        if ($item instanceof Contracts\Auth\User && $user->id() === $item->id()) {
            return false;
        }

        return $user->can('delete', $item);
    }

    public function buttonText()
    {
        /** @translation */
        return 'Delete|Delete :count items?';
    }

    public function confirmationText()
    {
        /** @translation */
        return 'Are you sure you want to delete this?|Are you sure you want to delete these :count items?';
    }

    public function run($items, $values)
    {
        $items->each->delete();
    }
}
