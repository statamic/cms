<?php

namespace Tests\Tags;

use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Parse;
use Statamic\Tags\Svg;
use Stringy\StaticStringy;
use Tests\TestCase;

class SvgTagTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        File::copy(__DIR__.'/../../resources/svg/icons/light/users.svg', resource_path('users.svg'));
    }

    private function tag($tag, $variables = [])
    {
        return Parse::template($tag, $variables, trusted: true);
    }

    #[Test]
    public function it_renders_svg()
    {
        $this->assertStringStartsWith('<svg xmlns="', $this->tag('{{ svg:users }}'));
        $this->assertStringStartsWith('<svg xmlns="', $this->tag('{{ svg src="users" }}'));
    }

    #[Test]
    public function it_renders_svg_with_additional_params()
    {
        $this->assertStringStartsWith('<svg class="mb-2" xmlns="', $this->tag('{{ svg src="users" sanitize="false" class="mb-2" }}'));
    }

    #[Test]
    public function it_sanitizes()
    {
        File::put(resource_path('xss.svg'), <<<'SVG'
<svg>
    <path onload="loadxss" onclick="clickxss" />
    <script>alert("xss")</script>
    <foreignObject/>
    <mesh/>
</svg>
SVG);

        $this->assertEquals(
            '<svg><path/></svg>',
            $this->tag('{{ svg src="xss" }}')
        );

        $this->assertEquals(
            '<svg><path onclick="clickxss"/><foreignObject/><mesh/></svg>',
            $this->tag('{{ svg src="xss" allow_tags="mesh|foreignObject" allow_attrs="onclick" }}')
        );
    }

    #[Test]
    public function sanitizing_doesnt_add_xml_tag()
    {
        // We want to make sure if there wasn't one to begin with, it doesn't add one.

        $svg = '<svg><path/></svg>';

        File::put(resource_path('xmltag.svg'), $svg);

        $this->assertEquals($svg, $this->tag('{{ svg src="xmltag" sanitize="true" }}'));
    }

    #[Test]
    public function sanitizing_removes_an_xml_tag()
    {
        // The declaration is meaningless once the svg is inlined into an html document,
        // and keeping it meant trusting an attacker controlled "<?xml" prefix.

        File::put(resource_path('xmltag.svg'), '<?xml version="1.0" encoding="UTF-8"?><svg><path/></svg>');

        $this->assertEquals('<svg><path/></svg>', $this->tag('{{ svg src="xmltag" }}'));
    }

    #[Test]
    #[DataProvider('xmlStylesheetProvider')]
    public function sanitizing_removes_xml_stylesheet_processing_instructions($svg)
    {
        File::put(resource_path('stylesheet.svg'), $svg);

        $this->assertStringNotContainsString('xml-stylesheet', $this->tag('{{ svg src="stylesheet" }}'));
    }

    public static function xmlStylesheetProvider()
    {
        $pi = '<?xml-stylesheet type="text/xsl" href="data:text/xml;base64,PHhzbDpzdHlsZXNoZWV0Lz4="?>';

        return [
            'on its own' => [$pi.'<svg><path/></svg>'],
            'after a declaration' => ['<?xml version="1.0"?>'.$pi.'<svg><path/></svg>'],
            'on separate lines' => ['<?xml version="1.0"?>'."\n".$pi."\n".'<svg><path/></svg>'],
        ];
    }

    #[Test]
    public function sanitizing_doesnt_remove_additional_params()
    {
        $this->assertStringStartsWith('<svg x-ref="svg" xmlns="', $this->tag('{{ svg src="users" x-ref="svg" }}'));
    }

    #[Test]
    public function sanitization_can_be_disabled()
    {
        $rawSvg = StaticStringy::collapseWhitespace(<<<'SVG'
<svg>
    <path onload="loadxss" onclick="clickxss" />
    <script>alert("xss")</script>
    <foreignObject/>
    <mesh/>
</svg>
SVG);
        File::put(resource_path('xss.svg'), $rawSvg);

        Svg::disableSanitization();

        $this->assertEquals($rawSvg, $this->tag('{{ svg src="xss" }}'));

        // If it's globally disabled, you can still opt into it per-tag.
        $this->assertEquals('<svg><path/></svg>', $this->tag('{{ svg src="xss" sanitize="true" }}'));

        Svg::enableSanitization();

        $this->assertEquals('<svg><path/></svg>', $this->tag('{{ svg src="xss" }}'));
    }

    #[Test]
    public function fails_gracefully_when_src_is_empty()
    {
        $output = $this->tag('{{ svg :src="icon" }}', [
            'icon' => null,
        ]);

        $this->assertEmpty((string) $output);
    }
}
