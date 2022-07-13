<?php

namespace Statamic\View;

use Facades\Statamic\View\Cascade;
use InvalidArgumentException;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\View\Antlers\Engine;
use Statamic\View\Antlers\Engine as AntlersEngine;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Statamic\View\Antlers\Language\Runtime\LiteralReplacementManager;
use Statamic\View\Antlers\Language\Runtime\StackReplacementManager;
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
        return app(static::class)
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
        if (func_num_args() === 0) {
            return $this->layout;
        }

        $this->layout = $layout;

        return $this;
    }

    public function template($template = null)
    {
        if (func_num_args() === 0) {
            return $this->template;
        }

        $this->template = $template;

        return $this;
    }

    public function render(): string
    {
        $cascade = $this->gatherData();

        if ($this->shouldUseLayout()) {
            GlobalRuntimeState::$containsLayout = true;

            $contents = view($this->templateViewName(), $cascade);

            if (Str::endsWith($this->layoutViewPath(), Engine::EXTENSIONS)) {
                $contents = $contents->withoutExtractions();
            }

            $contents = $contents->render();
            GlobalRuntimeState::$containsLayout = false;

            $contents = view($this->layoutViewName(), array_merge($cascade, [
                'template_content' => $contents,
            ]));
        } else {
            $contents = view($this->templateViewName(), $cascade);
        }

        ViewRendered::dispatch($this);

        $renderedContents = $contents->render();

        if (config('statamic.antlers.version') == 'runtime') {
            $renderedContents = LiteralReplacementManager::processReplacements($renderedContents);
            $renderedContents = StackReplacementManager::processReplacements($renderedContents);
        }

        return $renderedContents;
    }

    protected function shouldUseLayout()
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

    protected function isUsingAntlersTemplate()
    {
        return Str::endsWith($this->templateViewPath(), collect(AntlersEngine::EXTENSIONS)->map(function ($extension) {
            return '.'.$extension;
        })->all());
    }

    protected function isUsingXmlTemplate()
    {
        return Str::endsWith($this->templateViewPath(), '.xml');
    }

    protected function isUsingXmlLayout()
    {
        if (! $this->layout) {
            return false;
        }

        return Str::endsWith($this->layoutViewPath(), '.xml');
    }

    protected function templateViewPath()
    {
        return view($this->templateViewName())->getPath();
    }

    protected function layoutViewPath()
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
