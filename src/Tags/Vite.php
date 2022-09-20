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

        $directory = $this->params->get('directory');
        $hot = $this->params->get('hot');

        return app(LaravelVite::class)
            ->withEntryPoints($src)
            ->useBuildDirectory($directory)
            ->useHotFile($hot)
            ->toHtml();
    }
}
