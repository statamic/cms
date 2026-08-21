<?php

namespace Tests\Support;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Support\Svg;
use Tests\TestCase;

class SvgTest extends TestCase
{
    #[Test]
    #[DataProvider('sanitizeCssProvider')]
    public function it_sanitizes_css(string $input, string $expected)
    {
        $this->assertSame($expected, trim(Svg::sanitizeCss($input)));
    }

    public static function sanitizeCssProvider()
    {
        return [
            'strips @import with url()' => [
                '@import url("https://evil.com/x.css");',
                '',
            ],
            'strips @import with bare string' => [
                '@import "https://evil.com/x.css";',
                '',
            ],
            'strips @import with protocol-relative url' => [
                '@import url(//evil.com/x.css);',
                '',
            ],
            'strips @import without semicolon' => [
                "@import url('https://evil.com/x.css')",
                '',
            ],
            'strips @import using hex escapes' => [
                '@\\69mport url("https://evil.com/x.css");',
                '',
            ],
            'strips @import using non-hex backslash escapes' => [
                '@\import url("https://evil.com/x.css");',
                '',
            ],
            'strips @import using mixed hex and non-hex escapes' => [
                '@\\69\mport url("https://evil.com/x.css");',
                '',
            ],
            'neutralizes external url' => [
                '.cls { background: url(https://evil.com/beacon.gif); }',
                '.cls { background: url(); }',
            ],
            'neutralizes protocol-relative url' => [
                '.cls { background: url(//evil.com/x); }',
                '.cls { background: url(); }',
            ],
            'neutralizes quoted external url' => [
                '.cls { background: url("http://evil.com/x"); }',
                '.cls { background: url(); }',
            ],
            'neutralizes external url using hex escapes' => [
                '.cls { background: url(\\68\\74\\74\\70\\73://evil.com/beacon.gif); }',
                '.cls { background: url(); }',
            ],
            'neutralizes external url using non-hex backslash escapes' => [
                '.cls { background: url(\https://evil.com/x); }',
                '.cls { background: url(); }',
            ],
            'neutralizes external url using non-breaking space escape' => [
                '.cls { background: url(\\a0 https://evil.com/x); }',
                '.cls { background: url(); }',
            ],
            'neutralizes external url using zero-width space escape' => [
                '.cls { background: url(\\200B https://evil.com/x); }',
                '.cls { background: url(); }',
            ],
            'neutralizes external url using BOM escape' => [
                '.cls { background: url(\\FEFF https://evil.com/x); }',
                '.cls { background: url(); }',
            ],
            'neutralizes external url in @font-face src' => [
                '@font-face { font-family: "x"; src: url("https://evil.com/font.woff"); }',
                '@font-face { font-family: "x"; src: url(); }',
            ],
            'preserves normal css' => [
                '.cls-1 { fill: #333; stroke: red; }',
                '.cls-1 { fill: #333; stroke: red; }',
            ],
            'preserves internal url references' => [
                '.cls { fill: url(#myGradient); }',
                '.cls { fill: url(#myGradient); }',
            ],
            'preserves data uris' => [
                '.cls { background: url(data:image/png;base64,abc123); }',
                '.cls { background: url(data:image/png;base64,abc123); }',
            ],
            'handles mixed legitimate and malicious css' => [
                ".cls-1 { fill: #333; }\n@import url(\"https://evil.com/track.css\");\n.cls-2 { stroke: url(#grad); background: url(https://evil.com/bg.gif); }",
                ".cls-1 { fill: #333; }\n\n.cls-2 { stroke: url(#grad); background: url(); }",
            ],
        ];
    }

    #[Test]
    public function it_sanitizes_style_tags_in_full_svg()
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><style>@import url("https://evil.com/track.css"); .cls-1 { fill: #333; }</style><rect class="cls-1"/></svg>';

        $result = Svg::sanitize($svg);

        $this->assertStringNotContainsString('@import', $result);
        $this->assertStringNotContainsString('evil.com', $result);
        $this->assertStringContainsString('.cls-1', $result);
        $this->assertStringContainsString('fill:', $result);
    }

    #[Test]
    public function it_passes_through_svg_without_style_tags()
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><rect width="1" height="1" fill="white"/></svg>';

        $result = Svg::sanitize($svg);

        $this->assertStringContainsString('<rect', $result);
        $this->assertStringContainsString('<svg', $result);
    }

    #[Test]
    public function it_preserves_xml_declaration()
    {
        $svg = '<?xml version="1.0" encoding="UTF-8"?><svg xmlns="http://www.w3.org/2000/svg"><rect/></svg>';

        $result = Svg::sanitize($svg);

        $this->assertStringStartsWith('<?xml', $result);
    }

    #[Test]
    public function it_does_not_add_xml_declaration()
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><rect/></svg>';

        $result = Svg::sanitize($svg);

        $this->assertStringStartsWith('<svg', $result);
    }

    #[Test]
    public function it_sanitizes_css_inside_cdata_sections()
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><style><![CDATA[@import url("https://evil.com/track.css"); .cls-1 { fill: url(https://evil.com/bg.gif); }]]></style><rect class="cls-1"/></svg>';

        $result = Svg::sanitize($svg);

        $this->assertStringNotContainsString('@import', $result);
        $this->assertStringNotContainsString('evil.com', $result);
        $this->assertStringContainsString('.cls-1', $result);
        $this->assertStringContainsString('fill:', $result);
    }
}
