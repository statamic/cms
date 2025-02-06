<?php

namespace Statamic\Forms;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Statamic\Facades\Antlers;

class RenderableFieldSlot
{
    protected $context;

    public function __construct(protected $html, protected $isBlade)
    {
        //
    }

    public function addContext(array $context): self
    {
        $this->context = $context;

        return $this;
    }

    public function __toString(): string
    {
        if ($this->isBlade) {
            return Blade::render($this->html, ['field' => $this->context]);
        }

        return (string) Antlers::parse($this->html, $this->context);
    }
}
