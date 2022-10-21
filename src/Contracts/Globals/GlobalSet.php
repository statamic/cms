<?php

namespace Statamic\Contracts\Globals;

use Statamic\Contracts\Data\Localizable;

interface GlobalSet extends Localizable
{
    public function in($locale): ?Variables;

    public function inSelectedSite(): ?Variables;

    public function inCurrentSite(): ?Variables;

    public function inDefaultSite(): ?Variables;
}
