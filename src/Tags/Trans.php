<?php

namespace Statamic\Tags;

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
        $locale = $this->params->pull('locale') ?? $this->params->pull('site');
        $params = $this->params->all();

        return __($key, $params, $locale);
    }
}
