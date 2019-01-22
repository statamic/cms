<?php

namespace Statamic\Contracts\Data\Entries;

use Statamic\Contracts\Data\Localizable;
use Illuminate\Contracts\Support\Arrayable;
use Statamic\Contracts\Data\Content\Content;

interface Entry extends Content, Localizable, Arrayable
{
}
