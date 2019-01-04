<?php

namespace Statamic\View\Antlers;

use Statamic\Events\ViewRendered;
use Facades\Statamic\View\Cascade;

class View
{
    protected $data = [];
    protected $layout;
    protected $template;

    public static function make()
    {
        return new static;
    }

    public function data($data = null)
    {
        if (! $data) {
            return $this->data;
        }

        $this->data = $data;

        return $this;
    }

    public function layout($layout = null)
    {
        if (! $layout) {
            return $this->layout;
        }

        $this->layout = $layout;

        return $this;
    }

    public function template($template = null)
    {
        if (! $template) {
            return $this->template;
        }

        $this->template = $template;

        return $this;
    }

    public function render()
    {
        $data = array_merge($this->data, [
            'template_content' => view($this->template, $this->data)
        ]);

        $contents = view($this->layout, $data)->render();

        ViewRendered::dispatch($this);

        return $contents;
    }

    public function withCascade()
    {
        return $this->data(Cascade::hydrate()->toArray());
    }

    public function __toString()
    {
        return $this->render();
    }
}
