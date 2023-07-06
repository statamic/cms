<?php

namespace Statamic\Tags;

use Statamic\Facades\Config;
use Statamic\Facades\File;
use Statamic\Facades\Path;
use Statamic\Facades\URL;
use Statamic\Support\Str;

class Theme extends Tags
{
    /**
     * Catch-all for dynamic theme tags.
     *
     * Namespaces the `src` to the specified directory.
     * eg. {{ theme:img }}, {{ theme:svg }}, etc.
     *
     * @param  string  $method  Tag part
     * @param  array  $arguments  Unused
     * @return string
     */
    public function __call($method, $arguments)
    {
        return $this->path($method);
    }

    private function path($dir = null)
    {
        $src = $this->params->get('src');

        $path = Path::tidy($dir.'/'.$src);

        return $this->themeUrl($path);
    }

    /**
     * The {{ theme:asset }} tag.
     *
     * Outputs the path to an asset.
     *
     * @return string
     */
    public function asset()
    {
        return $this->path();
    }

    /**
     * The {{ theme:img }} tag.
     *
     * @return string
     */
    public function img()
    {
        $src = $this->params->get('src');

        $path = 'img/'.$src;

        $url = $this->themeUrl($path);

        $alt = $this->params->get('alt');

        if ($this->params->bool('tag')) {
            return "<img src=\"$url\" alt=\"$alt\" />";
        }

        return $url;
    }

    /**
     * The {{ theme:js }} tag.
     *
     * @return string
     */
    public function js()
    {
        $src = $this->params->get('src', 'app');

        $path = 'js/'.Str::ensureRight($src, '.js');

        $url = $this->themeUrl($path);

        if ($this->params->bool('tag')) {
            return '<script src="'.$url.'"></script>';
        }

        return $url;
    }

    /**
     * The {{ theme:css }} tag.
     *
     * @return string
     */
    public function css()
    {
        $src = $this->params->get('src', 'app');

        $path = 'css/'.Str::ensureRight($src, '.css');

        $url = $this->themeUrl($path);

        if ($this->params->bool('tag')) {
            return '<link rel="stylesheet" href="'.$url.'" />';
        }

        return $url;
    }

    /**
     * The {{ theme:output }} tag.
     *
     * Outputs the contents of the specified file.
     *
     * @return string
     */
    public function output()
    {
        $src = Path::tidy($this->params->get('src'));
        $disk = File::disk('resources');

        // Output nothing if the file doesn't exist or is outside the resources directory.
        if (! $disk->exists($src) || ! $disk->isWithinRoot($src)) {
            return '';
        }

        $contents = $disk->get($src);

        // If its a tag pair, the contents should be inserted into a variable.
        // {{ output_contents }} by default, but can be changed using `as`.
        if ($this->content) {
            return [$this->params->get('as', 'output_contents') => $contents];
        }

        return $contents;
    }

    private function themeUrl($path)
    {
        if ($this->params->bool('version')) {
            $pi = pathinfo($path);
            $path = $this->versioned($pi['extension'], $pi['filename']);
        }

        $url = URL::prependSiteUrl(
            $path,
            $this->params->get('locale', Config::getDefaultLocale()),
            false
        );

        if ($this->params->bool('cache_bust')) {
            throw_if(! File::exists($path = Path::tidy(public_path($path))), new \Exception("File $path does not exist."));
            $url .= '?v='.File::lastModified($path);
        }

        if (! $this->params->bool('absolute')) {
            $url = URL::makeRelative($url);
        }

        return $url;
    }

    private function versioned($type, $file)
    {
        $file = "{$type}/{$file}.{$type}";

        [$manifest, $method] = $this->getManifestAndMethod();

        if (! $manifest) {
            return '/'.$file;
        }

        $manifest = json_decode($manifest, true);

        // Mix prepends filenames with slashes.
        // We'll remove them to make it the same as Elixir.
        $manifest = collect($manifest)->mapWithKeys(function ($path, $key) {
            return [ltrim($key, '/') => ltrim($path, '/')];
        });

        if (! $manifest->has($file)) {
            return '/'.$file;
        }

        return $method === 'elixir'
            ? '/build/'.$manifest->get($file)
            : $manifest->get($file);
    }

    private function getManifestAndMethod()
    {
        if ($manifest = File::get(public_path('mix-manifest.json'))) {
            return [$manifest, 'mix'];
        }

        if ($manifest = File::get(public_path('build/rev-manifest.json'))) {
            return [$manifest, 'elixir'];
        }

        return [null, null];
    }
}
