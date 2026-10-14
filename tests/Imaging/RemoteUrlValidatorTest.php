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
            'ietf protocol assignments' => ['2001:1ff::1'],
            'documentation' => ['2001:db8::1'],
            'documentation 3fff::/20 start' => ['3fff::1'],
            'documentation 3fff::/20 end' => ['3fff:fff:ffff::1'],
            'srv6 sids' => ['5f00::1'],
            'link-local end' => ['febf::1'],
            'site-local start' => ['fec0::1'],
            'site-local end' => ['feff::1'],
            'multicast' => ['ff02::1'],
            'ipv4-mapped shared address space' => ['::ffff:100.64.0.1'],
            'ipv4-mapped benchmarking' => ['::ffff:198.18.0.1'],
            'siit test-net-1' => ['::ffff:0:c000:201'],
            'nat64 shared address space' => ['64:ff9b::6440:1'],
            'nat64 ietf protocol assignments' => ['64:ff9b::c000:1'],
            'nat64 test-net-3' => ['64:ff9b::cb00:7101'],
            'nat64 multicast' => ['64:ff9b::e000:1'],
            'below global unicast' => ['1::1'],
            'end of 0::/3' => ['1fff:ffff::1'],
            'above global unicast' => ['4000::1'],
            'outside global unicast near srv6 sids' => ['5f01::1'],
            'unassigned e000::/4' => ['e000::1'],
            'unassigned fe00::/9' => ['fe00::1'],
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
            'just outside ietf protocol assignments' => ['2001:200::1'],
            'just outside documentation' => ['2001:db9::1'],
            'just outside 3fff::/20' => ['3fff:1000::1'],
            'start of global unicast' => ['2000::1'],
        ];
    }

    #[Test]
    #[DataProvider('nonPublicIpv4Provider')]
    public function it_blocks_hosts_that_resolve_to_non_public_ipv4_addresses($ip)
    {
        $this->expectException(InvalidRemoteUrlException::class);
        $this->expectExceptionMessage('Destination IP is not publicly routable.');

        $this->validator(fn () => [['ip' => $ip]])->resolve('https://internal.test/foo.jpg');
    }

    #[Test]
    #[DataProvider('nonPublicIpv4Provider')]
    public function it_blocks_non_public_ipv4_literal_hosts($ip)
    {
        $this->expectException(InvalidRemoteUrlException::class);
        $this->expectExceptionMessage('Destination IP is not publicly routable.');

        $this->validator()->resolve("https://{$ip}/foo.jpg");
    }

    public static function nonPublicIpv4Provider()
    {
        return [
            'shared address space start' => ['100.64.0.0'],
            'shared address space end' => ['100.127.255.255'],
            'ietf protocol assignments' => ['192.0.0.8'],
            'test-net-1' => ['192.0.2.1'],
            'benchmarking start' => ['198.18.0.0'],
            'benchmarking end' => ['198.19.255.255'],
            'test-net-2' => ['198.51.100.1'],
            'test-net-3' => ['203.0.113.1'],
            'multicast start' => ['224.0.0.1'],
            'multicast end' => ['239.255.255.255'],
            'this network start' => ['0.0.0.0'],
            'this network end' => ['0.255.255.255'],
            'private 10/8 start' => ['10.0.0.0'],
            'private 10/8 end' => ['10.255.255.255'],
            'loopback start' => ['127.0.0.0'],
            'loopback end' => ['127.255.255.255'],
            'link-local start' => ['169.254.0.0'],
            'link-local end' => ['169.254.255.255'],
            'private 172.16/12 start' => ['172.16.0.0'],
            'private 172.16/12 end' => ['172.31.255.255'],
            '6to4 relay anycast' => ['192.88.99.1'],
            'private 192.168/16 start' => ['192.168.0.0'],
            'private 192.168/16 end' => ['192.168.255.255'],
            'reserved start' => ['240.0.0.0'],
            'broadcast' => ['255.255.255.255'],
        ];
    }

    #[Test]
    #[DataProvider('publicIpv4Provider')]
    public function it_allows_hosts_that_resolve_to_public_ipv4_addresses($ip)
    {
        $this->assertSame([$ip], $this->validator(fn () => [['ip' => $ip]])->resolve('https://example.com/foo.jpg')['ips']);
    }

    #[Test]
    #[DataProvider('publicIpv4Provider')]
    public function it_allows_public_ipv4_literal_hosts($ip)
    {
        $this->assertSame([$ip], $this->validator()->resolve("https://{$ip}/foo.jpg")['ips']);
    }

    public static function publicIpv4Provider()
    {
        return [
            'below shared address space' => ['100.63.255.255'],
            'above shared address space' => ['100.128.0.0'],
            'below ietf protocol assignments' => ['191.255.255.255'],
            'between ietf protocol assignments and test-net-1' => ['192.0.1.1'],
            'above test-net-1' => ['192.0.3.0'],
            'below benchmarking' => ['198.17.255.255'],
            'above benchmarking' => ['198.20.0.0'],
            'below test-net-2' => ['198.51.99.255'],
            'above test-net-2' => ['198.51.101.0'],
            'below test-net-3' => ['203.0.112.255'],
            'above test-net-3' => ['203.0.114.0'],
            'below multicast' => ['223.255.255.255'],
            'above this network' => ['1.0.0.0'],
            'below private 10/8' => ['9.255.255.255'],
            'above private 10/8' => ['11.0.0.0'],
            'below loopback' => ['126.255.255.255'],
            'above loopback' => ['128.0.0.0'],
            'below link-local' => ['169.253.255.255'],
            'above link-local' => ['169.255.0.0'],
            'below private 172.16/12' => ['172.15.255.255'],
            'above private 172.16/12' => ['172.32.0.0'],
            'below 6to4 relay anycast' => ['192.88.98.255'],
            'above 6to4 relay anycast' => ['192.88.100.0'],
            'below private 192.168/16' => ['192.167.255.255'],
            'above private 192.168/16' => ['192.169.0.0'],
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
    public function it_parses_without_resolving_the_host()
    {
        $parsed = $this->validator(function () {
            throw new \Exception('The resolver should not be called when parsing.');
        })->parse('https://unknown.test/foo.jpg?w=100');

        $this->assertSame([
            'path' => 'foo.jpg',
            'base' => 'https://unknown.test',
            'query' => 'w=100',
        ], $parsed);
    }

    #[Test]
    public function it_still_rejects_malformed_urls_when_parsing()
    {
        $this->expectException(InvalidRemoteUrlException::class);
        $this->expectExceptionMessage('URLs with credentials are not allowed.');

        $this->validator()->parse('https://user:pass@example.com/foo.jpg');
    }

    #[Test]
    public function it_resolves_the_host_when_validating()
    {
        $this->expectException(InvalidRemoteUrlException::class);
        $this->expectExceptionMessage('Unable to resolve URL host.');

        $this->validator()->validate('https://unknown.test/foo.jpg');
    }

    #[Test]
    public function it_includes_an_explicit_port_in_the_parsed_base()
    {
        $this->assertSame('http://example.com:8080', $this->validator()->parse('http://example.com:8080/foo.jpg')['base']);
    }
}
