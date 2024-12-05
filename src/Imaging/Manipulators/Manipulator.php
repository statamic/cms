<?php

namespace Statamic\Imaging\Manipulators;

use Statamic\Contracts\Imaging\Manipulator as Contract;
use Statamic\Imaging\Manipulators\Sources\Source;

abstract class Manipulator implements Contract
{
    protected Source $source;
    private array $params = [];

    public function setSource(Source $source): Contract
    {
        $this->source = $source;

        return $this;
    }

    public function addParams(array $params): Contract
    {
        $this->params += $params;

        return $this;
    }

    public function getParams(): array
    {
        return $this->params;
    }
}
