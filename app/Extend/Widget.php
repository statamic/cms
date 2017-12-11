<?php

namespace Statamic\Extend;

class Widget
{
    /**
     * Provides access to addon helper methods
     */
    use Extensible;

    /**
     * Provides access to methods for retrieving parameters
     */
    use HasParameters;

    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }
}
