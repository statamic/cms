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

        [$name, $methodPart] = self::splitNameAndMethodPart($input);

        $identifier->name = trim($name);

        if ($methodPart === null) {
            $identifier->methodPart = null;
            $identifier->compound = $identifier->name;
        } else {
            $identifier->methodPart = trim($methodPart);
            $identifier->compound = $identifier->name.':'.$identifier->methodPart;
        }

        if (Str::startsWith($identifier->name, '/')) {
            $identifier->name = StringUtilities::substr($identifier->name, 1);
            $identifier->compound = StringUtilities::substr($identifier->compound, 1);
        }

        return $identifier;
    }

    /**
     * Splits the input into the tag name and method part at the first
     * single colon. Double colons act as a namespace separator and
     * remain part of the name (e.g. `namespace::tag:method`).
     */
    public static function splitNameAndMethodPart(string $input): array
    {
        // Mask double colons so the namespace separator
        // is not mistaken for the name/method boundary.
        if (($pos = strpos(strtr($input, ['::' => '__']), ':')) === false) {
            return [$input, null];
        }

        return [substr($input, 0, $pos), substr($input, $pos + 1)];
    }
}
