<?php

namespace Statamic\StaticCaching\NoCache;

interface Region
{
    public function key(): string;

    public function placeholder(): string;

    public function context(): array;

    public function fragmentData(): array;

    public function render(): string;
}
