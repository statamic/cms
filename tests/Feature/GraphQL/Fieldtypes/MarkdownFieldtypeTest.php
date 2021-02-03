<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

/** @group graphql */
class MarkdownFieldtypeTest extends FieldtypeTestCase
{
    /** @test */
    public function it_gets_markdown()
    {
        $this->createEntryWithFields([
            'filled' => [
                'value' => "# Heading\nParagraph",
                'field' => ['type' => 'markdown'],
            ],
            'undefined' => [
                'value' => null,
                'field' => ['type' => 'markdown'],
            ],
        ]);

        $query = <<<'GQL'
default: filled
as_markdown: filled(format: "markdown")
as_html: filled(format: "html")
undefined
GQL;

        $this->assertGqlEntryHas($query, [
            'default' => $html = "<h1>Heading</h1>\n<p>Paragraph</p>\n",
            'as_markdown' => "# Heading\nParagraph",
            'as_html' => $html,
            'undefined' => null,
        ]);
    }
}
