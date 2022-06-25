<?php

namespace Statamic\StaticCaching\NoCache;

class ViewRegion extends AbstractRegion
{
    protected $view;

    public function __construct(Session $session, string $view, array $context)
    {
        $this->session = $session;
        $this->view = $view;
        $this->context = $this->filterContext($context);
        $this->key = str_random(32);
    }

    public function key(): string
    {
        return $this->key;
    }

    public function fragment(): ViewFragment
    {
        return new ViewFragment($this->view, $this->fragmentData());
    }
}
