<?php

namespace Statamic\Assets;

use Illuminate\Filesystem\FilesystemManager;
use Statamic\API\Config;
use Statamic\API\Parse;
use Statamic\API\Path;
use Statamic\API\URL;

class AssetContainerManager
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(FilesystemManager $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function resolveLocalPath($path)
    {
        return (Path::isAbsolute($path))
            ? $path
            : Path::resolve(realpath(statamic_path('../')) . '/' . $path);
    }

    /**
     * Get an absolute version of a given URL
     *
     * @param string $url
     * @return string|bool
     */
    public function getAbsoluteUrl($url)
    {
        return URL::makeAbsolute($url);
    }

    /**
     * Check if the URL of a given container exists
     *
     * Since a directory cannot reliably be detected as a URL, a
     * temporary file will be written, and we'll check for that directly.
     *
     * @param $url
     * @param $path
     * @return bool
     */
    public function urlExists($url, $path)
    {
        $filename = '/statamic-test-'.time();
        $tmp = $path . $filename;
        touch($tmp);

        $resolvedUrl = URL::assemble($this->getAbsoluteUrl($url), $filename);

        $headers = get_headers($resolvedUrl);

        unlink($tmp);

        return (!$headers || strpos($headers[0], '404')) == false;
    }

    /**
     * Get an Amazon S3 filesystem instance
     *
     * @param array $config  An array containing key, secret, region, bucket, and path.
     * @return
     */
    public function createS3Filesystem($config)
    {
        $config = $this->parseEnv($config);

        $config['root'] = array_get($config, 'path');

        return $this->filesystem->createS3Driver($config);
    }

    /**
     * Parse the keys in an array for environment variables
     *
     * @param array $data
     * @return static
     */
    private function parseEnv($data)
    {
        return collect($data)->map(function ($value) {
            return Parse::env($value);
        })->all();
    }
}
