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
     * Get any children of the current url
     *
     * @return string
     */
    public function index()
    {
        $this->params->put('from', Str::start(Str::after(URL::getCurrent(), Site::current()->url()), '/'));
        $this->params->put('max_depth', 1);

        return $this->structure($this->params->get('handle', 'collection::pages'));
    }
}
