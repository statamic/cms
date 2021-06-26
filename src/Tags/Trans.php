<?php

namespace Statamic\Tags;

use Statamic\Facades\Site;

class Trans extends Tags
{
    /**
     * The {{ trans }} tag.
     *
     * @return string
     */
    public function wildcard($tag)
    {
        $key = $this->params->get('key', $tag);
        $locale = $this->params->pull('locale')
            ?? $this->params->pull('site')
            ?? Site::current()->shortLocale();
        $params = $this->params->all();

        return __($key, $params, $locale);
    }
}
