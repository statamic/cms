<?php

namespace Tests\Antlers\Runtime\Libraries;

use Statamic\Support\Str;
use Tests\Antlers\ParserTestCase;

class StringLibraryTest extends ParserTestCase
{
    protected $singleParamValues = [
        'hello world',
        'helloWorld',
        'hello_world',
        'hello-world',
        '1cd18397-921b-46e0-bf20-9c6a493108e6',
        '_hello_world',
        'categories',
        'theories',
        'ships',
        'lower',
        'UPPER',
        'more than two words',
        'more_than-two words',
        'https://statamic.dev',
        'https://statamic.dev/',
        ' this has a space in front',
        'this has a space in back ',
        ' this has a space in both ',
        'ü',
    ];

    public function test_str_substr_count()
    {
        $this->assertSame(3, $this->evaluateRaw('str.substrCount("wordwordword", "word")'));
        $this->assertSame(1, $this->evaluateRaw('str.substrCount("wordwordword", "word", 4, 4)'));
        $this->assertSame(2, $this->evaluateRaw('str.substrCount("wordwordword", "word", 4)'));
        $this->assertSame(0, $this->evaluateRaw('str.substrCount("wordwordword", "word", 4, 2)'));
    }

    public function test_str_trim()
    {
        $string = <<<'EOT'

                     value

EOT;

        $this->assertSame('value', $this->renderString('{{ str.trim(str_arg) }}', [
            'str_arg' => $string,
        ]));
    }

    public function test_str_word_count()
    {
        foreach ($this->singleParamValues as $param) {
            $this->assertSame(\str_word_count($param), $this->evaluateRaw('str.wordCount(arg_param)', [
                'arg_param' => $param,
            ]));
        }
    }

    public function test_str_ucfirst()
    {
        foreach ($this->singleParamValues as $param) {
            $this->assertSame(Str::ucfirst($param), $this->evaluateRaw('str.ucfirst(arg_param)', [
                'arg_param' => $param,
            ]));
        }
    }

    public function test_str_substr()
    {
        $this->assertSame('world', $this->evaluateRaw('str.substr("helloworld", 5)'));
        $this->assertSame('worl', $this->evaluateRaw('str.substr("helloworld", 5, 4)'));
        $this->assertSame('wo', $this->evaluateRaw('str.substr("helloworld", 5, 2)'));
    }

    public function test_str_studly()
    {
        foreach ($this->singleParamValues as $param) {
            $this->assertSame(Str::studly($param), $this->evaluateRaw('str.studly(arg_param)', [
                'arg_param' => $param,
            ]));
        }
    }

    public function test_str_snake()
    {
        foreach ($this->singleParamValues as $param) {
            $this->assertSame(Str::snake($param), $this->evaluateRaw('str.snake(arg_param)', [
                'arg_param' => $param,
            ]));
        }

        foreach ($this->singleParamValues as $param) {
            $this->assertSame(Str::snake($param, '-'), $this->evaluateRaw('str.snake(arg_param, "-")', [
                'arg_param' => $param,
            ]));
        }
    }

    public function test_str_singular()
    {
        foreach ($this->singleParamValues as $param) {
            $this->assertSame(Str::singular($param), $this->evaluateRaw('str.singular(arg_param)', [
                'arg_param' => $param,
            ]));
        }
    }

    public function test_str_title()
    {
        foreach ($this->singleParamValues as $param) {
            $this->assertSame(Str::title($param), $this->evaluateRaw('str.title(arg_param)', [
                'arg_param' => $param,
            ]));
        }
    }

    public function test_str_upper()
    {
        foreach ($this->singleParamValues as $param) {
            $this->assertSame(Str::upper($param), $this->evaluateRaw('str.upper(arg_param)', [
                'arg_param' => $param,
            ]));
        }
    }

    public function test_str_start()
    {
        $this->assertSame('https://laravel.com/', $this->evaluateRaw('str.start("laravel.com/", "https://")'));
        $this->assertSame('https://laravel.com/', $this->evaluateRaw('str.start("https://laravel.com/", "https://")'));
    }

