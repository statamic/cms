<?php

namespace Statamic\Actions;

use Statamic\Fields\Fieldset;

class ResetFieldset extends DeleteFieldset
{
    protected $icon = 'history';

    public static function title()
    {
        return __('Reset');
    }

    public function warningText()
    {
        return null;
    }

    public function visibleTo($item)
    {
        return $item instanceof Fieldset && $item->isResettable();
    }

    public function buttonText()
    {
        /** @translation */
        return 'Reset|Reset :count items?';
    }

    public function confirmationText()
    {
        /** @translation */
        return 'Are you sure you want to reset this item?';
    }

    public function run($items, $values)
    {
        $items->each->reset();
    }
}
