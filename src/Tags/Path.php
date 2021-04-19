<?php

namespace Statamic\Tags;

use Statamic\Facades;
use Statamic\Facades\Data;
use Statamic\Facades\Site;
use Statamic\Facades\URL;
use Statamic\Support\Str;

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
            return $this->context->get('path');
        }

        $site = Site::current();
        $absolute = $this->params->bool('absolute', false);

        if (! Str::isUrl($src) && ($data = Data::find($src))) {
            $data = $data->in($this->params->get('in', $site->handle()));

            return $absolute ? $data->absoluteUrl() : $data->url();
        }

        $url = $absolute
            ? $site->absoluteUrl().'/'.$src
            : URL::makeRelative($site->url()).'/'.$src;

        return Facades\Path::tidy($url);
    }
}
