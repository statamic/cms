<?php

namespace Statamic\Tags;

use Statamic\View\Cascade;

class Scope extends Tags
{
    public function wildcard($method)
    {
        throw_unless($this->isPair, new \Exception('Scope tag must be a pair'));

        if ($this->tagRenderer) {
            return [
                $this->method => $this->context->all(),
            ];
        }

        app(Cascade::class)->set($this->method, $this->context->all());

        return $this->context->all();
    }
}
