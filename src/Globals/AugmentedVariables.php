<?php

namespace Statamic\Globals;

use Statamic\Data\AbstractAugmented;

class AugmentedVariables extends AbstractAugmented
{
    protected $cachedKeys = null;

    public function keys()
    {
        if (! $this->cachedKeys) {
            $this->cachedKeys = $this->data->values()->keys()->all();
        }

        return $this->cachedKeys;
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
