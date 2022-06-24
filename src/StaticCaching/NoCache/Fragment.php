<?php

namespace Statamic\StaticCaching\NoCache;

interface Fragment
{
    public function render(): string;
}
