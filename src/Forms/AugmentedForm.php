<?php

namespace Statamic\Forms;

use Statamic\Data\AbstractAugmented;
use Statamic\Statamic;

class AugmentedForm extends AbstractAugmented
{
    public function keys()
    {
        $keys = ['handle', 'title', 'fields', 'api_url'];

        if (! Statamic::isApiRoute()) {
            $keys[] = 'honeypot';
        }

        return $keys;
    }
}
