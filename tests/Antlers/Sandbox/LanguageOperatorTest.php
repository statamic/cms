<?php

namespace Tests\Antlers\Sandbox;

use Illuminate\Support\Str;
use Tests\Antlers\ParserTestCase;

class LanguageOperatorTest extends ParserTestCase
{
    public function test_str_operators_work()
    {
        $this->assertSame('UPPER', $this->evaluateRaw('str_upper "upper"'));
        $this->assertSame('lower', $this->evaluateRaw('str_lower "LOWER"'));
        $this->assertSame(true, $this->evaluateRaw('"abc" startswith "a"'));
        $this->assertSame(true, $this->evaluateRaw('"abc" endswith "c"'));
        $this->assertSame(true, $this->evaluateRaw('"teststring" str_is "test*" '));
    }

    public function test_op_str_ascii()
    {
        $this->assertSame('u', $this->evaluateRaw('str_ascii "ü"'));
    }

    public function test_str_is_uuid()
    {
        $this->assertFalse($this->evaluateRaw('is_uuid "nope"'));
        $this->assertTrue($this->evaluateRaw('is_uuid "976bb061-d11e-449e-a846-fe47cee3a87f"'));
    }

    public function test_str_is_url()
    {
        $this->assertFalse($this->evaluateRaw('is_url "test"'));
        $this->assertTrue($this->evaluateRaw('is_url "https://statamic.com"'));
    }

    public function test_str_finish()
    {
        $this->assertSame('test/', $this->evaluateRaw('"test" str_finish "/"'));
        $this->assertSame('test/', $this->evaluateRaw('"test/" str_finish "/"'));
    }

    public function test_str_after()
    {
        $this->assertSame('nah', $this->evaluateRaw('"hannah" str_after "han"'));
    }

    public function test_str_lower()
    {
        $this->assertSame('lower', $this->evaluateRaw(" str_lower 'LOWER'"));
    }

    public function test_str_upper()
    {
        $this->assertSame('UPPER', $this->evaluateRaw(" str_upper 'upper'"));
    }

    public function test_str_ucfirst()
    {
        $this->assertSame('Ucfirst', $this->evaluateRaw(" str_ucfirst 'ucfirst'"));
    }

    public function test_str_len()
    {
        $this->assertSame(4, $this->evaluateRaw('str_len "four"'));
    }

    public function test_str_after_last()
    {
        $this->assertSame('tte', $this->evaluateRaw('"yvette" str_after_last "yve"'));
    }

    public function test_str_before()
    {
        $this->assertSame('han', $this->evaluateRaw('"hannah" str_before "nah"'));
    }

    public function test_str_before_last()
    {
        $this->assertSame('yve', $this->evaluateRaw('"yvette" str_before_last "tte"'));
    }

    public function test_str_contains_all()
    {
        $this->assertTrue($this->evaluateRaw('"test string" str_contains_all check', ['check' => ['test', 'string']]));
        $this->assertTrue($this->evaluateRaw('"test string" str_contains_all check', ['check' => ['test']]));
        $this->assertFalse($this->evaluateRaw('"test string" str_contains_all check', ['check' => ['test', 'xxx']]));
    }

    public function test_str_camel()
    {
        $this->assertSame(Str::camel('test-value'), $this->evaluateRaw('str_camel "test-value"'));
    }

    public function test_str_word_count()
    {
        $this->assertSame(Str::wordCount('test value for word count'), $this->evaluateRaw('str_word_count "testtest value for word count"'));
    }

    public function test_str_studly()
    {
        $this->assertSame(Str::studly('test-value'), $this->evaluateRaw('str_studly "test-value"'));
    }

    public function test_str_kebab()
    {
        $this->assertSame(Str::kebab('test value'), $this->evaluateRaw('str_kebab "test value"'));
    }

    public function test_array_pluck_and_contains()
    {
        $vars = [
            'test' => [
                [
                    'locale' => 'en_US',
                ],
                [
                    'locale' => 'en_UK',
                ],
            ],
        ];

        $has = "{{ if test pluck 'locale' arr_contains 'en_US' }}yes{{ else }}no{{ /if }}";
        $does_not_have = "{{ if test pluck 'locale' arr_has 'en_ES' }}yes{{ else }}no{{ /if }}";

        $this->assertSame('no', $this->renderString($does_not_have, $vars));
        $this->assertSame('yes', $this->renderString($has, $vars));
    }

    public function test_take_operator()
    {
        $data = ['test_array' => ['one', 'two', 'three', 'four']];
        $template = <<<'EOT'
{{ test = test_array take 2 }}{{ value }}{{ /test }}
EOT;

        $this->assertSame('onetwo', $this->renderString($template, $data));

        $data = ['test_array' => ['one']];
        $template = <<<'EOT'
{{ test = test_array take 2 }}{{ value }}{{ /test }}
EOT;

        $this->assertSame('one', $this->renderString($template, $data));
    }

    public function test_array_merge()
    {
        $a = [1, 2, 3];
        $b = [3, 4, 5];

        $this->assertSame('123345', $this->renderString('{{ merged = a merge b }}{{ value }}{{ /merged }}', ['a' => $a, 'b' => $b]));
    }
}
