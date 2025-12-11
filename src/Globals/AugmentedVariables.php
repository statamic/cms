<?php

namespace Statamic\Globals;

use Statamic\Data\AbstractAugmented;

class AugmentedVariables extends AbstractAugmented
{
    private $cachedKeys;

    public function keys()
    {
        if ($this->cachedKeys) {
            return $this->cachedKeys;
        }

        return $this->cachedKeys = $this->data->values()->keys()
            ->merge($this->blueprintFields()->keys())
            ->unique()->sort()->values()->all();
    }

    public function site()
    {
        if ($site = $this->data->value('site')) {
            return $this->wrapValue($site, 'site');
        }
    }

    public function title()
    {
        if ($title = $this->data->value('title')) {
            return $this->wrapValue($title, 'title');
        }
    }
}
