<?php

namespace Statamic\Entries;

class GetSlugFromPath
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

    private function isDate($str)
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}(-\d{4})?$/', $str);
    }
}
