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
        if (! $src = $this->params->get('src')) {
            throw new \Exception('Please provide a source file."');
        }

        return app(LaravelVite::class)->__invoke($src);
    }
}
