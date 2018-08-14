<?php

namespace Statamic\Contracts\Data;

use Statamic\Contracts\CP\Editable;
use Statamic\Contracts\HasFieldset;
use Illuminate\Contracts\Support\Arrayable;

interface Data extends Arrayable, Editable, HasFieldset
{
}
