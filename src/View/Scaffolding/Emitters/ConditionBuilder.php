<?php

namespace Statamic\View\Scaffolding\Emitters;

class ConditionBuilder
{
    public function __construct(
        protected string $ifTemplate,
        protected string $elseifTemplate,
        protected string $endTemplate,
    ) {
    }

    public function build(array $branches, callable $indentCallback): string
    {
        $output = '';

        foreach ($branches as $index => $branch) {
            $condition = $branch['condition'];
            $template = $indentCallback($branch['template']);
            $directiveTemplate = $index === 0 ? $this->ifTemplate : $this->elseifTemplate;

            $output .= str_replace(
                ['{condition}', '{template}'],
                [$condition, $template],
                $directiveTemplate
            );
        }

        if (! empty($branches)) {
            $output .= $this->endTemplate;
        }

        return $output;
    }
}
