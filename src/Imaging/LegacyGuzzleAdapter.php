<?php

namespace Statamic\Imaging;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Uri;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use League\Flysystem\Util\MimeType;
use Psr\Http\Message\ResponseInterface;

/**
 * Uses Guzzle as a backend for HTTP URLs.
 *
 * This is for Flysystem v1.
 */
class LegacyGuzzleAdapter implements AdapterInterface
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
     * The visibility of this adapter.
     *
     * @var string
     */
    protected $visibility = AdapterInterface::VISIBILITY_PUBLIC;

    /**
     * Constructs a GuzzleAdapter object.
     *
     * @param  string  $base  The base URL.
     * @param  \GuzzleHttp\ClientInterface  $client  An optional Guzzle client.
     * @param  bool  $supportsHead  Whether the client supports HEAD requests.
     */
    public function __construct($base, ClientInterface $client = null, $supportsHead = true)
    {
        $this->base = rtrim($base, '/').'/';
        $this->client = $client ?: new Client();
        $this->supportsHead = $supportsHead;

        if (isset(parse_url($base)['user'])) {
            $this->visibility = AdapterInterface::VISIBILITY_PRIVATE;
        }
    }

    /**
     * Returns the base URL.
     *
     * @return string The base URL.
     */
    public function getBaseUrl()
    {
        return $this->base;
    }

    /**
     * {@inheritdoc}
     */
    public function copy($path, $newpath)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function createDir($path, Config $config)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($path)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDir($path)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($path)
    {
        if (! $response = $this->head($path)) {
            return false;
        }

        return [
            'type' => 'file',
            'path' => $path,
        ] + $this->getResponseMetadata($path, $response);
    }

    /**
     * {@inheritdoc}
     */
    public function getMimetype($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getSize($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getVisibility($path)
    {
        return [
            'path' => $path,
            'visibility' => $this->visibility,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function has($path)
    {
        return (bool) $this->head($path);
    }

    /**
     * {@inheritdoc}
     */
    public function listContents($directory = '', $recursive = false)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function read($path)
    {
        if (! $response = $this->get($path)) {
            return false;
        }

        return [
            'path' => $path,
            'contents' => (string) $response->getBody(),
        ] + $this->getResponseMetadata($path, $response);
    }

    /**
     * {@inheritdoc}
     */
    public function readStream($path)
    {
        if (! $response = $this->get($path)) {
            return false;
        }

        return [
            'path' => $path,
            'stream' => $response->getBody()->detach(),
        ] + $this->getResponseMetadata($path, $response);
    }

    /**
     * {@inheritdoc}
     */
    public function rename($path, $newpath)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function setVisibility($path, $visibility)
    {
        throw new \LogicException('GuzzleAdapter does not support visibility. Path: '.$path.', visibility: '.$visibility);
    }

    /**
     * {@inheritdoc}
     */
    public function update($path, $contents, Config $conf)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function updateStream($path, $resource, Config $config)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function write($path, $contents, Config $config)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function writeStream($path, $resource, Config $config)
    {
        return false;
    }

    /**
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
     * Returns the mimetype of a response.
     *
     * @param  string  $path
     * @param  \Psr\Http\Message\ResponseInterface  $response
     * @return string
     */
    protected function getMimetypeFromResponse($path, ResponseInterface $response)
    {
        if ($mimetype = $response->getHeader('Content-Type')) {
            [$mimetype] = explode(';', reset($mimetype), 2);

            return trim($mimetype);
        }

        // Try to guess from file extension.
        $uri = new Uri($path);

        return MimeType::detectByFilename($uri->getPath());
    }

    /**
     * Returns the metadata array for a response.
     *
     * @param  string  $path
     * @param  \Psr\Http\Message\ResponseInterface  $response
     * @return array
     */
    protected function getResponseMetadata($path, ResponseInterface $response)
    {
        $metadata = [
            'visibility' => $this->visibility,
            'mimetype' => $this->getMimetypeFromResponse($path, $response),
        ];

        if ($last_modified = $response->getHeader('Last-Modified')) {
            $last_modified = strtotime(reset($last_modified));

            if ($last_modified !== false) {
                $metadata['timestamp'] = $last_modified;
            }
        }

        if ($length = $response->getHeader('Content-Length')) {
            $length = reset($length);

            if (is_numeric($length)) {
                $metadata['size'] = (int) $length;
            }
        }

        return $metadata;
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
