<?php

namespace Statamic\Extensions\Translation;

use Illuminate\Contracts\Translation\Loader;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\Translator as BaseTranslator;
use Statamic\Statamic;

class Translator extends BaseTranslator
{
    private $files;

    public function __construct(Filesystem $files, Loader $loader, $locale)
    {
        $this->files = $files;
        parent::__construct($loader, $locale);
    }

    public function parseKey($key)
    {
        if (Statamic::isCpRoute() && starts_with($key, 'validation.')) {
            $key = 'statamic::'.$key;
        }

        return parent::parseKey($key);
    }

    /**
     * Get the translations in a JSON object suitable for the Control Panel's translator.
     *
     * @return array
     */
    public function toJson()
    {
        // Get all the translations organized by hint::file
        $translations = collect($this->loader->paths())->mapWithKeys(function ($path, $namespace) {
            // The default (un-hinted) translations come back with an asterisk as the namespace.
            $namespace = ($namespace === '*') ? null : $namespace;

            return $this->getTranslations($path, $namespace);
        });

        // Add the JSON translations into an asterisk since there's one file for the whole locale.
        $translations->put('*', $this->loader->load($this->locale, '*', '*'));

        // The Javascript side is expecting one flattened object.
        return array_dot($translations);
    }

    protected function getTranslations($path, $namespace)
    {
        return $this->phpFiles($path)->mapWithKeys(function ($name) use ($namespace) {
            $key = ltrim($namespace.'::'.$name, ':');
            $value = $this->loader->load($this->locale, $name, $namespace);

            return [$key => $value];
        })->all();
    }

    protected function phpFiles($path)
    {
        if (! $this->files->exists($path)) {
            return collect();
        }

        return collect($this->files->allFiles($path))
            ->filter(function ($file) {
                return $file->getExtension() === 'php';
            })->map->getBasename('.php');
    }
}
