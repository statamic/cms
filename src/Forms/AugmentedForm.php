<?php

namespace Statamic\Forms;

use Statamic\Data\AbstractAugmented;

class AugmentedForm extends AbstractAugmented
{
    public function keys()
    {
        return ['handle', 'title', 'fields'];
    }
}
