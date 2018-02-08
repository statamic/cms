<?php

namespace Statamic\View\Antlers;

use Statamic\Events\ViewRendered;

class View
{
    protected $data;
    protected $layout;
    protected $template;

    public function data($data)
    {
        $this->data = $data;

        return $this;
    }

    public function layout($layout)
    {
        $this->layout = $layout;

        return $this;
    }

    public function template($template)
    {
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
}