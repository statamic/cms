<?php

namespace Statamic\Extend;

/**
 * Modify values within templates
 */
class Modifier
{
    /**
     * Provides access to addon helper methods
     */
    use Extensible;

    /**
     * Create a new Modifier instance
     */
    public function __construct()
    {
        $this->bootstrap();
        $this->init();
    }
}
