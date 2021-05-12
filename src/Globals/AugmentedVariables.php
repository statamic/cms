<?php

namespace Statamic\Globals;

use Statamic\Data\AbstractAugmented;

class AugmentedVariables extends AbstractAugmented
{
    public function keys()
    {
        return $this->data->values()->keys()->all();
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
