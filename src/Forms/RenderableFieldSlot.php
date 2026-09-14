<?php

namespace Statamic\Forms;

use Illuminate\Support\Facades\Blade;
use Statamic\Facades\Antlers;
use Statamic\Support\Arr;

class RenderableFieldSlot
{
    protected $context = [];

    public function __construct(protected $html, protected $scope, protected $isBlade)
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
        $context = $this->context;

        if ($this->scope) {
            $context = Arr::addScope($context, $this->scope);
        }

        if ($this->isBlade) {
            return Blade::render($this->html, ['field' => $context]);
        }

        return (string) Antlers::parse($this->html, $context);
    }
}
