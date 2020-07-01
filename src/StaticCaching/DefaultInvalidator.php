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
        if ($this->rules['rules'] === 'all') {
            return $this->cacher->flush();
        }

        // Invalidate the item's own URL.
        if ($url = $item->url()) {
            $this->cacher->invalidateUrl($url);
        }

        if($this->rules['rules'] !== 'all' && ! empty($this->rules['rules']['collections'])) {
            $invalidateUrls = $this->rules['rules']['collections'][$item->collectionHandle()]['urls'];

            if(is_array($invalidateUrls) && ! empty($invalidateUrls)) {
                foreach($invalidateUrls as $urlToInvalidate) {
                    $this->cacher->invalidateUrl($urlToInvalidate);
                }
            }
        }

    }
}
