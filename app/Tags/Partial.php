<?php

namespace Statamic\Tags;

use Statamic\API\File;
use Statamic\API\Path;
use Statamic\API\Parse;
use Statamic\Tags\Tags;
use Statamic\API\Config;
use Statamic\API\Antlers;

class Partial extends Tags
{
    public function __call($method, $arguments)
    {
        // We pass the original non-studly case value in as
        // an argument, but fall back to the studly version just in case.
        $partial = $this->get('src', array_get_colon($arguments, 0, $this->tag_method));

        $variables = array_merge($this->context, $this->parameters, [
            '__frontmatter' => $this->parameters
        ]);

        return view($this->viewName($partial), $variables);
    }

    protected function viewName($partial)
    {
        $partial = str_replace('/', '.', $partial);

        if (view()->exists($underscored = $this->underscoredViewName($partial))) {
            return $underscored;
        }

        if (view()->exists($subdirectoried = 'partials.'.$partial)) {
            return $subdirectoried;
        }

        return $partial;
    }

    protected function underscoredViewName($partial)
    {
        $bits = collect(explode('.', $partial));

        $last = $bits->pull($bits->count()-1);

        return $bits->implode('.') . '._' . $last;
    }
}
