<?php

namespace Statamic\Extend;

class API
{
    /**
     * Provides access to addon helper methods
     */
    use Extensible;

    /**
     * Create a new API instance
     */
    public function __construct()
    {
        $this->bootstrap();
        $this->init();
    }
}
