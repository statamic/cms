<?php

namespace Statamic\Imaging;

use League\Glide\Responses\ResponseFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResponseFactory implements ResponseFactoryInterface
{
    /**
     * Request object to check "is not modified".
     *
     * @var Request|null
     */
    protected $request;

    /**
     * Create SymfonyResponseFactory instance.
     *
     * @param  Request|null  $request  Request object to check "is not modified".
     */
    public function __construct(Request $request = null)
    {
        $this->request = $request;
    }

    /**
     * Create the response.
     *
     * @param  FilesystemInterface  $cache  The cache file system.
     * @param  string  $path  The cached file path.
     * @return StreamedResponse The response object.
     */
    public function create($cache, $path)
    {
        $isFlysystemV1 = method_exists($cache, 'getTimestamp');

        // Determine whether or not to use Flysystem 1.x or 3.x getter methods.
        $mimeTypeMethod = $isFlysystemV1 ? 'getMimetype' : 'mimeType';
        $fileSizeMethod = $isFlysystemV1 ? 'getSize' : 'fileSize';
        $lastModifiedMethod = $isFlysystemV1 ? 'getTimestamp' : 'lastModified';

        $stream = $cache->readStream($path);

        $response = new StreamedResponse();
        $response->headers->set('Content-Type', $cache->{$mimeTypeMethod}($path));
        $response->headers->set('Content-Length', $cache->{$fileSizeMethod}($path));
        $response->setPublic();
        $response->setMaxAge(31536000);
        $response->setExpires(date_create()->modify('+1 years'));

        if ($this->request) {
            $response->setLastModified(date_create()->setTimestamp($cache->{$lastModifiedMethod}($path)));
            $response->isNotModified($this->request);
        }

        $response->setCallback(function () use ($stream) {
            if (ftell($stream) !== 0) {
                rewind($stream);
            }
            fpassthru($stream);
            fclose($stream);
        });

        return $response;
    }
}
