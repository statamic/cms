<?php

namespace Tests\Yaml;

use Exception;
use Illuminate\Support\Facades\Facade;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\YAML;
use Statamic\Yaml\ParseException;
use Statamic\Yaml\Yaml as StatamicYaml;
use Symfony\Component\Yaml\Yaml as SymfonyYaml;
use Tests\TestCase;

class YamlTest extends TestCase
{
    #[Test]
    public function it_dumps_yaml()
    {
        $array = [
            'foo' => 'bar',
            'two_words' => 'two words',
            'multiline' => "first\nsecond",
            'array' => ['one', 'two'],
        ];

        $symfonyYaml = Mockery::mock(SymfonyYaml::class)
            ->shouldReceive('dump')
            ->with($array, 100, 2, SymfonyYaml::DUMP_MULTI_LINE_LITERAL_BLOCK)
            ->once()
            ->andReturn('some properly dumped yaml from symfony')
            ->getMock();

        $this->app->instance(StatamicYaml::class, new StatamicYaml($symfonyYaml));
        Facade::clearResolvedInstance(StatamicYaml::class);

        $this->assertEquals('some properly dumped yaml from symfony', YAML::dump($array));
    }

    #[Test]
    public function it_dumps_with_front_matter_when_content_is_passed()
    {
        $expected = <<<'EOT'
---
foo: bar
---
some content
EOT;

        $this->assertStringEqualsStringIgnoringLineEndings($expected, YAML::dump(['foo' => 'bar'], 'some content'));
    }

    #[Test]
    public function it_dumps_without_front_matter_when_content_is_an_array()
    {
        $expected = <<<'EOT'
foo: bar
content:
  baz: qux

EOT;

        $this->assertEquals($expected, YAML::dump(['foo' => 'bar'], ['baz' => 'qux']));
    }

    #[Test]
    public function it_dumps_without_front_matter_when_content_is_an_empty_array()
    {
        $expected = <<<'EOT'
foo: bar
content: {  }

EOT;

        $this->assertEquals($expected, YAML::dump(['foo' => 'bar'], []));
    }

    #[Test]
    public function it_dumps_without_front_matter_when_content_is_null()
    {
        $expected = <<<'EOT'
foo: bar

EOT;

        $this->assertEquals($expected, YAML::dump(['foo' => 'bar'], null));
        $this->assertEquals($expected, YAML::dump(['foo' => 'bar']));
    }

    #[Test]
    public function it_explicitly_dumps_front_matter()
    {
        $expected = <<<'EOT'
---
foo: bar
---

EOT;

        $this->assertStringEqualsStringIgnoringLineEndings($expected, YAML::dumpFrontMatter(['foo' => 'bar']));
    }

    #[Test]
    public function it_explicitly_dumps_front_matter_with_content()
    {
        $expected = <<<'EOT'
---
foo: bar
---
some content
EOT;

        $this->assertStringEqualsStringIgnoringLineEndings($expected, YAML::dumpFrontMatter(['foo' => 'bar'], 'some content'));
    }

    #[Test]
    public function it_explicitly_dumps_front_matter_including_content_when_its_an_array()
    {
        $expected = <<<'EOT'
---
foo: bar
content:
  baz: qux
---

EOT;

        $this->assertStringEqualsStringIgnoringLineEndings($expected, YAML::dumpFrontMatter(['foo' => 'bar'], ['baz' => 'qux']));
    }

    #[Test]
    public function it_explicitly_dumps_front_matter_including_content_when_its_an_empty_array()
    {
        $expected = <<<'EOT'
---
foo: bar
content: {  }
---

EOT;

        $this->assertStringEqualsStringIgnoringLineEndings($expected, YAML::dumpFrontMatter(['foo' => 'bar'], []));
    }

    #[Test]
    public function it_explicitly_dumps_front_matter_without_content_when_its_null()
    {
        $expected = <<<'EOT'
---
foo: bar
---

EOT;

        $this->assertStringEqualsStringIgnoringLineEndings($expected, YAML::dumpFrontMatter(['foo' => 'bar'], null));
        $this->assertStringEqualsStringIgnoringLineEndings($expected, YAML::dumpFrontMatter(['foo' => 'bar']));
    }

