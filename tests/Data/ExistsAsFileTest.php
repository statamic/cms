<?php

namespace Tests\Data;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Data\ExistsAsFile;
use Tests\TestCase;

class ExistsAsFileTest extends TestCase
{
    #[Test]
    public function it_dumps_yaml_without_front_matter_when_the_file_extension_is_yaml()
    {
        $item = $this->makeItem([
            'extension' => 'yaml',
            'removeNulls' => false,
            'data' => [
                'foo' => 'bar',
                'content' => 'the content',
            ],
        ]);

        $expected = <<<'EOT'
foo: bar
content: 'the content'
EOT;

        $this->assertEquals($expected, trim($item->fileContents()));
    }

    #[Test]
    public function it_dumps_yaml_with_front_matter_when_theres_content()
    {
        $item = $this->makeItem([
            'extension' => 'md',
            'removeNulls' => false,
            'data' => [
                'foo' => 'bar',
                'content' => 'the content',
            ],
        ]);

        $expected = <<<'EOT'
---
foo: bar
---
the content
EOT;

        $this->assertStringEqualsStringIgnoringLineEndings($expected, trim($item->fileContents()));
    }

    #[Test]
    public function it_dumps_yaml_with_front_matter_when_content_is_missing()
    {
        $item = $this->makeItem([
            'extension' => 'md',
            'removeNulls' => false,
            'data' => [
                'foo' => 'bar',
            ],
        ]);

        $expected = <<<'EOT'
---
foo: bar
---
EOT;

        $this->assertStringEqualsStringIgnoringLineEndings($expected, trim($item->fileContents()));
    }

    #[Test]
    public function it_dumps_yaml_without_front_matter_when_content_is_literally_null()
    {
        $item = $this->makeItem([
            'extension' => 'md',
            'removeNulls' => false,
            'data' => [
                'foo' => 'bar',
                'content' => null,
            ],
        ]);

        $expected = <<<'EOT'
foo: bar
content: null
EOT;

        $this->assertEquals($expected, trim($item->fileContents()));
    }

    private function makeItem($args)
    {
        return new class($args['extension'] ?? 'yaml', $args['removeNulls'] ?? true, $args['data'] ?? [])
        {
            use ExistsAsFile;

            protected $extension;
            protected $shouldRemoveNulls;
            protected $fileData;

            public function __construct($extension, $shouldRemoveNulls, $fileData)
            {
                $this->extension = $extension;
                $this->shouldRemoveNulls = $shouldRemoveNulls;
                $this->fileData = $fileData;
            }

            public function path()
            {
                //
            }

            public function fileExtension()
            {
                return $this->extension;
            }

            public function shouldRemoveNullsFromFileData()
            {
                return $this->shouldRemoveNulls;
            }

            public function fileData()
            {
                return $this->fileData;
            }
        };
    }
}
