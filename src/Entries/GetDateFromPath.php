<?php

namespace Statamic\Entries;

class GetDateFromPath
{
    public function __invoke($path)
    {
        $filename = pathinfo($path, PATHINFO_FILENAME);

        if (strpos($filename, '.') === false) {
            return null;
        }

        $firstSegment = explode('.', pathinfo($path, PATHINFO_FILENAME), 2)[0];

        return $this->isDate($firstSegment) ? $firstSegment : null;
    }

    private function isDate($str)
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}(-\d{4})?$/', $str);
    }
}
