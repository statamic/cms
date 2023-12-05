<?php

namespace Statamic\Imaging;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\UnableToReadFile;

class GuzzleAdapter implements FilesystemAdapter
{
    /**
     * Whether this endpoint supports head requests.
     *
     * @var bool
     */
    protected $supportsHead = true;

    /**
     * The base URL.
     *
     * @var string
     */
    protected $base;

    /**
     * The Guzzle HTTP client.
     *
     * @var \GuzzleHttp\ClientInterface
     */
    protected $client;

    /**
     * Constructs a GuzzleAdapter object.
     *
     * @param  string  $base  The base URL.
     * @param  \GuzzleHttp\ClientInterface  $client  An optional Guzzle client.
     */
    public function __construct($base, ClientInterface $client = null)
    {
        $this->base = rtrim($base, '/').'/';
        $this->client = $client ?: new Client();
    }

    public function fileExists(string $location): bool
    {
        return (bool) $this->head($location);
    }

    public function directoryExists(string $location): bool
    {
        return $this->fileExists($location);
    }

    public function read(string $path): string
    {
        if (! $response = $this->get($path)) {
            throw UnableToReadFile::fromLocation($path);
        }

        return (string) $response->getBody();
    }

    public function readStream($path)
    {
        if (! $response = $this->get($path)) {
            throw UnableToReadFile::fromLocation($path);
        }

        return $response->getBody()->detach();
    }

    public function setVisibility(string $path, string $visibility): void
    {
        //
    }

    public function write(string $path, string $contents, Config $config): void
    {
        //
    }

    public function writeStream(string $path, $contents, Config $config): void
    {
        //
    }

    public function mimeType(string $path): FileAttributes
    {
        //
    }

    public function lastModified(string $path): FileAttributes
    {
        //
    }

    public function fileSize(string $path): FileAttributes
    {
        //
    }

    public function listContents(string $path, bool $deep): iterable
    {
        //
    }

    public function move(string $source, string $destination, Config $config): void
    {
        //
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        //
    }

    public function createDirectory(string $path, Config $config): void
    {
        //
    }

    public function delete(string $path): void
    {
        //
    }

    public function deleteDirectory(string $prefix): void
    {
        //
    }

    public function visibility(string $path): FileAttributes
    {
        //
    }

    /*
     * Performs a GET request.
     *
     * @param  string  $path  The path to GET.
     * @return \GuzzleHttp\Psr7\Response|false The response or false if failed.
     */
    protected function get($path)
    {
        try {
            $response = $this->client->get($this->base.$path);
        } catch (BadResponseException $e) {
            return false;
        }

        if ($response->getStatusCode() !== 200) {
            return false;
        }

        return $response;
    }

    /**
     * Performs a HEAD request.
     *
     * @param  string  $path  The path to HEAD.
     * @return \GuzzleHttp\Psr7\Response|false The response or false if failed.
     */
    protected function head($path)
    {
        if (! $this->supportsHead) {
            return $this->get($path);
        }

        try {
            $response = $this->client->head($this->base.$path);
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 405) {
                $this->supportsHead = false;

                return $this->get($path);
            }

            return false;
        } catch (BadResponseException $e) {
            return false;
        }

        if ($response->getStatusCode() !== 200) {
            return false;
        }

        return $response;
    }
}
