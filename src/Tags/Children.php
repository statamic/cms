<?php

namespace Statamic\Tags;

use Statamic\Facades\Site;
use Statamic\Facades\URL;
use Statamic\Support\Str;

class Children extends Structure
{
    /**
     * The {{ children }} tag.
     *
     * Get any children of the current or a specified url.
     *
     * @return string
     */
    public function index()
    {
        $url = $this->params->get('of', URL::getCurrent());

        $this->params->put('from', Str::start(Str::after(URL::makeAbsolute($url), Site::current()->absoluteUrl()), '/'));
        $this->params->put('max_depth', 1);

        $collection = $this->params->get('collection', $this->context->value('collection')?->handle());

        return $this->structure("collection::{$collection}");
    }
}
