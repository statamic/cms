<?php

namespace Statamic\Events;

use Statamic\View\Antlers\View;

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
