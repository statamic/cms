<?php

namespace Statamic\Stache;

interface Driver
{
    public function key();

    public function getFilesystemRoot();

    public function createItem($path, $contents);

    public function isMatchingFile($file);

    public function toPersistentArray($repo);
}
