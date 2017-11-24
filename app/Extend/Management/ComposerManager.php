<?php

namespace Statamic\Extend\Management;

use Statamic\API\File;
use Statamic\API\Str;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ComposerManager
{
    /**
     * Path to the composer.json file
     *
     * @var string
     */
    protected $path;

    /**
     * Read the contents of a composer.json
     *
     * @param string $filename
     * @return array
     */
    public function read($filename = 'composer.json')
    {
        return json_decode(File::get($this->path().'/'.$filename), true);
    }

    /**
     * Read the contents of a composer.json.original if it exists,
     * otherwise just the composer.json.
     *
     * @return array
     */
    public function readOriginal()
    {
        if (! File::exists($this->path().'/composer.json.original')) {
            return $this->read();
        }

        return $this->read('composer.json.original');
    }

    /**
     * Save an array to a composer.json
     *
     * @param array $contents
     * @return mixed
     */
    public function save($contents)
    {
        // First, create a backup of the original
        if (! File::exists($this->path().'/composer.json.original')) {
            File::put(
                $this->path().'/composer.json.original',
                json_encode($this->read(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            );
        }

        File::put(
            $this->path().'/composer.json',
            json_encode($contents, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    /**
     * Get or set the path to the composer.json
     *
     * @param string|null $path
     * @return mixed
     */
    public function path($path = null)
    {
        if (is_null($path)) {
            return $this->path ?: statamic_path();
        }

        $this->path = Str::removeRight($path, 'composer.json');
    }

    /**
     * Run composer update
     *
     * @param array|null $packages Packages to specifically update
     * @return mixed
     */
    public function update($packages = null)
    {
        $packages = join(' ', $packages);

        $command = sprintf('php composer.phar update %s --prefer-dist --no-dev --optimize-autoloader', $packages);

        $process = new Process($command, $this->path(), array_merge($_SERVER, [
            'COMPOSER_HOME' => local_path('composer')
        ]));

        $process->setTimeout(null);

        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return true;
    }
}
