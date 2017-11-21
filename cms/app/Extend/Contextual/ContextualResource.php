<?php

namespace Statamic\Extend\Contextual;

use Statamic\API\URL;

class ContextualResource extends ContextualObject
{
    /**
     * Output the URL to the resource
     *
     * @param  string $path  Relative path to the resource
     * @return string
     */
    public function url($path)
    {
        return URL::prependSiteRoot(
            URL::assemble(RESOURCES_ROUTE, 'addons', $this->context, $path)
        );
    }

    /**
     * Output the URL to the resource in its appropriate HTML tag
     *
     * @param  string $path  Relative path to the resource
     * @return string
     */
    public function tag($path)
    {
        return $this->url($path);
    }

    /**
     * Output the resource content inline
     *
     * @param  string $str  Resource content
     * @return string
     */
    public function inline($str)
    {
        return $str;
    }
}
