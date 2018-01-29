<?php

namespace Statamic\Addons\Partial;

use Statamic\API\Path;
use Statamic\API\File;
use Statamic\API\Parse;
use Statamic\API\Config;
use Statamic\Extend\Tags;

class PartialTags extends Tags
{
    public function __call($method, $arguments)
    {
        // We pass the original non-studly case value in as
        // an argument, but fall back to the studly version just in case.
        $src = $this->get('src', array_get($arguments, 0, $this->tag_method));

        $partialPath = config('statamic.theming.dedicated_view_directories')
            ? resource_path("partials/{$src}.html")
            : resource_path("views/{$src}.html");

        $partial = File::get($partialPath);

        // Allow front matter in these suckers
        $parsed = Parse::frontMatter($partial);
        $variables = array_get($parsed, 'data', []);
        $template = array_get($parsed, 'content');

        // Front-matter, tag parameters, and the context is all passed through to the partial.
        // Since 2.5, parameters need to be prefixed with a colon in order to read from the field.
        $variables = array_merge($this->context, $variables, $this->parameters);

        return Parse::template($template, $variables);
    }
}
