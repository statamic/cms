<?php

namespace Statamic\CommandPalette;

class Link extends Command
{
    use Concerns\TracksRecent;

    protected $url;
    protected $openNewTab;

    public function url(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function openNewTab(bool $openNewTab): static
    {
        $this->openNewTab = $openNewTab;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'url' => $this->url,
            'openNewTab' => $this->openNewTab,
            'trackRecent' => $this->trackRecent,
        ]);
    }
}
