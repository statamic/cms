<?php

namespace Statamic\Extend\Management;

use Statamic\API\Str;
use Statamic\SiteHelpers\Tags as SiteTags;
use Statamic\Exceptions\ResourceNotFoundException;

class TagLoader
{
    public function load($name, $properties)
    {
        // Tags prefixed with site are loaded from the site helpers.
        if ($name === 'site' && class_exists(SiteTags::class)) {
            return $this->init(SiteTags::class, $properties);
        }

        $name = $this->getAlias($name);

        $studly = Str::studly($name);

        if (! class_exists($class = "Statamic\\Addons\\{$studly}\\{$studly}Tags")) {
            throw new ResourceNotFoundException("Could not find files to load the `{$name}` tag.");
        }

        return $this->init($class, $properties);
    }

    private function init($class, $properties)
    {
        return tap(app($class), function ($tag) use ($properties) {
            $tag->setProperties($properties);
        });
    }

    /**
     * Parse for tag aliases
     *
     * @param string $original Original tag to check for
     * @return string
     */
    private function getAlias($original)
    {
        switch ($original) {
            case "switch":
                return "rotate";

            case 'page':
            case 'entry':
                return 'Crud';

            case '404':
                return 'NotFound';

            case 'yield':
                return 'Yields';

            // temporary until we add aliasing for addons
            case 'var':
                return 'Variables';

            default:
                return $original;
        }
    }
}
