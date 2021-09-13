<?php

namespace Statamic\View\Antlers\Language\Nodes;

class LibraryInvocationConstruct extends AbstractNode
{
    public $libraryName = '';
    public $methodName = '';

    /**
     * @var ArgumentGroup|null
     */
    public $arguments = null;
}
