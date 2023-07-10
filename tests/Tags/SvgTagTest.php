<?php

namespace Tests\Tags;

use Illuminate\Support\Facades\File;
use Statamic\Facades\Parse;
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
        return Parse::template($tag, []);
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
    <path onload="loadxss" onclick="clickxss" />
    <script>alert("xss")</script>
    <foreignObject/>
    <mesh/>
</svg>
SVG);

        $this->assertEquals(
            '<svg><path/></svg>',
            $this->tag('{{ svg src="xss" sanitize="true" }}')
        );

        $this->assertEquals(
            '<svg><path onclick="clickxss"/><foreignObject/><mesh/></svg>',
            $this->tag('{{ svg src="xss" sanitize="true" allow_tags="mesh|foreignObject" allow_attrs="onclick" }}')
        );
    }

    /** @test */
    public function sanitizing_doesnt_add_xml_tag()
    {
        // We want to make sure if there wasn't one to begin with, it doesn't add one.

        $svg = '<svg><path/></svg>';

        File::put(resource_path('xmltag.svg'), $svg);

        $this->assertEquals($svg, $this->tag('{{ svg src="xmltag" sanitize="true" }}'));
    }

    /** @test */
    public function sanitizing_doesnt_remove_an_xml_tag()
    {
        // We want to make sure that we haven't configured it to remove it if we wanted it there to begin with.

        $svg = '<?xml version="1.0" encoding="UTF-8"?><svg><path/></svg>';

        File::put(resource_path('xmltag.svg'), $svg);

        $this->assertEquals($svg, $this->tag('{{ svg src="xmltag" sanitize="true" }}'));
    }
}
