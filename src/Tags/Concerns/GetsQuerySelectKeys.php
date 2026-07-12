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

        if (false !== ($shallow = array_search('@shallow', $selected))) {
            unset($selected[$shallow]);
            $selected = array_merge($selected, $item->shallowAugmentedArrayKeys());
        }

        return $selected;
    }
}
