<?php

namespace Statamic\Facades\Endpoint;

use Statamic\Support\Str;
use League\Flysystem\Util;
use Statamic\Facades\Pattern;

/**
 * Everything to do with file paths
 */
class Path
{
    /**
     * Makes a $path relative to the BASE
     *
     * @param string $path  The path to change
     * @return string
     */
    public function makeRelative($path)
    {
        $base = self::resolve(base_path());
        $path = self::resolve($path);

        if (strpos($path, $base) === 0) {
            $path = str_replace($base, '', $path);
        }

        return ltrim($path, '/');
    }

    /**
     * Makes a path full, or absolute.
     *
     * Performs a simple concatenation with the Flysytem root folder.
     * It doesn't perform any checks for whether a valid relative path was provided.
     *
     * @param  string $path  The path to change
     * @return string
     */
    public function makeFull($path)
    {
        return self::assemble(base_path(), $path);
    }

    /**
     * Determine if a given path is absolute or not.
     *
     * Unix based paths beginning with slashes are absolute: /path/to/something
     * Windows based paths beginning with drive letters are absolute: C:\path\to\something
     * Paths without a leading slash are relative: path/to/something
     *
     * @param string $path
     * @return bool
     */
    public function isAbsolute($path)
    {
        return $path[0] === '/' || preg_match('~\A[A-Z]:(?![^/\\\\])~i', $path) > 0;
    }

    /**
     * Makes a $path a valid URL
     *
     * @param string $path  The path to change
     * @return string
     */
    public function toUrl($path)
    {
        return Str::ensureLeft(self::makeRelative($path), '/');
    }

    /**
     * Resolve the real-ish path.
     *
     * When you need to resolve the dots in a path but the file doesn't
     * exist, PHP's realpath() won't work. Flysystem already has
     * a way to do this. Nice one. flysystem++
     *
     * @param $path
     * @return string
     */
    public function resolve($path)
    {
        $leadingSlash = Str::startsWith($path, '/');

        $path = Util::normalizeRelativePath(self::tidy($path));

        // Flysystem's method removes the leading slashes. We want to maintain them.
        return $leadingSlash ? Str::ensureLeft($path, '/') : $path;
    }

    /**
     * Cleans up a given $path, removing any flags and order keys (date-based or number-based)
     *
     * Assumes the path will always end with an extension.
     *
     * @param string  $path  Path to clean
     * @return string
     */
    public function clean($path)
    {
        // Remove draft and hidden flags
        $path = preg_replace('/\/_[_]?/', '/', $path);

        // Strip the order keys
        $segments = explode('/', $path);
        $total_segments = count($segments);
        foreach ($segments as $i => &$segment) {
            // Skip the final segment (the basename) if it doesn't contain two periods.
            // This stops filenames like 404.md from being interpreted with 404 as
            // the order key, resulting in a borked filename.
            if ($i+1 === $total_segments && substr_count($segment, '.') < 2) {
                continue;
            }

            $segment = preg_replace(Pattern::orderKey(), '', $segment);
        }

        return implode('/', $segments);
    }

    /**
     * Assembles a URL from an ordered list of segments
     *
     * @param mixed string  Open ended number of arguments
     * @return string
     */
    public function assemble($args)
    {
        $args = func_get_args();
        if (is_array($args[0])) {
            $args = $args[0];
        }

        if (! is_array($args) || ! count($args)) {
            return null;
        }

        return self::tidy(join('/', $args));
    }

    /**
     * Is a given $path a page?
     *
     * @param string $path  Path to check
     * @return bool
     */
    public function isPage($path)
    {
        $ext = pathinfo($path)['extension'];

        return Pattern::endsWith($path, "index.$ext");
    }

    /**
     * Is a given $path an entry?
     *
     * @param string $path  Path to check
     * @return bool
     */
    public function isEntry($path)
    {
        return ! self::isPage($path);
    }

    /**
     * Get the status of a $path
     *
     * @param $path
     * @return string
     */
    public function status($path)
    {
        if (self::isDraft($path)) {
            return 'draft';
        } elseif (self::isHidden($path)) {
            return 'hidden';
        }

        return 'live';
    }

    /**
     * Is a given $path a draft?
     *
     * @param string $path  Path to check
     * @return bool
     */
    public function isDraft($path)
    {
        $ext = pathinfo($path)['extension'];

        $pattern = (self::isPage($path))
            ? "#/__(?:\d+\.)?[\w-]+/(?:\w+\.)?index\.{$ext}$#"
            : "#/__[\w\._-]+\.{$ext}$#";

        return (bool) preg_match($pattern, $path);
    }

    /**
     * Is a given $path hidden?
     *
     * @param string $path  Path to check
     * @return bool
     */
    public function isHidden($path)
    {
        $ext = pathinfo($path)['extension'];

        $pattern = (self::isPage($path))
            ? "#/_(?!_)(?:\d+\.)?[\w-]+/(?:\w+\.)?index\.{$ext}$#"
            : "#/_(?!_)[\w\._-]+\.{$ext}$#";

        return (bool) preg_match($pattern, $path);
    }

    /**
     * Tidy a path.
     *
     * @param string $path  Path to tidy
     * @return string
     */
    public function tidy($path)
    {
        // Replace backslashes with forward slashes for consistency between platforms.
        // PHP is capable of understanding Windows paths that use forward slashes.
        $path = str_replace('\\', '/', $path);

        // Remove occurrences of "//" in a $path (except when part of a protocol).
        return preg_replace('#(^|[^:])//+#', '\\1/', $path);
    }

    /**
     * Get the file extension
     *
     * @param string $path
     * @return string|null
     */
    public function extension($path)
    {
        return array_get(pathinfo($path), 'extension');
    }

    /**
     * Removes the filename from a path
     *
     * eg. `foo/bar/baz/index.md` would return `foo/bar/baz`
     *
     * @param string $path
     * @return string
     */
    public function directory($path)
    {
        $info = pathinfo($path);

        return $info['dirname'];
    }

    /**
     * Get the folder of a path
     *
     * eg. `foo/bar/baz/index.md` would return `baz`
     *
     * @param string $path
     * @return string mixed
     */
    public function folder($path)
    {
        $parts = explode('/', self::directory($path));

        return last($parts);
    }

    /**
     * Remove the last segment of a path
     *
     * eg. `foo/bar/baz/` would return `foo/bar`
     *
     * @param string $path
     * @return string
     */
    public function popLastSegment($path)
    {
        $parts = explode('/', $path);
        array_pop($parts);

        return implode('/', $parts);
    }

    /**
     * Swaps the slug of a $path with the $slug provided
     *
     * @param string  $path  Path to modify
     * @param string  $slug  New slug to use
     * @return string
     */
    public function replaceSlug($path, $slug)
    {
        return self::popLastSegment($path) . '/' . $slug;
    }
}
