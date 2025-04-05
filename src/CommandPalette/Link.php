<?php

namespace Statamic\CommandPalette;

class Link extends Command
{
    protected $url;

    public function url(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'url' => $this->url,
        ]);
    }
}
