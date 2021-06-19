<?php

namespace Statamic\View;

use Facades\Statamic\View\Cascade;
use InvalidArgumentException;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\View\Events\ViewRendered;

class View
{
    protected $data = [];
    protected $layout;
    protected $template;
    protected $cascade;
    protected $cascadeContent;

    public static function make($template = null, $data = [], $mergeData = [])
    {
        $view = new static;
        $view->template($template);
        $view->with(array_merge($mergeData, $data));

        return $view;
    }

    public static function first(array $templates, $data = [], $mergeData = [])
    {
        $view = new static;

        $template = Arr::first($templates, function ($template) use ($view) {
            return $view->exists($template);
        });

        if (! $template) {
            throw new InvalidArgumentException('None of the templates in the given array exist.');
        }

        return $view::make($template, $data, $mergeData);
    }

    public function exists($template)
    {
        return view()->exists($template);
    }

    public function with($key, $value = null)
    {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $value;
        }

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

        // We only want the template-in-a-layout behavior if the template is Antlers.
        $isAntlers = Str::endsWith($contents->getPath(), ['.antlers.html', '.antlers.php']);

        if ($this->layout && $isAntlers) {
            $contents = view($this->layoutViewName(), array_merge($cascade, [
                'template_content' => $contents->withoutExtractions()->render(),
            ]));
        }

        ViewRendered::dispatch($this);

        return $contents->render();
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

    protected function layoutViewName()
    {
        $view = $this->layout;

        if (view()->exists($subdirectoried = 'layouts.'.$view)) {
            return $subdirectoried;
        }

        return $view;
    }

    protected function templateViewName()
    {
        $view = $this->template;

        if (view()->exists($subdirectoried = 'templates.'.$view)) {
            return $subdirectoried;
        }

        return $view;
    }
}
