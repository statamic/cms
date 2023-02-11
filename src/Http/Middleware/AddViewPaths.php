<?php

namespace Statamic\Http\Middleware;

use Closure;
use Statamic\Facades\Site;

class AddViewPaths
{
    private $paths;
    private $hints;
    private $site;
    private $finder;

    public function handle($request, Closure $next)
    {
        $this->update();

        $response = $next($request);

        $this->restore();

        return $response;
    }

    private function update()
    {
        $this->finder = view()->getFinder();
        $this->site = Site::current()->handle();
        $this->paths = $this->finder->getPaths();
        $this->hints = $this->finder->getHints();

        $this->updatePaths();
        $this->updateHints();
    }

    private function updatePaths()
    {
        $site = $this->site;

        $paths = collect($this->paths)->flatMap(function ($path) use ($site) {
            return [
                $path.'/'.$site,
                $path,
            ];
        })->filter()->values()->all();

        $this->finder->setPaths($paths);
    }

    private function updateHints()
    {
        foreach ($this->hints as $namespace => $paths) {
            $paths = collect($paths)->flatMap(function ($path) {
                return [
                    $path.'/'.$this->site,
                    $path,
                ];
            })->values();

            $this->finder->replaceNamespace($namespace, $paths->all());
        }
    }

    private function restore()
    {
        $this->finder->setPaths($this->paths);

        foreach ($this->hints as $namespace => $paths) {
            $this->finder->replaceNamespace($namespace, $paths);
        }
    }
}
