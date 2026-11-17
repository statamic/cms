<?php

namespace Statamic\Stache\Exceptions;

use Exception;

/**
 * Thrown when a store's items are asked for while they are still being made.
 */
class ItemsNotReadyException extends Exception
{
}
