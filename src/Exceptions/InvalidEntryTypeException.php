<?php

namespace Statamic\Exceptions;

/**
 * When an action for one type of entry is performed on another.
 * For example, trying to access the date of an entry when it is ordered numerically.
 */
class InvalidEntryTypeException extends \Exception
{
}
