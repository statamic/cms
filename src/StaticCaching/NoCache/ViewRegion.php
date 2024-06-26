<?php

namespace Statamic\StaticCaching\NoCache;

class ViewRegion extends Region
{
    protected $view;

    public function __construct(Session $session, string $view, array $context)
    {
        $this->session = $session;
        $this->view = $view;
        $this->context = $this->filterContext($context);
        $this->key = sha1($view).$session->getRegionId();
    }

    public function key(): string
    {
        return $this->key;
    }

    public function render(): string
    {
        return view($this->view, $this->fragmentData())->render();
    }
}