    public function test_str_remove()
    {
        $this->assertEquals('world', $this->evaluateRaw('str.remove("hello", "helloworld", false)'));
        $this->assertEquals('world', $this->evaluateRaw('str.remove("hello", "helloworld")'));
        $this->assertEquals('helloworld', $this->evaluateRaw('str.remove("HELLO", "helloworld", true)'));
    }

    public function test_str_replace_last()
    {
        $this->assertEquals('hello world there', $this->evaluateRaw('str.replaceLast("world", "there", "hello world world")'));
    }

    public function test_str_replace_first()
    {
        $this->assertEquals('hello there world', $this->evaluateRaw('str.replaceFirst("world", "there", "hello world world")'));
    }

    public function test_str_replace()
    {
        $this->assertEquals('hello there there', $this->evaluateRaw('str.replace("hello world world", "world", "there")'));
    }

    public function test_str_replace_array()
    {
        $this->assertSame('foo/bar/baz', $this->evaluateRaw("str.replaceArray('?', arr('foo', 'bar', 'baz'), '?/?/?')"));
    }

    public function test_str_repeat()
    {
        $this->assertSame('111', $this->evaluateRaw('str.repeat("1", 3)'));
    }

    public function test_str_random()
    {
        $this->assertEquals(16, $this->evaluateRaw('str.length(str.random())'));
        $this->assertEquals(32, $this->evaluateRaw('str.length(str.random(32))'));
    }

    public function test_str_plural_studly()
    {
        for ($i = 0; $i < 5; $i++) {
            foreach ($this->singleParamValues as $param) {
                $this->assertSame(Str::pluralStudly($param, $i), $this->evaluateRaw('str.pluralStudly(arg_param, count)', [
                    'arg_param' => $param,
                    'count' => $i,
                ]));
            }
        }
    }

    public function test_str_plural()
    {
        for ($i = 0; $i < 5; $i++) {
            foreach ($this->singleParamValues as $param) {
                $this->assertSame(Str::plural($param, $i), $this->evaluateRaw('str.plural(arg_param, count)', [
                    'arg_param' => $param,
                    'count' => $i,
                ]));
            }
        }
    }

    public function test_str_pad_left()
    {
        $this->assertSame('-=-=-Alien', $this->evaluateRaw('str.padLeft("Alien", 10, "-=")'));
        $this->assertSame('     Alien', $this->evaluateRaw('str.padLeft("Alien", 10)'));
    }

    public function test_str_pad_right()
    {
        $this->assertSame('Alien-----', $this->evaluateRaw('str.padRight("Alien", 10, "-")'));
        $this->assertSame('Alien     ', $this->evaluateRaw('str.padRight("Alien", 10)'));
    }

    public function test_str_pad_both()
    {
        $this->assertSame('__Alien___', $this->evaluateRaw('str.padBoth("Alien", 10, "_")'));
        $this->assertSame('  Alien   ', $this->evaluateRaw('str.padBoth("Alien", 10)'));
    }

    public function test_str_match_all()
    {
        $this->assertEquals(['un', 'ly'], $this->evaluateRaw("str.matchAll('/f(\w*)/', 'bar fun bar fly')"));
    }

    public function test_str_match()
    {
        $this->assertSame('bar', $this->evaluateRaw('str.match("/bar/", "foo bar")'));
        $this->assertSame('bar', $this->evaluateRaw('str.match("/foo (.*)/", "foo bar")'));
    }

    public function test_str_words()
    {
        foreach ($this->singleParamValues as $param) {
            $this->assertSame(Str::words($param, 2, '...'), $this->evaluateRaw('str.words(arg_param, 2, "...")', [
                'arg_param' => $param,
            ]));
        }
    }

    public function test_str_lower()
    {
        foreach ($this->singleParamValues as $param) {
            $this->assertSame(Str::lower($param), $this->evaluateRaw('str.lower(arg_param)', [
                'arg_param' => $param,
            ]));
        }
    }

    public function test_str_limit()
    {
        $string = 'The PHP framework for web artisans.';
        $this->assertSame('这是一', $this->evaluateRaw("str.limit('这是一段中文', 6, '')"));
        $this->assertSame('The PHP...', $this->evaluateRaw('str.limit(string, 7)', ['string' => $string]));
        $this->assertSame('The PHP', $this->evaluateRaw("str.limit(string, 7, '')", ['string' => $string]));
    }

