<?php

namespace Statamic\Actions\Concerns;

use Exception;
use Statamic\Contracts;

trait DeletesItems
{
    protected function deleteItems($items)
    {
        $failedActions = $items->filter(function ($item) {
            return ! $item->delete();
        });

        if ($failedActions->isNotEmpty()) {
            throw new Exception($this->getCouldntDeleteTranslation($items->first()));
        }
    }

    private function getCouldntDeleteTranslation($item)
    {
        switch (true) {
            case $item instanceof Contracts\Assets\Asset:
                /** @translation */
                return __("Couldn't delete asset");
            case $item instanceof Contracts\Assets\AssetFolder:
                /** @translation */
                return __("Couldn't delete asset folder");
            case $item instanceof Contracts\Auth\User:
                /** @translation */
                return __("Couldn't delete user");
            case $item instanceof Contracts\Entries\Entry && $item->collection()->sites()->count() === 1:
                /** @translation */
                return __("Couldn't delete entry");
            case $item instanceof Contracts\Forms\Form:
                /** @translation */
                return __("Couldn't delete form");
            case $item instanceof Contracts\Forms\Submission:
                /** @translation */
                return __("Couldn't delete submission");
            case $item instanceof Contracts\Taxonomies\Term:
                /** @translation */
                return __("Couldn't delete term");
            default:
                /** @translation */
                return __("Couldn't delete item");
        }
    }
}
