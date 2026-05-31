<?php

namespace Statamic\Imaging;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\UnableToReadFile;
use Statamic\Exceptions\InvalidRemoteUrlException;

class GuzzleAdapter implements FilesystemAdapter
{
    /**
     * The maximum number of redirects to follow.
     *
     * @var int
     */
    const MAX_REDIRECTS = 5;

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
    public function __construct($base, ?ClientInterface $client = null)
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
            $response = $this->send('GET', $this->base.$path);
        } catch (BadResponseException $e) {
            return false;
        }

        return $response->getStatusCode() === 200 ? $response : false;
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
            $response = $this->send('HEAD', $this->base.$path);
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 405) {
                $this->supportsHead = false;

                return $this->get($path);
            }

            return false;
        } catch (BadResponseException $e) {
            return false;
        }

        return $response->getStatusCode() === 200 ? $response : false;
    }

    /**
     * Send a request, pinning the connection to the validated IP so the host
     * cannot be rebound to an internal address between validation and the
     * actual fetch. Redirects are followed manually so each hop is validated
     * and pinned too.
     */
    protected function send(string $method, string $url, int $redirects = 0)
    {
        // The connection is pinned to the validated IP via curl's CURLOPT_RESOLVE,
        // which the stream handler has no equivalent for. Rather than silently fall
        // back to an unpinned (rebindable) request, refuse to fetch without curl.
        if (! $this->supportsConnectionPinning()) {
            throw new \RuntimeException('The curl PHP extension is required to fetch remote images.');
        }

        $resolved = app(RemoteUrlValidator::class)->resolve($url);

        $response = $this->client->request($method, $url, [
            'allow_redirects' => false,
            'curl' => [
                CURLOPT_RESOLVE => [sprintf('%s:%d:%s', $resolved['host'], $resolved['port'], implode(',', $resolved['ips']))],
            ],
        ]);

        $status = $response->getStatusCode();

        if ($status >= 300 && $status < 400 && $response->hasHeader('Location')) {
            if ($redirects >= self::MAX_REDIRECTS) {
                throw new InvalidRemoteUrlException('Too many redirects.');
            }

            $location = UriResolver::resolve(new Uri($url), new Uri($response->getHeaderLine('Location')));

            return $this->send($method, (string) $location, $redirects + 1);
        }

        return $response;
    }

    protected function supportsConnectionPinning(): bool
    {
        return extension_loaded('curl');
    }
}
