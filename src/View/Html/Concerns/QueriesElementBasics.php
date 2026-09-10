<?php

namespace Statamic\View\Html\Concerns;

use Statamic\View\Instrumentation\HtmlSpec;

/** @internal */
trait QueriesElementBasics
{
    public function is(string $name): bool
    {
        $tagName = $this->name();

        return $tagName !== null && strcasecmp($tagName, $name) === 0;
    }

    /** @return string[] */
    public function tokens(string $attribute): array
    {
        return HtmlSpec::classList($this->attr($attribute), $this->dynamicTokenDelimiters());
    }

    /** @return array<int, array{0: string, 1: string}> */
    protected function dynamicTokenDelimiters(): array
    {
        return [['{{', '}}']];
    }

    /** @return string[] */
    public function classes(): array
    {
        return $this->tokens('class');
    }

    public function hasClass(string $class): bool
    {
        return in_array($class, $this->classes(), true);
    }
}
