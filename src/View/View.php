<?php

namespace Statamic\View;

use Facades\Statamic\View\Cascade;
use InvalidArgumentException;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\View\Antlers\Engine as AntlersEngine;
use Statamic\View\Events\ViewRendered;

class View
{
    protected $data = [];
    protected $layout;
    protected $template;
    protected $cascade;
    protected $cascadeContent;

    public static function make($template = null, $data = [])
    {
        return (new static)
            ->template($template)
            ->with($data);
    }

    public static function first(array $templates, $data = [])
    {
        $template = Arr::first($templates, function ($template) {
            return view()->exists($template);
        });

        if (! $template) {
            throw new InvalidArgumentException('None of the views in the given array exist.');
        }

        return static::make($template, $data);
    }

    public function with($data)
    {
        $this->data = $data;

        return $this;
    }

    public function data()
    {
        return $this->data;
    }

    public function gatherData()
    {
        return array_merge($this->cascade(), $this->data, [
            'current_template' => $this->template(),
        ]);
    }

    public function layout($layout = null)
    {
        if (count(func_get_args()) === 0) {
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

    public function render(): string
    {
        $cascade = $this->gatherData();

        $contents = view($this->templateViewName(), $cascade);

        if ($this->shouldUseLayout()) {
            $contents = view($this->layoutViewName(), array_merge($cascade, [
                'template_content' => $contents->withoutExtractions()->render(),
            ]));
        }

        ViewRendered::dispatch($this);

        return $contents->render();
    }

    private function shouldUseLayout()
    {
        if (! $this->layout) {
            return false;
        }

        if (! $this->isUsingAntlersTemplate()) {
            return false;
        }

        if ($this->isUsingXmlTemplate() && ! $this->isUsingXmlLayout()) {
            return false;
        }

        return true;
    }

    public function wantsXmlResponse()
    {
        if (! $this->isUsingAntlersTemplate()) {
            return false;
        }

        return $this->isUsingXmlTemplate() || $this->isUsingXmlLayout();
    }

    private function isUsingAntlersTemplate()
    {
        return Str::endsWith($this->templateViewPath(), collect(AntlersEngine::EXTENSIONS)->map(function ($extension) {
            return '.'.$extension;
        })->all());
    }

    private function isUsingXmlTemplate()
    {
        return Str::endsWith($this->templateViewPath(), '.xml');
    }

    private function isUsingXmlLayout()
    {
        if (! $this->layout) {
            return false;
        }

        return Str::endsWith($this->layoutViewPath(), '.xml');
    }

    private function templateViewPath()
    {
        return view($this->templateViewName())->getPath();
    }

    private function layoutViewPath()
    {
        return view($this->layoutViewName())->getPath();
    }

    protected function cascade()
    {
        return $this->cascade = $this->cascade ?? Cascade::instance()
            ->withContent($this->cascadeContent)
            ->hydrate()
            ->toArray();
    }

    public function cascadeContent($content = null)
    {
        if (func_num_args() === 0) {
            return $this->cascadeContent;
        }

        $this->cascadeContent = $content;

        return $this;
    }

    public function __toString()
    {
        return $this->render();
    }

    private function layoutViewName()
    {
        $view = $this->layout;

        if (view()->exists($subdirectoried = 'layouts.'.$view)) {
            return $subdirectoried;
        }

        return $view;
    }

    private function templateViewName()
    {
        $view = $this->template;

        if (view()->exists($subdirectoried = 'templates.'.$view)) {
            return $subdirectoried;
        }

        return $view;
    }
}
