<?php

namespace Statamic\Addons\Path;

use Statamic\API\URL;
use Statamic\API\Path;
use Statamic\API\Config;
use Statamic\Extend\Tags;

class PathTags extends Tags
{
    /**
     * Maps to the {{ path }} tag
     *
     * @return string
     */
    public function index()
    {
        // If no src param was used, we will treat this as a regular `path` variable.
        if (! $src = $this->get(['src', 'to'])) {
            return array_get($this->context, 'path');
        }

        $url = Path::tidy(Config::getSiteUrl() . $src);

        if ($this->getBool('absolute', false)) {
            $url = URL::makeAbsolute($url);
        }

        return $url;
    }
}
