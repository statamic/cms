<?php

namespace Statamic\Tags;

use Statamic\Facades\Data;
use Statamic\Facades\URL;

class Nav extends Structure
{
    public function index()
    {
        return $this->structure($this->get('handle', 'collection::pages'));
    }

    public function breadcrumbs()
    {
        $url = URL::getCurrent();
        $segments = explode('/', $url);
        $segments[0] = '/';

        if (! $this->params->bool('include_home', true)) {
            array_shift($segments);
        }

        $crumbs = collect($segments)->map(function () use (&$segments) {
            $uri = URL::tidy(join('/', $segments));
            array_pop($segments);
            return $uri;
        })->mapWithKeys(function ($uri) {
            return [$uri => Data::findByUri($uri)];
        })->filter();

        if (! $this->params->bool('reverse', false)) {
            $crumbs = $crumbs->reverse();
        }

        if ($this->params->bool('trim', true)) {
            $this->content = trim($this->content);
        }

        $output = $this->parseLoop($crumbs->values()->toAugmentedArray());

        if ($backspaces = $this->params->int('backspace', 0)) {
            $output = substr($output, 0, -$backspaces);
        }

        return $output;
    }
}
