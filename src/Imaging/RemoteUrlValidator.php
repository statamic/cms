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
        if (! filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new InvalidRemoteUrlException('Destination IP is not publicly routable.');
        }

        $packed = inet_pton($ip);

        if ($this->matchesAnyPrefix($packed, $this->embeddedIpv4Prefixes())) {
            $packed = substr($packed, 12);
            $ip = inet_ntop($packed);
        }

        $public = strlen($packed) === 4 ? $this->isPublicIpv4($packed) : $this->isPublicIpv6($packed);

        if (! $public || ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            throw new InvalidRemoteUrlException('Destination IP is not publicly routable.');
        }
    }

    protected function isPublicIpv4($packed)
    {
        return ! $this->matchesAnyPrefix($packed, $this->blockedIpv4Prefixes());
    }

    protected function isPublicIpv6($packed)
    {
        return $this->matchesAnyPrefix($packed, $this->globalIpv6Prefixes())
            && ! $this->matchesAnyPrefix($packed, $this->blockedIpv6Prefixes());
    }

    protected function embeddedIpv4Prefixes()
    {
        return [
            '::ffff:0:0/96', // IPv4-mapped
            '::ffff:0:0:0/96', // SIIT
            '64:ff9b::/96', // NAT64 well-known prefix
        ];
    }

    protected function blockedIpv4Prefixes()
    {
        return [
            '0.0.0.0/8', // "This network"
            '10.0.0.0/8', // Private
            '100.64.0.0/10', // Shared address space (CGNAT)
            '127.0.0.0/8', // Loopback
            '169.254.0.0/16', // Link-local
            '172.16.0.0/12', // Private
            '192.0.0.0/24', // IETF protocol assignments
            '192.0.2.0/24', // TEST-NET-1
            '192.88.99.0/24', // Deprecated 6to4 relay anycast
            '192.168.0.0/16', // Private
            '198.18.0.0/15', // Benchmarking
            '198.51.100.0/24', // TEST-NET-2
            '203.0.113.0/24', // TEST-NET-3
            '224.0.0.0/4', // Multicast
            '240.0.0.0/4', // Reserved, including broadcast
        ];
    }

    protected function globalIpv6Prefixes()
    {
        return [
            '2000::/3', // Global unicast
        ];
    }

    protected function blockedIpv6Prefixes()
    {
        return [
            '2001::/23', // IETF protocol assignments, including Teredo
            '2001:db8::/32', // Documentation
            '2002::/16', // 6to4
            '3fff::/20', // Documentation
        ];
    }

    protected function matchesAnyPrefix($packed, array $prefixes)
    {
        foreach ($prefixes as $prefix) {
            [$network, $bits] = explode('/', $prefix);
            $network = inet_pton($network);
            $bytes = intdiv((int) $bits, 8);
            $remainder = (int) $bits % 8;

            if (strlen($packed) !== strlen($network) || substr($packed, 0, $bytes) !== substr($network, 0, $bytes)) {
                continue;
            }

            if ($remainder === 0) {
                return true;
            }

            $mask = (0xFF << (8 - $remainder)) & 0xFF;

            if ((ord($packed[$bytes]) & $mask) === (ord($network[$bytes]) & $mask)) {
                return true;
            }
        }

        return false;
    }
}
