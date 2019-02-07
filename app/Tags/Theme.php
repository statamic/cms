<?php

namespace Statamic\Tags;

use Statamic\API\Str;
use Statamic\API\URL;
use Statamic\API\File;
use Statamic\API\Path;
use Statamic\API\Parse;
use Statamic\Tags\Tags;
use Statamic\API\Config;

class Theme extends Tags
{
    /**
     * Catch-all for dynamic theme tags.
     *
     * Namespaces the `src` to the specified directory.
     * eg. {{ theme:img }}, {{ theme:svg }}, etc.
     *
     * @param string $method    Tag part
     * @param array  $arguments Unused
     * @return string
     */
    public function __call($method, $arguments)
    {
        return $this->path($method);
    }

    private function path($dir = null)
    {
        $src = $this->get('src');

        $path = $dir . '/' . $src;

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
        $src = $this->get('src');

        $path = 'img/' . $src;

        $url = $this->themeUrl($path);

        $alt = $this->get('alt');

        if ($this->getBool('tag')) {
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
        $src = $this->get('src', 'app');

        $path = 'js/' . Str::ensureRight($src, '.js');

        $url = $this->themeUrl($path);

        if ($this->getBool('tag')) {
            return '<script src="' . $url . '"></script>';
        }

        return $url;
    }

    /**
     * The {{ theme:css }} tag
     *
     * @return string
     */
    public function css()
    {
        $src = $this->get('src', 'app');

        $path = 'css/' . Str::ensureRight($src, '.css');

        $url = $this->themeUrl($path);

        if ($this->getBool('tag')) {
            return '<link rel="stylesheet" href="' . $url . '" />';
        }

        return $url;
    }

    /**
     * The {{ theme:partial }} tag
     *
     * Renders a partial template
     *
     * @return string
     */
    public function partial()
    {
        $src = $this->get('src');

        $partialPath = config('statamic.theming.dedicated_view_directories')
            ? resource_path("partials/{$src}.antlers")
            : resource_path("views/{$src}.antlers");

        if (! $partial = File::get($partialPath.'.html')) {
            if ($partial = File::get($partialPath.'.php')) {
                $php = true;
            }
        }

        // Allow front matter in these suckers
        $parsed = Parse::frontMatter($partial);
        $variables = array_get($parsed, 'data', []);
        $template = array_get($parsed, 'content');

        // Front-matter, tag parameters, and the context is all passed through to the partial.
        // Since 2.5, parameters need to be prefixed with a colon in order to read from the field.
        $variables = array_merge($this->context, $variables, $this->parameters);

        return Parse::template($template, $variables, [], $php ?? false);
    }

    /**
     * The {{ theme:output }} tag
     *
     * Outputs the contents of the specified file.
     *
     * @return string
     */
    public function output()
    {
        $src = $this->get('src');

        // Output nothing if the file doesn't exist.
        if (! File::disk('resources')->exists($src)) {
            return '';
        }

        $contents = File::disk('resources')->get($src);

        // If its a tag pair, the contents should be inserted into a variable.
        // {{ output_contents }} by default, but can be changed using `as`.
        if ($this->content) {
            return [$this->get('as', 'output_contents') => $contents];
        }

        return $contents;
    }

    private function themeUrl($path)
    {
        if ($this->getBool('version')) {
            $pi = pathinfo($path);
            $path = $this->versioned($pi['extension'], $pi['filename']);
        }

        $url = URL::prependSiteUrl(
            $path,
            $this->get('locale', default_locale()),
            false
        );

        if ($this->getBool('cache_bust')) {
            $url .= '?v=' . File::lastModified(public_path($path));
        }

        if (! $this->getBool('absolute')) {
            $url = URL::makeRelative($url);
        }

        return $url;
    }

    private function versioned($type, $file)
    {
        list($manifest, $method) = $this->getManifestAndMethod();
        $manifest = json_decode($manifest, true);

        // Mix prepends filenames with slashes.
        // We'll remove them to make it the same as Elixir.
        $manifest = collect($manifest)->mapWithKeys(function ($path, $key) {
            return [ltrim($key, '/') => ltrim($path, '/')];
        });

        if (! $manifest->has($file = "{$type}/{$file}.{$type}")) {
            return '/' . $file;
        }

        return $method === 'elixir'
            ? '/build/' . $manifest->get($file)
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
