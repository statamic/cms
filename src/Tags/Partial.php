<?php

namespace Statamic\Tags;

class Partial extends Tags
{
    public function wildcard($tag)
    {
        // We pass the original non-studly case value in as
        // an argument, but fall back to the studly version just in case.
        $partial = $this->params->get('src', $tag);

        return $this->render($partial);
    }

    protected function render($partial)
    {
        if (! $this->shouldRender()) {
            return;
        }

        $variables = array_merge($this->context->all(), $this->params->all(), [
            '__frontmatter' => $this->params->all(),
            'slot' => $this->isPair ? trim($this->parse()) : null,
        ]);

        return view($this->viewName($partial), $variables)
            ->withoutExtractions()
            ->render();
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

    /**
     * The {{ partial:exists }} tag.
     *
     * Returns true if the partial exists, false otherwise.
     * If the src parameter is omitted, it acts like the user is trying to use a partial named "exists".
     */
    public function exists()
    {
        if (! $partial = $this->params->get('src')) {
            return $this->wildcard('exists');
        }

        return view()->exists($this->viewName($partial));
    }

    /**
     * The {{ partial:if_exists }} tag.
     *
     * Returns true if the partial exists, false otherwise.
     * If the src parameter is omitted, it acts like the user is trying to use a partial named "if_exists".
     */
    public function ifExists()
    {
        if (! $partial = $this->params->get('src')) {
            return $this->wildcard('if_exists');
        }

        if (view()->exists($this->viewName($partial))) {
            return $this->render($partial);
        }
    }
}
