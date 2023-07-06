<?php

namespace Statamic\StaticCaching\NoCache;

class StringRegion extends Region
{
    protected $content;
    protected $extension;

    public function __construct(Session $session, string $content, array $context, string $extension)
    {
        $this->session = $session;
        $this->content = $content;
        $this->context = $this->filterContext($context);
        $this->extension = $extension;
        $this->key = sha1($content.str_random());
    }

    public function key(): string
    {
        return $this->key;
    }

    public function render(): string
    {
        return (new StringFragment(
            $this->key(),
            $this->content,
            $this->extension,
            $this->fragmentData()
        ))->render();
    }
}
