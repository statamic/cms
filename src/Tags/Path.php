<?php

namespace Statamic\Tags;

use Statamic\Facades;
use Statamic\Facades\Data;
use Statamic\Facades\Site;
use Statamic\Facades\URL;

class Path extends Tags
{
    /**
     * Maps to the {{ path }} tag.
     *
     * @return string|void
     */
    public function index()
    {
        // If no src param was used, we will treat this as a regular `path` variable.
        if (! $this->params->hasAny(['src', 'to', 'id'])) {
            return $this->context->get('path');
        }

        if ($id = $this->params->get('id')) {
            return $this->getUrlFromId($id);
        }

        if ($path = $this->params->get(['src', 'to'])) {
            return $this->getUrlFromPath($path);
        }
    }

    protected function getUrlFromId($id)
    {
        if (! $data = Data::find($id)) {
            return;
        }

        if ($localized = $data->in($this->targetSite()->handle())) {
            $data = $localized;
        } elseif ($this->wantsSpecificSite()) {
            return;
        }

        return $this->wantsAbsoluteUrl() ? $data->absoluteUrl() : $data->url();
    }

    protected function getUrlFromPath($path)
    {
        $site = $this->targetSite();

        $url = $this->wantsAbsoluteUrl()
            ? $site->absoluteUrl().'/'.$path
            : URL::makeRelative($site->url()).'/'.$path;

        return Facades\Path::tidy($url);
    }

    protected function targetSite()
    {
        return Site::get($this->params->get('in', Site::current()->handle()));
    }

    protected function wantsSpecificSite()
    {
        return $this->params->has('in');
    }

    protected function wantsAbsoluteUrl()
    {
        return $this->params->bool('absolute', false);
    }
}
