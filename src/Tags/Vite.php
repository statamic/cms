<?php

namespace Statamic\Tags;

use Illuminate\Foundation\Vite as LaravelVite;
use Illuminate\Support\Arr;

class Vite extends Tags
{
    /**
     * The {{ vite }} tag.
     *
     * @return string|array
     */
    public function index()
    {
        $asset = $this->params->explode('asset');

        if ($asset) {
            return app(LaravelVite::class)
                ->asset(Arr::first($asset));
        }

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
}