    /**
     * @see https://github.com/statamic/cms/issues/3612
     **/
    #[Test]
    public function it_dumps_front_matter_properly_when_symfony_yaml_dumper_doesnt_end_with_a_line_break()
    {
        $array = [
            'foo' => 'bar',
            'baz' => "first line\nsecond line", // the multiline string *must* be last for this bug
        ];

        // We mock symfony because the multiline character is different depending on the version installed.
        // It will be | on early versions. It will be |- on later versions.
        // The |- character means trim trailing newlines, and is the default when dumping multiline strings.
        $symfonyYaml = Mockery::mock(SymfonyYaml::class)
            ->shouldReceive('dump')
            ->with($array, 100, 2, SymfonyYaml::DUMP_MULTI_LINE_LITERAL_BLOCK)
            ->once()
            ->andReturn($symfonyDumpedYaml = "foo: bar\nbaz: |-\n  first line\n  second line")
            ->getMock();

        $this->app->instance(StatamicYaml::class, new StatamicYaml($symfonyYaml));
        Facade::clearResolvedInstance(StatamicYaml::class);

        // Without the bug fix, the --- would come immediately after the "second line". Like this:
        // baz: |-
        //   first line
        //   second line---
        // content
        $expected = <<<EOT
---
$symfonyDumpedYaml
---
content
EOT;

        $this->assertStringEqualsStringIgnoringLineEndings($expected, YAML::dumpFrontMatter($array, 'content'));
    }

    #[Test]
    public function it_parses_a_string_of_yaml()
    {
        $this->assertEquals(['foo' => 'bar'], YAML::parse('foo: bar'));
    }

    #[Test]
    public function it_parses_an_empty_string_of_yaml()
    {
        $this->assertEquals([], YAML::parse(''));
    }

    #[Test]
    public function it_parses_with_content_and_front_matter()
    {
        $yaml = "---\nfoo: bar\n---\nsome content";

        $this->assertEquals(['foo' => 'bar', 'content' => 'some content'], YAML::parse($yaml));
    }

    #[Test]
    public function it_parses_with_content_and_front_matter_with_crlf()
    {
        $yaml = "---\r\nfoo: bar\r\n---\r\nsome content";

        $this->assertEquals(['foo' => 'bar', 'content' => 'some content'], YAML::parse($yaml));
    }

    #[Test]
    public function it_parses_with_content_when_its_in_the_front_matter()
    {
        $yaml = <<<'EOT'
---
foo: bar
content: some content
---
EOT;

        $this->assertEquals(['foo' => 'bar', 'content' => 'some content'], YAML::parse($yaml));
    }

    #[Test]
    public function it_throws_exception_when_there_is_a_content_var_and_a_content_area()
    {
        $yaml = <<<'EOT'
---
foo: bar
content: some content
---
some text
EOT;

        $this->expectException(ParseException::class);
        YAML::parse($yaml);
    }

    #[Test]
    public function it_parses_a_file_when_no_argument_is_given()
    {
        $yaml = <<<'EOT'
---
foo: bar
---
some content
EOT;

        $fp = tmpfile();
        fwrite($fp, $yaml);
        $path = stream_get_meta_data($fp)['uri'];

        $this->assertEquals(
            ['foo' => 'bar', 'content' => 'some content'],
            YAML::file($path)->parse()
        );
    }

    #[Test]
    public function when_parsing_and_content_is_just_whitespace_it_treats_it_as_null()
    {
        $yaml = <<<'EOT'
---
foo: bar
---


EOT;

        $this->assertEquals(['foo' => 'bar'], YAML::parse($yaml));
    }

    #[Test]
    public function it_throws_exception_when_parsing_without_an_argument_or_file()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot parse YAML without a file or string.');

