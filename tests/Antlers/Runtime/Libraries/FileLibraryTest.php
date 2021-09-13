<?php

namespace Tests\Antlers\Runtime\Libraries;

use Tests\Antlers\ParserTestCase;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;

class FileLibraryTest extends ParserTestCase
{
    public function test_file_exists()
    {
        $file = realpath(__DIR__.'/../../../__fixtures__/content/file.txt');

        $this->assertSame('no', $this->renderString('{{ if (file.exists("not_a_file.txt")) }}yes{{ else }}no{{ /if }}'));
        $this->assertSame('yes', $this->renderString('{{ if (file.exists(file)) }}yes{{ else }}no{{ /if }}', [
            'file' => $file,
        ]));
    }

    public function test_dir_exists()
    {
        $dir = realpath(__DIR__.'/../../../__fixtures__/content');

        $this->assertSame('no', $this->renderString('{{ if (file.dirExists("not_a_dir")) }}yes{{ else }}no{{ /if }}'));
        $this->assertSame('yes', $this->renderString('{{ if (file.dirExists(directory)) }}yes{{ else }}no{{ /if }}', [
            'directory' => $dir,
        ]));
    }

    public function test_read_text()
    {
        $file = realpath(__DIR__.'/../../../__fixtures__/content/file.txt');
        $contents = StringUtilities::normalizeLineEndings(file_get_contents($file));

        $this->assertSame($contents, StringUtilities::normalizeLineEndings($this->renderString('{{ file.readText(file) }}', [
            'file' => $file,
        ])));
    }

    public function test_read_lines()
    {
        $file = realpath(__DIR__.'/../../../__fixtures__/content/file.txt');

        $template = <<<'EOT'
{{ lines = file.readLines(file) }}
Lines:

{{ lines }}{{ value }}
{{ /lines }}
EOT;

        $expected = <<<'EOT'

Lines:

line 1
line 2
line 3

EOT;

        $results = StringUtilities::normalizeLineEndings($this->renderString($template, ['file' => $file]));

        $this->assertSame(StringUtilities::normalizeLineEndings($expected), $results);
    }
}
