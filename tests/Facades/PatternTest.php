<?php

namespace Tests\Facades;

use Statamic\Facades\Pattern;
use Tests\TestCase;

class PatternTest extends TestCase
{
    /**
     * @test
     *
     * @dataProvider likeProvider
     */
    public function it_escapes_sql_like_syntax($string, $expected)
    {
        $this->assertEquals($expected, Pattern::sqlLikeQuote($string));
    }

    public function likeProvider()
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
}
