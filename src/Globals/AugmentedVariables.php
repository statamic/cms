<?php

namespace Statamic\Globals;

use Statamic\Data\AbstractAugmented;

class AugmentedVariables extends AbstractAugmented
{
    public function keys()
    {
        return $this->data->values()->keys()->all();
    }
}
