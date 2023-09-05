<?php

namespace Statamic\Tags\Concerns;

trait GetsQuerySelectKeys
{
    protected function getQuerySelectKeys($item)
    {
        $selected = $this->params->explode('select');

        if (! $selected || $selected === ['*']) {
            return null;
        }

        if (($shallow = array_search('@shallow', $selected)) !== false) {
            unset($selected[$shallow]);
            $selected = array_merge($selected, $item->shallowAugmentedArrayKeys());
        }

        return $selected;
    }
}
