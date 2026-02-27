<?php

namespace Statamic\Imaging;

use Statamic\Exceptions\InvalidRemoteUrlException;
use Statamic\Support\Str;

class RemoteUrlValidator
{
    protected $resolver;

    public function __construct(?callable $resolver = null)
    {
        $this->resolver = $resolver ?? fn ($host) => dns_get_record($host, DNS_A + DNS_AAAA) ?: [];
    }

    public function parse($url)
    {
        $parsed = parse_url($url);

        if (! is_array($parsed)) {
            throw new InvalidRemoteUrlException('Invalid URL.');
        }

        $scheme = strtolower($parsed['scheme'] ?? '');

        if (! in_array($scheme, ['http', 'https'])) {
            throw new InvalidRemoteUrlException('Only http and https URLs are allowed.');
        }

        if (isset($parsed['user']) || isset($parsed['pass'])) {
            throw new InvalidRemoteUrlException('URLs with credentials are not allowed.');
        }

        $host = $parsed['host'] ?? null;

        if (! is_string($host) || $host === '') {
            throw new InvalidRemoteUrlException('URL host is required.');
        }

        $host = Str::lower(trim($host));

        if ($host !== trim($host, '.')) {
            throw new InvalidRemoteUrlException('Invalid URL host.');
        }

        if (! $this->isValidHost($host)) {
            throw new InvalidRemoteUrlException('Invalid URL host.');
        }

        $this->ensureHostResolvesToPublicIps($host);

        $port = isset($parsed['port']) ? ':'.$parsed['port'] : '';

        return [
            'path' => Str::after($parsed['path'] ?? '/', '/'),
            'base' => $scheme.'://'.$host.$port,
            'query' => $parsed['query'] ?? null,
        ];
    }

    public function validate($url)
    {
        $this->parse($url);
    }

    protected function isValidHost($host)
    {
        return filter_var($host, FILTER_VALIDATE_IP)
            || filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME);
    }

    protected function ensureHostResolvesToPublicIps($host)
    {
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $this->assertPublicIp($host);

            return;
        }

        $records = call_user_func($this->resolver, $host);
        $ips = collect($records)->flatMap(function ($record) {
            return [$record['ip'] ?? null, $record['ipv6'] ?? null];
        })->filter()->values()->all();

        if (empty($ips)) {
            throw new InvalidRemoteUrlException('Unable to resolve URL host.');
        }

        foreach ($ips as $ip) {
            $this->assertPublicIp($ip);
        }
    }

    protected function assertPublicIp($ip)
    {
        $result = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);

        if (! $result) {
            throw new InvalidRemoteUrlException('Destination IP is not publicly routable.');
        }
    }
}
