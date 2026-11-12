<?php

namespace Statamic\Yaml;

use Exception;

/**
 * Thrown when a document needs Symfony's full parser.
 *
 * @internal
 */
class CannotFastParse extends Exception
{
}