        YAML::parse();
    }

    #[Test]
    public function it_creates_parse_exception_pointing_to_temporary_file_when_no_file_is_provided()
    {
        $yaml = <<<'EOT'
---
foo: 'bar
baz: 'qux'
---
some content
EOT;

        try {
            YAML::parse($yaml);
        } catch (Exception $e) {
            $this->assertInstanceOf(ParseException::class, $e);
            $this->assertEquals('Unexpected characters near "qux\'" at line 3 (near "baz: \'qux\'").', $e->getMessage());
            $path = storage_path('statamic/tmp/yaml/'.md5("---\nfoo: 'bar\nbaz: 'qux'"));
            $this->assertEquals($path, $e->getFile());

            return;
        }

        $this->fail('Exception was not thrown.');
    }

    #[Test]
    public function it_creates_parse_exception_pointing_to_actual_file_when_file_is_provided_and_it_exists()
    {
        $yaml = <<<'EOT'
---
foo: 'bar
baz: 'qux'
---
some content
EOT;

        file_put_contents($path = __DIR__.'/test.yaml', $yaml);

        try {
            YAML::file($path)->parse($yaml);
        } catch (Exception $e) {
            $this->assertInstanceOf(ParseException::class, $e);
            $this->assertEquals('Unexpected characters near "qux\'" at line 3 (near "baz: \'qux\'").', $e->getMessage());
            $this->assertEquals($path, $e->getFile());
            @unlink($path);

            return;
        }

        $this->fail('Exception was not thrown.');
    }

    #[Test]
    public function it_creates_parse_exception_pointing_to_temporary_file_with_similar_path_when_file_is_provided_but_doesnt_exist()
    {
        $yaml = <<<'EOT'
---
foo: 'bar
baz: 'qux'
---
some content
EOT;

        try {
            YAML::file('path/to/file.yaml')->parse($yaml);
        } catch (Exception $e) {
            $this->assertInstanceOf(ParseException::class, $e);
            $this->assertEquals('Unexpected characters near "qux\'" at line 3 (near "baz: \'qux\'").', $e->getMessage());
            $this->assertEquals(storage_path('statamic/tmp/yaml/path/to/file.yaml'), $e->getFile());

            return;
        }

        $this->fail('Exception was not thrown.');
    }

    #[Test]
    public function it_doesnt_maintain_files_across_uses()
    {
        YAML::file('path/to/file/previously/used.yaml')->parse('foo: bar');

        $yaml = <<<'EOT'
---
foo: 'bar
baz: 'qux'
---
some content
EOT;

        try {
            YAML::parse($yaml);
        } catch (Exception $e) {
            $this->assertInstanceOf(ParseException::class, $e);
            $this->assertEquals('Unexpected characters near "qux\'" at line 3 (near "baz: \'qux\'").', $e->getMessage());
            $path = storage_path('statamic/tmp/yaml/'.md5("---\nfoo: 'bar\nbaz: 'qux'"));
            $this->assertEquals($path, $e->getFile());

            return;
        }

        $this->fail('Exception was not thrown.');
    }

    #[Test]
    public function it_doesnt_maintain_files_across_uses_when_previous_call_had_no_yaml()
    {
        YAML::file('path/to/file/previously/used.yaml')->parse('');

        $yaml = <<<'EOT'
---
foo: 'bar
baz: 'qux'
---
some content
EOT;

        try {
            YAML::parse($yaml);
        } catch (Exception $e) {
            $this->assertInstanceOf(ParseException::class, $e);
            $this->assertEquals('Unexpected characters near "qux\'" at line 3 (near "baz: \'qux\'").', $e->getMessage());
            $path = storage_path('statamic/tmp/yaml/'.md5("---\nfoo: 'bar\nbaz: 'qux'"));
            $this->assertEquals($path, $e->getFile());

            return;
        }

        $this->fail('Exception was not thrown.');
    }

    #[Test]
    public function it_throws_an_exception_when_an_array_cannot_be_returned()
    {
        $string = <<<'EOT'
<<< HEAD
An example when this happens
===
Is in a merge conflict
>>> BRANCH
EOT;

        try {
            YAML::parse($string);
        } catch (Exception $e) {
            $this->assertInstanceOf(ParseException::class, $e);
            $this->assertEquals('Unable to parse (near "<<< HEAD").', $e->getMessage());

            return;
        }

        $this->fail('Exception was not thrown.');
    }
}
