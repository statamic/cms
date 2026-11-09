<?php

namespace Statamic\Stache\Indexes;

/**
 * @deprecated Entries resolve their site handle in getQueryableValue(), so the regular value index is used.
 */
class Site extends Value
{
    public function getItemValue($item)
    {
        return $item->locale();
    }
}
