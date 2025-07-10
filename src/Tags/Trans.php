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
        $fallback = $this->params->get('fallback');
        $params = $this->params->all();

        $translation = __($key, $params, $locale);
        if ($fallback && $translation === $key) {
            return __($fallback, $params, $locale);
        } else {
            return $translation;
        }
    }
}
