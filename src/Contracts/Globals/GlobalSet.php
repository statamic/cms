<?php

namespace Statamic\Contracts\Globals;

use Statamic\Contracts\Data\Localizable;

interface GlobalSet extends Localizable
{
    public function in($locale);

    public function inSelectedSite();

    public function inCurrentSite();

    public function inDefaultSite();
}
