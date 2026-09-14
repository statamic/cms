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
        $components = $this->validatedComponents($url);

        return [
            'path' => Str::after($components['path'], '/'),
            'base' => $components['scheme'].'://'.$components['host'].$components['port_suffix'],
            'query' => $components['query'],
        ];
    }

    public function validate($url)
    {
        $this->parse($url);
    }

    /**
     * Resolve and validate the URL host, returning the host, port, and the
     * validated public IPs it resolves to. These IPs are intended to be pinned
     * to the actual connection so the host cannot be rebound to an internal
     * address between this check and the request being made.
     */
    public function resolve($url)
    {
        $components = $this->validatedComponents($url);

        return [
            'host' => $components['host'],
            'port' => $components['port'],
            'ips' => $components['ips'],
        ];
    }

    protected function validatedComponents($url)
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

        $ips = $this->ensureHostResolvesToPublicIps($host);

        return [
            'scheme' => $scheme,
            'host' => $host,
            'port' => $parsed['port'] ?? ($scheme === 'https' ? 443 : 80),
            'port_suffix' => isset($parsed['port']) ? ':'.$parsed['port'] : '',
            'path' => $parsed['path'] ?? '/',
            'query' => $parsed['query'] ?? null,
            'ips' => $ips,
        ];
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

            return [$host];
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

        return $ips;
    }

    protected function assertPublicIp($ip)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $packed = inet_pton($ip);
            if ($packed !== false && substr($packed, 0, 12) === "\0\0\0\0\0\0\0\0\0\0\xff\xff") {
                $ip = inet_ntop(substr($packed, 12));
            }
        }

        $result = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);

        if (! $result) {
            throw new InvalidRemoteUrlException('Destination IP is not publicly routable.');
        }
    }
}
