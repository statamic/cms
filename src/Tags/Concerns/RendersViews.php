<?php

namespace Statamic\Tags\Concerns;

use Illuminate\Support\HtmlString;

trait RendersViews
{
    protected function viewName($partial)
    {
        $partial = str_replace('/', '.', $partial);

        if (view()->exists($underscored = $this->underscoredViewName($partial))) {
            return $underscored;
        }

        if (view()->exists($subdirectoried = 'partials.'.$partial)) {
            return $subdirectoried;
        }

        if (view()->exists($underscored_subdirectoried = 'partials.'.$this->underscoredViewName($partial))) {
            return $underscored_subdirectoried;
        }

        return $partial;
    }

    protected function underscoredViewName($partial)
    {
        $bits = collect(explode('.', $partial));

        $last = $bits->pull($bits->count() - 1);

        return $bits->implode('.').'._'.$last;
    }

    protected function shouldRender(): bool
    {
        if ($this->params->has('when')) {
            return $this->params->bool('when');
        }

        if ($this->params->has('unless')) {
            return ! $this->params->bool('unless');
        }

        return true;
    }

    protected function getSlotContent(): HtmlString|string
    {
        $content = trim($this->parse());

        if ($this->isAntlersBladeComponent()) {
            return new HtmlString($content);
        }

        return $content;
    }

    /**
     * The {{ exists }} tag.
     *
     * Returns true if the view exists, false otherwise. If the src parameter is
     * omitted, it acts like the user is trying to use a view named "exists".
     */
    public function exists()
    {
        if (! $view = $this->params->get('src')) {
            return $this->wildcard('exists');
        }

        return view()->exists($this->viewName($view));
    }

    /**
     * The {{ if_exists }} tag.
     *
     * Renders the view if it exists, and outputs nothing otherwise. If the src parameter
     * is omitted, it acts like the user is trying to use a view named "if_exists".
     */
    public function ifExists()
    {
        if (! $view = $this->params->get('src')) {
            return $this->wildcard('if_exists');
        }

        if (view()->exists($this->viewName($view))) {
            return $this->render($view);
        }
    }
}
