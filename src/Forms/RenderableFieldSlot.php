<?php

namespace Statamic\Forms;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Statamic\Facades\Antlers;

class RenderableFieldSlot
{
    public $context;

    public function __construct(protected $html, protected $isBlade)
    {
        //
    }

    public function addContext($context)
    {
        $this->context = $context;

        return $this;
    }

    public function __toString()
    {
        if ($this->isBlade) {
            return Blade::render($this->html, ['field' => $this->context]);
        }

        return (string) Antlers::parse($this->html, $this->context);
    }
}
