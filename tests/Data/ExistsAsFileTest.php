<?php

namespace Tests\Data;

use Statamic\Data\ExistsAsFile;
use Tests\TestCase;

class ExistsAsFileTest extends TestCase
{
    /** @test */
    public function it_dumps_yaml_without_front_matter_when_the_file_extension_is_yaml()
    {
        $item = new class extends ExistsAsFileItem {
            public function fileExtension()
            {
                return 'yaml';
            }

            public function shouldRemoveNullsFromFileData()
            {
                return false;
            }

            public function fileData()
            {
                return [
                    'foo' => 'bar',
                    'content' => 'the content',
                ];
            }
        };

        $expected = <<<'EOT'
foo: bar
content: 'the content'
EOT;

        $this->assertEquals($expected, trim($item->fileContents()));
    }

    /** @test */
    public function it_dumps_yaml_with_front_matter_when_theres_content()
    {
        $item = new class extends ExistsAsFileItem {
            public function fileExtension()
            {
                return 'md';
            }

            public function shouldRemoveNullsFromFileData()
            {
                return false;
            }

            public function fileData()
            {
                return [
                    'foo' => 'bar',
                    'content' => 'the content',
                ];
            }
        };

        $expected = <<<'EOT'
---
foo: bar
---
the content
EOT;

        $this->assertEquals($expected, trim($item->fileContents()));
    }

    /** @test */
    public function it_dumps_yaml_with_front_matter_when_content_is_missing()
    {
        $item = new class extends ExistsAsFileItem {
            public function fileExtension()
            {
                return 'md';
            }

            public function shouldRemoveNullsFromFileData()
            {
                return false;
            }

            public function fileData()
            {
                return [
                    'foo' => 'bar',
                ];
            }
        };

        $expected = <<<'EOT'
---
foo: bar
---
EOT;

        $this->assertEquals($expected, trim($item->fileContents()));
    }

    /** @test */
    public function it_dumps_yaml_without_front_matter_when_content_is_literally_null()
    {
        $item = new class extends ExistsAsFileItem {
            public function fileExtension()
            {
                return 'md';
            }

            public function shouldRemoveNullsFromFileData()
            {
                return false;
            }

            public function fileData()
            {
                return [
                    'foo' => 'bar',
                    'content' => null,
                ];
            }
        };

        $expected = <<<'EOT'
foo: bar
content: null
EOT;

        $this->assertEquals($expected, trim($item->fileContents()));
    }
}

class ExistsAsFileItem
{
    use ExistsAsFile;

    public function path()
    {
        //
    }
}
