<?php

namespace Statamic\Contracts\Entries;

use Statamic\Contracts\Data\Localizable;
use Illuminate\Contracts\Support\Arrayable;

interface Entry extends Localizable, Arrayable
{
}
