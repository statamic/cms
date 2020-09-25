<?php

namespace Statamic\Tags;

class Partial extends Tags
{
    public function wildcard($tag)
    {
        // We pass the original non-studly case value in as
        // an argument, but fall back to the studly version just in case.
        $partial = $this->params->get('src', $tag);

        $variables = array_merge($this->context->all(), $this->params->all(), [
            '__frontmatter' => $this->params->all(),
            'slot' => $this->isPair ? trim($this->parse()) : null,
        ]);

        return view($this->viewName($partial), $variables)
            ->withoutExtractions()
            ->render();
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

        return $partial;
    }

    protected function underscoredViewName($partial)
    {
        $bits = collect(explode('.', $partial));

        $last = $bits->pull($bits->count() - 1);

        return $bits->implode('.').'._'.$last;
    }
}
