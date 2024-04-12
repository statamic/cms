<?php

namespace Tests;

use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk as BasePreventsSavingStacheItemsToDisk;

trait PreventSavingStacheItemsToDisk
{
    use BasePreventsSavingStacheItemsToDisk;
}
