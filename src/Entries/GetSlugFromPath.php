<?php

namespace Statamic\Entries;

class GetSlugFromPath extends GetDateFromPath
{
    public function __invoke($path)
    {
        $path = pathinfo($path, PATHINFO_FILENAME);

        if (strpos($path, '.') === false) {
            return $path;
        }

        $segments = explode('.', $path);

        if ($this->isDate($segments[0])) {
            return $segments[1];
        }

        return $segments[0];
    }
}
