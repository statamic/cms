<?php

namespace Statamic\Tags;

use Statamic\Tags\Concerns\RendersViews;

class Partial extends Tags
{
    use RendersViews;

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

        $context = array_diff_key($this->context->all(), array_flip(IncludeTag::VIEW_DATA_KEYS));

        $variables = array_merge($context, $this->params->all(), [
            '__frontmatter' => $this->params->all(),
            'slot' => $this->isPair ? $this->getSlotContent() : null,
        ]);

        return view($this->viewName($partial), $variables)
            ->withoutExtractions()
            ->render();
    }
}
