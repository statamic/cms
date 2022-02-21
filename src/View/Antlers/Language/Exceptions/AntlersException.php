<?php

namespace Statamic\View\Antlers\Language\Exceptions;

use Exception;
use Statamic\View\Antlers\Language\Nodes\AbstractNode;

class AntlersException extends Exception
{
    /**
     * @var AbstractNode|null
     */
    public $node = null;

    public $type = '';
}
