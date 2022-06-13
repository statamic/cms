<?php

namespace Statamic\View\Antlers\Language\Analyzers;

use Illuminate\Support\Str;
use Statamic\View\Antlers\Language\Nodes\TagIdentifier;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;

class TagIdentifierAnalyzer
{
    /**
     * Parses the input string to determine what the
     * associated nodes identifying information is.
     *
     * This method will handle:
     *   - primary variable paths
     *   - tag names
     *   - tag method parts
     *   - etc.
     *
     * @param  string  $input  The content to parse.
     * @return TagIdentifier
     */
    public static function getIdentifier($input)
    {
        $identifier = new TagIdentifier();
        $identifier->content = trim($input);

        $parts = explode(':', $input);

        if (count($parts) == 1) {
            $identifier->name = trim($parts[0]);
            $identifier->methodPart = null;
            $identifier->compound = $identifier->name;
        } elseif (count($parts) > 1) {
            $name = array_shift($parts);
            $methodPart = implode(':', $parts);

            $identifier->name = trim($name);
            $identifier->methodPart = trim($methodPart);
            $identifier->compound = $identifier->name.':'.$identifier->methodPart;
        } else {
            $identifier->name = trim($input);
            $identifier->methodPart = '';
        }

        if (Str::startsWith($identifier->name, '/')) {
            $identifier->name = StringUtilities::substr($identifier->name, 1);
            $identifier->compound = StringUtilities::substr($identifier->compound, 1);
        }

        return $identifier;
    }
}
