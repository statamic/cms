<?php

namespace Statamic\Entries;

class RemoveSuffixFromPath
{
    public function __invoke($path)
    {
        if (! $suffix = (new GetSuffixFromPath)($path)) {
            return $path;
        }

        $ext = pathinfo($path, PATHINFO_EXTENSION);

        $pathWithoutExtension = substr($path, 0, -(strlen($ext) + 1));

        $path = substr($pathWithoutExtension, 0, -(strlen($suffix) + 1));

        return $path.'.'.$ext;
    }
}
