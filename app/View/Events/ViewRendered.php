<?php

namespace Statamic\View\Events;

use Statamic\View\View;
use Statamic\Events\Event;

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
