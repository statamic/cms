<?php

namespace Statamic\CommandPalette;

class ContentSearchResult extends Link
{
    use Concerns\TracksRecent;

    protected $badge;
    protected $reference;

    public function badge(string $badge): static
    {
        $this->badge = $badge;

        return $this;
    }

    public function reference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'badge' => $this->badge,
            'reference' => $this->reference,
            'trackRecent' => $this->trackRecent,
        ]);
    }
}
