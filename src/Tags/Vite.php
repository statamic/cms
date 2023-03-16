<?php

namespace Statamic\Tags;

use Illuminate\Foundation\Vite as LaravelVite;

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

        return app(LaravelVite::class)
            ->withEntryPoints($src)
            ->useBuildDirectory($directory)
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

        return app(LaravelVite::class)
            ->useBuildDirectory($directory)
            ->useHotFile($hot ? base_path($hot) : null)
            ->asset($src);
    }
}
