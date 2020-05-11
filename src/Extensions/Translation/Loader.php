<?php

namespace Statamic\Extensions\Translation;

use Illuminate\Contracts\Translation\Loader as LoaderContract;

/**
 * This class is essentially a decorator that keeps track of namespaces and paths,
 * since Statamic will want to loop over them to output from within Javascript.
 * Laravel's implementation doesn't expose them. This is a decorator and not
 * a subclass so that custom loaders can continue to function.
 */
class Loader implements LoaderContract
{
    protected $loader;
    protected $path;
    protected $hints;
    protected $jsonPaths;

    public function __construct(LoaderContract $loader, $path)
    {
        $this->loader = $loader;
        $this->path = $path;
    }

    public function load($locale, $group, $namespace = null)
    {
        return $this->loader->load($locale, $group, $namespace);
    }

    public function addNamespace($namespace, $hint)
    {
        $this->hints[$namespace] = $hint;

        return $this->loader->addNamespace($namespace, $hint);
    }

    public function addJsonPath($path)
    {
        $this->jsonPaths[] = $path;

        return $this->loader->addJsonPath($path);
    }

    public function namespaces()
    {
        return $this->loader->namespaces();
    }

    public function paths()
    {
        return array_merge($this->hints, [
            '*' => $this->path,
        ]);
    }
}
