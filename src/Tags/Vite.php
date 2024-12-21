<?php

namespace Statamic\Tags;

use Illuminate\Foundation\Vite as LaravelVite;
use Statamic\Support\Str;

class Vite extends Tags
{
    /**
     * Static flag to ensure assets are rendered only once per request.
     */
    private static $hasRendered = false; // Add this static flag

    /**
     * The {{ vite }} tag.
     *
     * @return string|array
     */
    public function index()
    {
        // Check if the Vite tag has already been rendered
        if (self::$hasRendered) {
            return ''; // Prevent further rendering
        }

        self::$hasRendered = true; // Set the flag to true after first render

        if (! $src = $this->params->explode('src')) {
            throw new \Exception('Please provide a source file.');
        }

        $directory = $this->params->get('directory', 'build');
        $hot = $this->params->get('hot');

        [$scriptAttrs, $styleAttrs] = $this->parseAttrs();

        return $this->vite()
            ->withEntryPoints($src)
            ->useBuildDirectory($directory)
            ->useStyleTagAttributes($styleAttrs)
            ->useScriptTagAttributes($scriptAttrs)
            ->useHotFile($hot ? base_path($hot) : null)
            ->toHtml();
    }

    /**
     * The {{ vite:asset src="" }} tag.
     *
     * @return string
     */
    public function asset()
    {
        if (! $src = $this->params->get('src')) {
            throw new \Exception('Please provide a source file.');
        }

        $directory = $this->params->get('directory', 'build');
        $hot = $this->params->get('hot');

        return $this->vite()
            ->useBuildDirectory($directory)
            ->useHotFile($hot ? base_path($hot) : null)
            ->asset($src);
    }

    /**
     * The {{ vite:content }} tag.
     *
     * @return string
     */
    public function content()
    {
        if (! $src = $this->params->get('src')) {
            throw new \Exception('Please provide a source file.');
        }

        $directory = $this->params->get('directory', 'build');

        return $this->vite()
            ->useBuildDirectory($directory)
            ->content($src);
    }

    private function vite()
    {
        return clone app(LaravelVite::class);
    }

    private function parseAttrs()
    {
        $script = collect();
        $style = collect();

        $attrs = $this->params
            ->filter(fn ($_, $key) => Str::startsWith($key, 'attr:'))
            ->keyBy(fn ($_, $key) => Str::after($key, 'attr:'))
            ->filter(function ($value, $key) use ($script, $style) {
                if (Str::startsWith($key, 'script:')) {
                    $script->put(Str::after($key, 'script:'), $value);
                } elseif (Str::startsWith($key, 'style:')) {
                    $style->put(Str::after($key, 'style:'), $value);
                } else {
                    return true;
                }
            });

        return [
            $attrs->merge($script)->all(),
            $attrs->merge($style)->all(),
        ];
    }
}
