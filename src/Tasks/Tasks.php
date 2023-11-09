<?php

namespace Statamic\Tasks;

interface Tasks
{
    public function run(...$closures);
}
