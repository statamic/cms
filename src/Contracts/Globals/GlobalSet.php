<?php

namespace Statamic\Contracts\Globals;

use Statamic\Contracts\Data\Localizable;
use Illuminate\Contracts\Support\Arrayable;

interface GlobalSet extends Localizable, Arrayable
{
}
