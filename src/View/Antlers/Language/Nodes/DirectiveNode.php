<?php

namespace Statamic\View\Antlers\Language\Nodes;

class DirectiveNode extends AntlersNode
{
    public string $directiveName = '';
    public string $args = '';
}
