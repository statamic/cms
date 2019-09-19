<?php

namespace Statamic\Entries;

class GetDateFromPath
{
    public function __invoke($path)
    {
        $filename = pathinfo($path, PATHINFO_FILENAME);

        return strpos($filename, '.') === false
            ? null
            : explode('.', pathinfo($path, PATHINFO_FILENAME))[0];
    }
}
