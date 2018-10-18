<?php

namespace Statamic\StaticCaching;

use Statamic\StaticCaching\Cacher;

class DefaultInvalidator implements Invalidator
{
    protected $cacher;
    protected $rules;

    public function __construct(Cacher $cacher, $rules = [])
    {
        $this->cacher = $cacher;
        $this->rules = $rules;
    }

    public function invalidate($item)
    {
        if ($this->rules === 'all') {
            return $this->cacher->flush();
        }

        // Invalidate the item's own URL.
        $this->cacher->invalidateUrl($item->url());
    }
}
