<?php

namespace Statamic\View\Events;

use Statamic\Events\Event;
use Statamic\View\View;

class ViewRendered extends Event
{
    /**
     * @var View
     */
    public $view;

    /**
     * @param View $view
     */
    public function __construct(View $view)
    {
        $this->view = $view;
    }
}
