<?php

namespace Statamic\Extend;

use Statamic\Http\Controllers\Controller as AbstractController;

class Controller extends AbstractController
{
    /**
     * Provides access to addon helper methods
     */
    use Extensible;

    /**
     * Create a new Controller instance
     */
    public function __construct()
    {
        $this->bootstrap();
        $this->init();
    }
}
