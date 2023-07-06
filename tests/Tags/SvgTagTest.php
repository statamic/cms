<?php

namespace Tests\Tags;

use Illuminate\Support\Facades\File;
use Statamic\Facades\Parse;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\TestCase;

class SvgTagTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        File::copy(__DIR__.'/../../resources/svg/icons/light/users.svg', resource_path('users.svg'));
    }

    private function tag($tag)
    {
        $output = Parse::template($tag, []);

        // Normalize whitespace and line breaks for testing ease.
        return trim(StringUtilities::normalizeLineEndings($output));
    }

    /** @test */
    public function it_renders_svg()
    {
        $this->assertStringStartsWith('<svg xmlns="', $this->tag('{{ svg:users }}'));
        $this->assertStringStartsWith('<svg xmlns="', $this->tag('{{ svg src="users" }}'));
    }

    /** @test */
    public function it_renders_svg_with_additional_params()
    {
        $this->assertStringStartsWith('<svg class="mb-2" xmlns="', $this->tag('{{ svg src="users" class="mb-2" }}'));
    }

    /** @test */
    public function it_sanitizes()
    {
        File::put(resource_path('xss.svg'), <<<'SVG'
<svg>
    <path onload="loadxss" onclick="clickxss"></path>
    <script>alert("xss")</script>
    <foreignObject></foreignObject>
    <mesh></mesh>
</svg>
SVG);

        $this->assertEquals(<<<'SVG'
<svg>
  <path></path>
</svg>
SVG,
            $this->tag('{{ svg src="xss" sanitize="true" }}')
        );

        $this->assertEquals(<<<'SVG'
<svg>
  <path onclick="clickxss"></path>
  <foreignObject></foreignObject>
  <mesh></mesh>
</svg>
SVG,
            $this->tag('{{ svg src="xss" sanitize="true" allow_tags="mesh|foreignObject" allow_attrs="onclick" }}')
        );
    }

    /** @test */
    public function sanitizing_doesnt_add_xml_tag()
    {
        // Thes sanitizer package adds an xml tag by default.
        // We want to make sure if there wasn't one to begin with, it doesn't add one.

        $svg = <<<'SVG'
<svg>
  <path></path>
</svg>
SVG;

        File::put(resource_path('xmltag.svg'), $svg);

        $this->assertEquals($svg, $this->tag('{{ svg src="xmltag" sanitize="true" }}'));
    }

    /** @test */
    public function sanitizing_doesnt_remove_an_xml_tag()
    {
        // Thes sanitizer package adds an xml tag by default.
        // We want to make sure that we haven't configured it to remove it if we wanted it there to begin with.

        $svg = <<<'SVG'
<?xml version="1.0" encoding="UTF-8"?>
<svg>
  <path></path>
</svg>
SVG;

        File::put(resource_path('xmltag.svg'), $svg);

        $this->assertEquals($svg, $this->tag('{{ svg src="xmltag" sanitize="true" }}'));
    }
}
