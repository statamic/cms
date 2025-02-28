<?php

namespace Statamic\Entries;

use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Statamic\Facades\Entry as EntryFacade;

class MinuteScheduledRevisions
{
    public function __construct(private readonly CarbonInterface $minute)
    {
    }

    public function __invoke(): Collection
    {
        return EntryFacade::all()
            ->filter(fn (Entry $entry) => $entry->hasRevisions())
            ->filter(fn (Entry $entry) => $entry->latestRevision()->publishAt())
            ->filter(
                fn (Entry $entry) => $entry
                    ->latestRevision()
                    ->publishAt()
                    ->isSameMinute($this->minute)
            );
    }
}
