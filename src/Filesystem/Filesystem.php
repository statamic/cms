<?php

namespace Statamic\Filesystem;

interface Filesystem
{
    public function get($path, $fallback = null);

    public function exists($path);

    public function put($path, $contents);

    public function delete($path);

    public function copy($src, $dest, $overwrite = false);

    public function move($src, $dest, $overwrite = false);

    public function rename($old, $new);

    public function extension($path);

    public function mimeType($path);

    public function lastModified($path);

    public function size($file);

    public function isImage($path);

    public function makeDirectory($path);
}
