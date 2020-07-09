<?php

namespace Statamic\StaticCaching;
use Illuminate\Support\Facades\Log;

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

        if ($this->rules !== 'all' && !empty($this->rules['collections'])) {
            if (!empty($this->rules['collections'][$item->collectionHandle()])) {
                $invalidateUrls = $this->rules['collections'][$item->collectionHandle()]['urls'];

                if (is_array($invalidateUrls) && !empty($invalidateUrls)) {
                    foreach ($invalidateUrls as $urlToInvalidate) {
                        $this->cacher->invalidateUrl($urlToInvalidate);
                    }
                }
            }
        }

    }

}
