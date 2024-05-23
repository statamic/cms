<?php

namespace Statamic\Tags;

use Statamic\Tags\Tags;

class Js extends Tags
{
    /** @var \Statamic\Tags\Parameters */
    public $params;

    /** @var \Statamic\Tags\Context */
    public $context;

    public function index()
    {
        $name = $this->params->get('name');
        $from = $this->params->get('from');
        if (! $name || ! $from) {
            return;
        }

        return $this->render($name, $from);
    }

    public function wildcard($from)
    {
        $name = $this->method;
        $from = $this->context->get($name);
        if (! $name || ! $from) {
            return;
        }

        return $this->render($name, $from);
    }

    public function render($name, $from)
    {
        return ($this->params->get('script', true) ? '<script>' : '')
            . ($this->params->get('const', true) ? 'const' : 'let') . ' ' . $name . ' = ' . json_encode($from) . ';'
            . ($this->params->get('script', true) ? '</script>' : '');
    }
}
