<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class IsUrlTest extends TestCase
{
    public static function urlsProvider(): array
    {
        return [
            'valid_url_http' => [true, 'http://google.com/'],
            'valid_url_ssl' => [true, 'https://google.com/'],
            'valid_url_without_slash' => [true, 'https://google.com'],
            'with_subdomain' => [true, 'https://foo.google.com'],
            'without_subdomain_protocol' => [false, 'google.com'],
            'none_url' => [false, 'foo bar baz'],
        ];
    }

    #[Test]
    #[DataProvider('urlsProvider')]
    public function it_returns_true_if_value_is_valid_url($expected, $input): void
    {
        $modified = $this->modify($input);
        $this->assertEquals($expected, $modified);
    }

    private function modify($value)
    {
        return Modify::value($value)->isUrl()->fetch();
    }
}
