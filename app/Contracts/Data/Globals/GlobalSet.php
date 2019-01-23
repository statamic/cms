<?php

namespace Statamic\Contracts\Data\Globals;

use Statamic\Contracts\Data\Localizable;
use Illuminate\Contracts\Support\Arrayable;
use Statamic\Contracts\Data\Content\Content;

interface GlobalSet extends Content, Localizable, Arrayable
{
}
