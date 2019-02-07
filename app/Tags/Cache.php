<?php

namespace Statamic\Tags;

use Statamic\API\URL;
use Statamic\Tags\Tag;

class Cache extends Tag
{
    /**
     * The {{ cache }} tag
     *
     * @return string
     */
    public function index()
    {
        // If disabled, do nothing.
        if (! $this->isEnabled()) {
            return $this->parse([]);
        }

        // Create a hash so we can identify it. Include the URL in the hash if this is scoped to the page.
        $hash = ($this->get('scope', 'site') === 'page') ? md5(URL::getCurrent(), $this->content) : md5($this->content);

        $path = 'troves:' . $hash;

        if (! $this->cache->exists($path)) {
            $html = $this->parse();

            $this->cache->put($path, $html);
        }

        return $this->cache->get($path);
    }

    private function isEnabled()
    {
        return true;
    }
}
