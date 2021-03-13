<?php

namespace Statamic\Actions;

use Statamic\Contracts\Assets\Asset;
use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Contracts\Entries\Entry;
use Statamic\Contracts\Taxonomies\Term;
use Statamic\Contracts\Forms\Submission;

class Delete extends Action
{
    protected $dangerous = true;

    public static function title()
    {
        return __('Delete');
    }

    public function visibleTo($item)
    {
        if ($item instanceof Asset || $item instanceof Term || $item instanceof Submission || $item instanceof UserContract ) {
            return true;
        }

        return $item instanceof Entry && $item->collection()->sites()->count() === 1;
    }

    public function authorize($user, $item)
    {
        if ($item instanceof UserContract && $user->id() === $item->id()) {
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
        return 'Are you sure you want to want to delete this?|Are you sure you want to delete these :count items?';
    }

    public function run($items, $values)
    {
        $items->each->delete();
    }
}
