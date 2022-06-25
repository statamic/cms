<?php

namespace Statamic\StaticCaching\NoCache;

interface Region
{
    public function key(): string;

    public function placeholder(): string;

    public function context(): array;

    public function fragment(): Fragment;

    public function fragmentData(): array;
}