    public function test_str_length()
    {
        foreach ($this->singleParamValues as $param) {
            $this->assertSame(Str::length($param), $this->evaluateRaw('str.length(arg_param)', [
                'arg_param' => $param,
            ]));
        }

        foreach ($this->singleParamValues as $param) {
            $this->assertSame(Str::length($param, 'UTF-8'), $this->evaluateRaw('str.length(arg_param, "UTF-8")', [
                'arg_param' => $param,
            ]));
        }
    }

    public function test_str_kebab()
    {
        foreach ($this->singleParamValues as $param) {
            $this->assertSame(Str::kebab($param), $this->evaluateRaw('str.kebab(arg_param)', [
                'arg_param' => $param,
            ]));
        }
    }

    public function test_str_is()
    {
        $this->assertTrue($this->evaluateRaw('str.is("/", "/")'));
        $this->assertTrue($this->evaluateRaw('str.is("foo/*", "foo/bar/baz")'));
        $this->assertTrue($this->evaluateRaw('str.is("*@*", "App\Class@method")'));
        $this->assertFalse($this->evaluateRaw('str.is("*FOO*", "foo/bar/baz")'));
    }

    public function test_str_finish()
    {
        $this->assertSame('laravel.com/', $this->evaluateRaw('str.finish("laravel.com/", "/")'));
        $this->assertSame('laravel.com/', $this->evaluateRaw('str.finish("laravel.com", "/")'));
    }

    public function test_str_ends_with()
    {
        $this->assertTrue($this->evaluateRaw('str.endsWith("test", "st")'));
        $this->assertFalse($this->evaluateRaw('str.endsWith("test", "fst")'));
    }

    public function test_str_contains()
    {
        $this->assertTrue($this->evaluateRaw('str.contains("test", "st")'));
        $this->assertFalse($this->evaluateRaw('str.contains("test", "ffff")'));
    }

    public function test_str_contains_all()
    {
        $this->assertTrue($this->evaluateRaw('str.containsAll("name test", arr("name", "test"))'));
        $this->assertTrue($this->evaluateRaw('str.containsAll("name test", arr("name"))'));
        $this->assertFalse($this->evaluateRaw('str.containsAll("name test", arr("name", "xxx"))'));
    }

    public function test_str_camel()
    {
        foreach ($this->singleParamValues as $param) {
            $this->assertSame(Str::camel($param), $this->evaluateRaw('str.camel(arg_param)', [
                'arg_param' => $param,
            ]));
        }
    }

    public function test_str_between()
    {
        $this->assertSame('b', $this->evaluateRaw("str.between('dddabcddd', 'a', 'c')"));
    }

    public function test_str_before_last()
    {
        $this->assertSame('ééé ', $this->evaluateRaw("str.beforeLast('ééé yvette', 'yve')"));
    }

    public function test_str_before()
    {
        $this->assertSame('han', $this->evaluateRaw('str.before("hannah", "nah")'));
    }

    public function test_str_ascii()
    {
        foreach ($this->singleParamValues as $param) {
            $this->assertSame(Str::ascii($param), $this->evaluateRaw('str.ascii(arg_param)', [
                'arg_param' => $param,
            ]));
        }
    }

    public function test_str_after_last()
    {
        $this->assertSame('te', $this->evaluateRaw('str.afterLast("yv0et0te", "0")'));
    }

    public function test_str_starts_with()
    {
        $this->assertTrue($this->evaluateRaw('str.startsWith("https://laravel.com", "https://")'));
        $this->assertFalse($this->evaluateRaw('str.startsWith("https://laravel.com", "http://")'));
    }

    public function test_str_sentence_list()
    {
        $list = ['one', 'two', 'three'];
        $this->assertSame(Str::makeSentenceList($list), $this->evaluateRaw('str.sentenceList(list)', [
            'list' => $list,
        ]));
    }

