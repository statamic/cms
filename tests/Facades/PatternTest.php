<?php

namespace Tests\Facades;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Pattern;
use Tests\TestCase;

class PatternTest extends TestCase
{
    #[Test]
    #[DataProvider('likeProvider')]
    public function it_escapes_sql_like_syntax($string, $expected)
    {
        $this->assertEquals($expected, Pattern::sqlLikeQuote($string));
    }

    public static function likeProvider()
    {
        return collect([
            'foo' => 'foo',
            '%foo' => '\%foo',
            'foo%' => 'foo\%',
            '%foo%' => '\%foo\%',
            '_foo' => '\_foo',
            'foo_' => 'foo\_',
            '_foo_' => '\_foo\_',
            'f_o' => 'f\_o',
        ])->mapWithKeys(fn ($expected, $string) => [$string => [$string, $expected]])->all();
    }

    #[Test]
    #[DataProvider('likeRegexProvider')]
    public function it_converts_sql_like_syntax_to_regex($string, $expected)
    {
        $this->assertEquals($expected, Pattern::sqlLikeToRegex($string));
    }

    public static function likeRegexProvider()
    {
        return collect([
            'foo' => '^foo$',
            'foo%' => '^foo.*$',
            '%world' => '^.*world$',
            '%world%' => '^.*world.*$',
            '_oo' => '^.oo$',
            'o_' => '^o.$',
            'foo_bar' => '^foo.bar$',
            'foo__bar' => '^foo..bar$',
            'fo__bar' => '^fo..bar$',
            'foo\_bar' => '^foo_bar$',
            '20\%' => '^20%$',
            '20\%%' => '^20%.*$',
            '%3.14%' => '^.*3\.14.*$',
            '%[4%' => '^.*\[4.*$',
            '/' => '^\/$',
            '%/' => '^.*\/$',
            '/%' => '^\/.*$',
            '%/%' => '^.*\/.*$',
        ])->mapWithKeys(fn ($expected, $string) => [$string => [$string, $expected]])->all();
    }
}
