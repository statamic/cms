<?php

namespace Statamic\Tags;

use Illuminate\Foundation\Vite as LaravelVite;
use Statamic\Support\Str;

class Vite extends Tags
{
    /**
     * The {{ vite }} tag.
     *
     * @return string|array
     */
    public function index()
    {
        if (! $src = $this->params->explode('src')) {
            throw new \Exception('Please provide a source file.');
        }

        $directory = $this->params->get('directory', 'build');
        $hot = $this->params->get('hot');

        $attrs = $this->params
            ->filter(fn ($_, $key) => Str::startsWith($key, 'attr:'))
            ->keyBy(fn ($_, $key) => Str::after($key, 'attr:'))
            ->all();

        return $this->vite()
            ->withEntryPoints($src)
            ->useBuildDirectory($directory)
            ->useStyleTagAttributes($attrs)
            ->useScriptTagAttributes($attrs)
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

    private function vite()
    {
        return clone app(LaravelVite::class);
    }
}
