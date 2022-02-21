<?php

namespace Statamic\View\Antlers\Language\Nodes\Operators\Arithmetic;

use Statamic\View\Antlers\Language\Nodes\AbstractNode;
use Statamic\View\Antlers\Language\Nodes\ArithmeticNodeContract;

class FactorialOperator extends AbstractNode implements ArithmeticNodeContract
{
    public $repeat = 1;
}
