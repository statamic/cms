<?php

namespace Statamic\Fieldtypes\Bard;

class StatamicLinkMark extends LinkMark
{
    protected function convertHref($href)
    {
        return $href;
    }
}
