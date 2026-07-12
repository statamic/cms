<?php

namespace Statamic\Tags;

use Statamic\View\Cascade;

class Scope extends Tags
{
    public function wildcard($method)
    {
        throw_unless($this->isPair, new \Exception('Scope tag must be a pair'));

        if ($this->tagRenderer) {
            // This could *probably* be the return value without this condition. It doesn't
            // seem like the cascade needs to be set below. Adding this as a condition so
            // for now though so it doesn't unintentionally break anything.
            return [
                $this->method => $this->context->all(),
            ];
        }

        app(Cascade::class)->set($this->method, $this->context->all());

        return $this->context->all();
    }
}
