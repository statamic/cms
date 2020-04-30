<?php

namespace Statamic\StaticCaching;

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
        if ($url = $item->url()) {
            $this->cacher->invalidateUrl($url);
        }
    }
}
