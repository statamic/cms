<?php

namespace Tests\Search\Typesense;

use Nyholm\Psr7\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class FakeHttpClient implements ClientInterface
{
    public array $requests = [];
    private array $responses = [];

    public function respond(array|string $body = [], int $status = 200): self
    {
        $this->responses[] = new Response(
            $status,
            ['Content-Type' => 'application/json'],
            is_array($body) ? json_encode($body, JSON_THROW_ON_ERROR) : $body,
        );

        return $this;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->requests[] = [
            'method' => $request->getMethod(),
            'uri' => (string) $request->getUri(),
            'body' => (string) $request->getBody(),
            'headers' => $request->getHeaders(),
        ];

        if ($this->responses === []) {
            throw new \RuntimeException("No fake response queued for {$request->getMethod()} {$request->getUri()}.");
        }

        return array_shift($this->responses);
    }

    public function request(int $index): array
    {
        return $this->requests[$index];
    }

    public function json(int $index): array
    {
        return json_decode($this->request($index)['body'], true, flags: JSON_THROW_ON_ERROR);
    }
}