    public function test_str_slug()
    {
        foreach ($this->singleParamValues as $param) {
            $this->assertSame(Str::slug($param), $this->evaluateRaw('str.slug(arg_param)', [
                'arg_param' => $param,
            ]));
        }

        foreach ($this->singleParamValues as $param) {
            $this->assertSame(Str::slug($param, '_'), $this->evaluateRaw('str.slug(arg_param, "_")', [
                'arg_param' => $param,
            ]));
        }

        foreach ($this->singleParamValues as $param) {
            $this->assertSame(Str::slug($param, '_'), $this->evaluateRaw('str.slug(arg_param, separator: "_")', [
                'arg_param' => $param,
            ]));
        }

        foreach ($this->singleParamValues as $param) {
            $this->assertSame(Str::slug($param, '_', 'es'), $this->evaluateRaw('str.slug(arg_param, "_", "es")', [
                'arg_param' => $param,
            ]));
        }
    }

    public function test_str_studly_to_title()
    {
        foreach ($this->singleParamValues as $param) {
            $this->assertSame(Str::studlyToTitle($param), $this->evaluateRaw('str.studlyToTitle(arg_param)', [
                'arg_param' => $param,
            ]));
        }
    }

    public function test_str_studly_to_words()
    {
        foreach ($this->singleParamValues as $param) {
            $this->assertSame(Str::studlyToWords($param), $this->evaluateRaw('str.studlyToWords(arg_param)', [
                'arg_param' => $param,
            ]));
        }
    }

    public function test_str_is_url()
    {
        foreach ($this->singleParamValues as $param) {
            $this->assertSame(Str::isUrl($param), $this->evaluateRaw('str.isUrl(arg_param)', [
                'arg_param' => $param,
            ]));
        }
    }

    public function test_str_deslugify()
    {
        foreach ($this->singleParamValues as $param) {
            $this->assertSame(Str::deslugify($param), $this->evaluateRaw('str.deslugify(arg_param)', [
                'arg_param' => $param,
            ]));
        }
    }

    public function test_str_file_size_for_humans()
    {
        $sizes = [
            0,
            1024,
            1792,
            1048576,
            1835008,
            1073741824,
            1879048192,
        ];

        foreach ($sizes as $size) {
            $this->assertSame(Str::fileSizeForHumans($size),
                $this->evaluateRaw('str.fileSizeForHumans(size)', ['size' => $size]));
        }

        foreach ($sizes as $size) {
            $this->assertSame(Str::fileSizeForHumans($size, 0),
                $this->evaluateRaw('str.fileSizeForHumans(size, 0)', ['size' => $size]));
        }
    }

    public function test_str_time_for_humans()
    {
        $times = [
            1,
            1000,
            1500,
            1570,
        ];

        foreach ($times as $time) {
            $this->assertSame(Str::timeForHumans($time),
                $this->evaluateRaw('str.timeForHumans(time)', ['time' => $time]));
        }
    }

    public function test_str_widont()
    {
        for ($i = 0; $i < 5; $i++) {
            foreach ($this->singleParamValues as $param) {
                $this->assertSame(Str::widont($param, $i), $this->evaluateRaw('str.widont(arg_param, count)', [
                    'arg_param' => $param,
                    'count' => $i,
                ]));
            }
        }

        for ($i = 0; $i < 5; $i++) {
            foreach ($this->singleParamValues as $param) {
                $this->assertSame(Str::widont($param, $i), $this->evaluateRaw('str.widont(arg_param, words: count)', [
                    'arg_param' => $param,
                    'count' => $i,
                ]));
            }
        }
    }

    public function test_str_compare()
    {
        foreach ($this->singleParamValues as $param) {
            $this->assertSame(Str::compare($param, strrev($param)), $this->evaluateRaw('str.compare(arg_param, string.reverse(arg_param))', [
                'arg_param' => $param,
            ]));
        }

        foreach ($this->singleParamValues as $param) {
            $this->assertSame(Str::compare($param, $param), $this->evaluateRaw('str.compare(arg_param, arg_param)', [
                'arg_param' => $param,
            ]));
        }
    }

    public function test_str_bool()
    {
        $this->assertSame(Str::bool(true), $this->evaluateRaw('str.bool(true)'));
        $this->assertSame(Str::bool(false), $this->evaluateRaw('str.bool(false)'));
    }
}
