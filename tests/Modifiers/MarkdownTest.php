<?php

namespace Tests\Modifiers;

use Statamic\Facades\Markdown;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class MarkdownTest extends TestCase
{
    /** @test */
    public function it_converts_to_markdown()
    {
        $markdown = '**bold** ~~strike~~';

        Markdown::extend('custom', function ($parser) {
            return $parser;
        });

        // the default parser includes support for strikethrough
        $this->assertEquals("<p><strong>bold</strong> <del>strike</del></p>\n", $this->modify($markdown));
        $this->assertEquals("<p><strong>bold</strong> <del>strike</del></p>\n", $this->modify($markdown, 'default'));

        // the custom one doesnt
        $this->assertEquals("<p><strong>bold</strong> ~~strike~~</p>\n", $this->modify($markdown, 'custom'));
    }

    /** @test */
    public function it_converts_to_markdown_with_front_matter()
    {
        $markdown = <<<'EOT'
---
title: Foo Bar
---

# Heading
EOT;

        Markdown::extend('front_matter', function ($parser) {
            return $parser->withFrontMatter();
        });

        $result = $this->modify($markdown, ['front_matter', true]);

        $this->assertEquals(
            '<h1>Heading</h1>',
            rtrim($result['content'])
        );
        $this->assertEquals(
            'Foo Bar',
            $result['title']
        );
    }

    /** @test */
    public function using_an_unknown_parser_throws_exception()
    {
        $this->expectExceptionMessage('Markdown parser [foo] is not defined.');

        $this->modify('**words**', 'foo');
    }

    public function modify($value, $params = [])
    {
        return Modify::value($value)->markdown($params)->fetch();
    }
}
