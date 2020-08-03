<?php

namespace Statamic\Tags;

use Statamic\Facades\Config;
use Statamic\Facades\Path as PathAPI;
use Statamic\Facades\URL;

class Path extends Tags
{
    /**
     * Maps to the {{ path }} tag.
     *
     * @return string
     */
    public function index()
    {
        // If no src param was used, we will treat this as a regular `path` variable.
        if (! $src = $this->params->get(['src', 'to'])) {
            return array_get($this->context, 'path');
        }

        $url = PathAPI::tidy(Config::getSiteUrl().$src);

        if ($this->params->bool('absolute', false)) {
            $url = URL::makeAbsolute($url);
        }

        return $url;
    }
}
