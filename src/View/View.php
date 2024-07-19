<?php

namespace Statamic\View;

use Illuminate\Support\HtmlString;
use InvalidArgumentException;
use Statamic\Facades\Cascade;
use Statamic\Facades\Site;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\View\Antlers\Engine;
use Statamic\View\Antlers\Engine as AntlersEngine;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Statamic\View\Antlers\Language\Runtime\LiteralReplacementManager;
use Statamic\View\Antlers\Language\Runtime\StackReplacementManager;
use Statamic\View\Events\ViewRendered;
use Statamic\View\Interop\Stacks;

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
            'current_layout' => $this->layout(),
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
        $usingLayout = $this->shouldUseLayout();

        if ($usingLayout) {
            GlobalRuntimeState::$containsLayout = true;
            $contents = view($this->templateViewName(), $cascade);

            if (Str::endsWith($this->layoutViewPath(), Engine::EXTENSIONS)) {
                $contents = $contents->withoutExtractions();
            }

            GlobalRuntimeState::$shareVariablesTemplateTrigger = $contents->getPath();

            $contents = $contents->render();
            GlobalRuntimeState::$containsLayout = false;
            GlobalRuntimeState::$shareVariablesTemplateTrigger = '';

            $factory = app('view');

            // Put the sections back. The ->render() will have flushed the sections.
            Cascade::sections()->each(function ($content, $section) use ($factory) {
                $factory->startSection($section, new HtmlString((string) $content));
            });

            Stacks::restoreStacks();

            $contents = view($this->layoutViewName(), array_merge($cascade, GlobalRuntimeState::$layoutVariables, [
                'template_content' => $contents,
            ]));

            GlobalRuntimeState::$layoutVariables = [];
        } else {
            $contents = view($this->templateViewName(), $cascade);
        }

        ViewRendered::dispatch($this);

        if ($usingLayout) {
            GlobalRuntimeState::$renderingLayout = true;
        }

        $renderedContents = $contents->render();

        if ($usingLayout) {
            GlobalRuntimeState::$renderingLayout = false;
        }

        $renderedContents = LiteralReplacementManager::processReplacements($renderedContents);
        $renderedContents = StackReplacementManager::processReplacements($renderedContents);

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
            ->withSite(Site::current())
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
