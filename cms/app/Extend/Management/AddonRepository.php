<?php

namespace Statamic\Extend\Management;

use Statamic\API\Str;
use Statamic\API\Path;
use Statamic\API\Addon;
use Statamic\FileCollection;

class AddonRepository
{
    /**
     * @var FileCollection
     */
    private $files;

    /**
     * @param FileCollection $files  A collection of paths within the addon and bundle directories.
     */
    public function __construct(FileCollection $files)
    {
        $this->files = $files->values();
    }

    /**
     * Get the collection of files.
     *
     * @return FileCollection
     */
    public function files()
    {
        return $this->files;
    }

    /**
     * Get a collection of the file's equivalent class names.
     *
     * Non-PHP files will be stripped out.
     *
     * @return \Illuminate\Support\Collection
     */
    public function classes()
    {
        return $this->files->filter(function ($path) {
            return Str::endsWith($path, '.php');
        })->map(function ($path) {
            $class = preg_replace('/^(site\/addons|statamic\/bundles)/', '', $path);
            $class = str_replace(['/', '.php'], ['\\', ''], $class);
            return 'Statamic\Addons' . $class;
        })->values();
    }

    /**
     * Filter the files by first party bundled addons.
     *
     * @return static
     */
    public function firstParty()
    {
        $files = $this->files->filter(function ($path) {
            return Str::startsWith($path, 'statamic/bundles/');
        });

        return new static($files);
    }

    /**
     * Filter the files by third party addons.
     *
     * @return static
     */
    public function thirdParty()
    {
        $files = $this->files->filter(function ($path) {
            return Str::startsWith($path, 'site/addons/');
        });

        return new static($files);
    }

    /**
     * Get a collection of Addon objects.
     *
     * @return \Illuminate\Support\Collection
     */
    public function addons()
    {
        return collect($this->files)->map(function ($path) {
            $parts = explode('/', $path);
            return ['name' => $parts[2], 'firstParty' => $parts[1] === 'bundles'];
        })->groupBy(function ($arr) {
            return $arr['name'];
        })->map(function ($arr) {
            return Addon::create($arr[0]['name'])->isFirstParty($arr[0]['firstParty']);
        })->values();
    }

    /**
     * Filter by API classes.
     *
     * @return static
     */
    public function apis()
    {
        return $this->type('API');
    }

    /**
     * Filter by command classes.
     *
     * @return static
     */
    public function commands()
    {
        return $this->type('Command');
    }

    /**
     * Filter by controller classes.
     *
     * @return static
     */
    public function controllers()
    {
        return $this->type('Controller');
    }

    /**
     * Filter by fieldtype classes.
     *
     * @return static
     */
    public function fieldtypes()
    {
        return $this->type('Fieldtype');
    }

    /**
     * Filter by filter classes.
     *
     * @return static
     */
    public function filters()
    {
        return $this->type('Filter');
    }

    /**
     * Filter by event listener classes.
     *
     * @return static
     */
    public function listeners()
    {
        return $this->type('Listener');
    }

    /**
     * Filter by modifier classes.
     *
     * @return static
     */
    public function modifiers()
    {
        return $this->type('Modifier');
    }

    /**
     * Filter by service provider classes.
     *
     * @return static
     */
    public function serviceProviders()
    {
        return $this->type('ServiceProvider');
    }

    /**
     * Filter by tags classes.
     *
     * @return static
     */
    public function tags()
    {
        return $this->type('Tags');
    }

    /**
     * Filter by tasks classes.
     *
     * @return static
     */
    public function tasks()
    {
        return $this->type('Tasks');
    }

    /**
     * Filter by widget classes.
     *
     * @return static
     */
    public function widgets()
    {
        return $this->type('Widget');
    }

    /**
     * Filter by the filename
     *
     * @param string $filename  The filename to find.
     * @param string $directory  The directory to look inside, relative to the addon.
     * @return static
     */
    public function filename($filename, $directory = null)
    {
        $files = $this->files->filter(function ($path) use ($filename, $directory) {
            $relativePath = explode('/', $path, 4)[3];
            $directory = trim($directory, '/');

            return ($directory)
                ? $relativePath === Path::tidy($directory . '/' . $filename)
                : Str::endsWith($path, '/'.$filename);
        });

        return new static($files);
    }

    public function filenameRegex($regex)
    {
        return new static($this->files->filterByRegex($regex));
    }

    public function installed()
    {
        $files = $this->files->filter(function ($path) {
            $name = explode('/', $path)[2];
            return Addon::create($name)->isInstalled();
        });

        return new static($files);
    }

    /**
     * Filter by filenames ending with a particular type of PHP file.
     *
     * @return static
     */
    private function type($type)
    {
        $files = $this->files->filter(function ($path) use ($type) {
            return Str::endsWith($path, "$type.php");
        });

        return new static($files);
    }
}
