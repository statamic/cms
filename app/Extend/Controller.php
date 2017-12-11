<?php

namespace Statamic\Extend;

use Statamic\Http\Controllers\Controller as AbstractController;

class Controller extends AbstractController
{
    /**
     * Provides access to addon helper methods
     */
    use Extensible;
}
