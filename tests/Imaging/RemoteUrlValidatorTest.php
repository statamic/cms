<?php

namespace Tests\Imaging;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Exceptions\InvalidRemoteUrlException;
use Statamic\Imaging\RemoteUrlValidator;
use Tests\TestCase;

class RemoteUrlValidatorTest extends TestCase
{
    private function validator(?callable $resolver = null): RemoteUrlValidator
    {
        return new RemoteUrlValidator($resolver ?? fn ($host) => match ($host) {
            'example.com' => [['ip' => '93.184.216.34']],
            default => [],
        });
    }

    #[Test]
    public function it_resolves_the_host_port_and_validated_ips()
    {
        $this->assertSame([
            'host' => 'example.com',
            'port' => 443,
            'ips' => ['93.184.216.34'],
        ], $this->validator()->resolve('https://example.com/foo.jpg'));
    }

    #[Test]
    public function it_defaults_the_port_based_on_the_scheme()
    {
        $this->assertSame(443, $this->validator()->resolve('https://example.com/foo.jpg')['port']);
        $this->assertSame(80, $this->validator()->resolve('http://example.com/foo.jpg')['port']);
    }

    #[Test]
    public function it_preserves_an_explicit_port()
    {
        $this->assertSame(8080, $this->validator()->resolve('http://example.com:8080/foo.jpg')['port']);
    }

    #[Test]
    public function it_returns_every_resolved_ip_so_they_can_all_be_pinned()
    {
        $resolved = $this->validator(fn () => [
            ['ip' => '93.184.216.34'],
            ['ip' => '93.184.216.35', 'ipv6' => '2606:4700:4700::1111'],
        ])->resolve('https://example.com/foo.jpg');

        $this->assertSame(['93.184.216.34', '93.184.216.35', '2606:4700:4700::1111'], $resolved['ips']);
    }

    #[Test]
    public function it_resolves_an_ip_literal_host_to_itself_without_using_the_resolver()
    {
        $resolved = $this->validator(function () {
            throw new \Exception('The resolver should not be called for an IP literal.');
        })->resolve('https://93.184.216.34/foo.jpg');

        $this->assertSame([
            'host' => '93.184.216.34',
            'port' => 443,
            'ips' => ['93.184.216.34'],
        ], $resolved);
    }

    #[Test]
    public function it_lowercases_the_host()
    {
        $this->assertSame('example.com', $this->validator()->resolve('https://Example.COM/foo.jpg')['host']);
    }

    #[Test]
    public function it_blocks_hosts_that_resolve_to_non_public_ips()
    {
        $this->expectException(InvalidRemoteUrlException::class);
        $this->expectExceptionMessage('Destination IP is not publicly routable.');

        $this->validator(fn () => [['ip' => '127.0.0.1']])->resolve('https://internal.test/foo.jpg');
    }

    #[Test]
    public function it_blocks_ip_literals_in_non_public_ranges()
    {
        $this->expectException(InvalidRemoteUrlException::class);
        $this->expectExceptionMessage('Destination IP is not publicly routable.');

        $this->validator()->resolve('http://169.254.169.254/latest/meta-data/');
    }

    #[Test]
    #[DataProvider('nonPublicIpv6Provider')]
    public function it_blocks_hosts_that_resolve_to_non_public_ipv6_addresses($ip)
    {
        $this->expectException(InvalidRemoteUrlException::class);
        $this->expectExceptionMessage('Destination IP is not publicly routable.');

        $this->validator(fn () => [['ipv6' => $ip]])->resolve('https://internal.test/foo.jpg');
    }

    public static function nonPublicIpv6Provider()
    {
        return [
            'loopback' => ['::1'],
            'unspecified' => ['::'],
            'link-local' => ['fe80::1'],
            'unique local' => ['fd00:ec2::254'],
            'ipv4-mapped private' => ['::ffff:10.0.0.1'],
            'ipv4-mapped loopback' => ['::ffff:127.0.0.1'],
            'siit private' => ['::ffff:0:a00:1'],
            'siit loopback' => ['::ffff:0:7f00:1'],
            'nat64 10/8' => ['64:ff9b::a00:1'],
            'nat64 172.16/12' => ['64:ff9b::ac10:1'],
            'nat64 192.168/16' => ['64:ff9b::c0a8:1'],
            'nat64 127/8' => ['64:ff9b::7f00:1'],
            'nat64 169.254/16' => ['64:ff9b::a9fe:a9fe'],
            'nat64 dotted private' => ['64:ff9b::10.0.0.1'],
            'nat64 local-use' => ['64:ff9b:1::808:808'],
            '6to4' => ['2002:808:808::1'],
            'teredo' => ['2001:0:4136:e378:8000:63bf:3fff:fdd2'],
            'ipv4-compatible private' => ['::a00:1'],
            'ipv4-compatible public' => ['::808:808'],
            'discard-only' => ['100::1'],
        ];
    }

    #[Test]
    #[DataProvider('publicIpv6Provider')]
    public function it_allows_hosts_that_resolve_to_public_ipv6_addresses($ip)
    {
        $this->assertSame([$ip], $this->validator(fn () => [['ipv6' => $ip]])->resolve('https://example.com/foo.jpg')['ips']);
    }

    public static function publicIpv6Provider()
    {
        return [
            'cloudflare' => ['2606:4700::1111'],
            'google' => ['2001:4860:4860::8888'],
            'ipv4-mapped public' => ['::ffff:8.8.8.8'],
            'nat64 public' => ['64:ff9b::808:808'],
        ];
    }

    #[Test]
    public function it_blocks_ipv6_literal_hosts()
    {
        $this->expectException(InvalidRemoteUrlException::class);
        $this->expectExceptionMessage('Invalid URL host.');

        $this->validator()->resolve('http://[64:ff9b::a00:1]/foo.jpg');
    }

    #[Test]
    public function it_blocks_hosts_that_cannot_be_resolved()
    {
        $this->expectException(InvalidRemoteUrlException::class);
        $this->expectExceptionMessage('Unable to resolve URL host.');

        $this->validator(fn () => [])->resolve('https://unknown.test/foo.jpg');
    }

    #[Test]
    public function it_blocks_urls_with_credentials()
    {
        $this->expectException(InvalidRemoteUrlException::class);
        $this->expectExceptionMessage('URLs with credentials are not allowed.');

        $this->validator()->resolve('https://user:pass@example.com/foo.jpg');
    }

    #[Test]
    public function it_blocks_non_http_schemes()
    {
        $this->expectException(InvalidRemoteUrlException::class);
        $this->expectExceptionMessage('Only http and https URLs are allowed.');

        $this->validator()->resolve('file:///etc/passwd');
    }

    #[Test]
    public function it_parses_the_base_path_and_query()
    {
        $this->assertSame([
            'path' => 'foo/bar.jpg',
            'base' => 'https://example.com',
            'query' => 'w=100',
        ], $this->validator()->parse('https://example.com/foo/bar.jpg?w=100'));
    }

    #[Test]
    public function it_includes_an_explicit_port_in_the_parsed_base()
    {
        $this->assertSame('http://example.com:8080', $this->validator()->parse('http://example.com:8080/foo.jpg')['base']);
    }
}
