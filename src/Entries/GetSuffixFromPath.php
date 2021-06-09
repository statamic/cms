<?php

namespace Statamic\Entries;

class GetSuffixFromPath
{
    public function __invoke($path)
    {
        $filename = pathinfo($path, PATHINFO_FILENAME);

        if (strpos($filename, '.') === false) {
            return null;
        }

        $segments = explode('.', pathinfo($path, PATHINFO_FILENAME));

        if ($this->isDate($segments[0])) {
            return $segments[2] ?? null;
        }

        return $segments[1] ?? null;
    }

    private function isDate($str)
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}(-\d{4})?$/', $str);
    }
}
