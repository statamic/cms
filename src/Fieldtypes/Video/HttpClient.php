<?php

namespace Statamic\Fieldtypes\Video;

use Embera\Http\HttpClientInterface;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;

class HttpClient implements HttpClientInterface
{
    const CONNECT_TIMEOUT = 5;
    const TIMEOUT = 10;

    protected array $config = [];

    public function fetch($url, array $params = [])
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException(sprintf('Invalid url %s', $url));
        }

        $response = Http::withUserAgent($this->config['user_agent'] ?? 'Statamic')
            ->connectTimeout(self::CONNECT_TIMEOUT)
            ->timeout(self::TIMEOUT)
            ->withOptions(['allow_redirects' => ['max' => 3, 'strict' => true]])
            ->get($url);

        if ($response->failed()) {
            throw new RuntimeException(sprintf('Request to %s returned status %s', $url, $response->status()));
        }

        return $response->body();
    }

    public function setConfig(array $config = [])
    {
        $this->config = $config;
    }
}
