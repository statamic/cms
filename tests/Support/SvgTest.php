<?php

namespace Tests\Support;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Support\Svg;
use Tests\TestCase;

class SvgTest extends TestCase
{
    #[Test]
    public function it_strips_import_with_url()
    {
        $this->assertSame('', trim(Svg::sanitizeCss('@import url("https://evil.com/x.css");')));
    }

    #[Test]
    public function it_strips_import_with_bare_string()
    {
        $this->assertSame('', trim(Svg::sanitizeCss('@import "https://evil.com/x.css";')));
    }

    #[Test]
    public function it_strips_import_with_protocol_relative_url()
    {
        $this->assertSame('', trim(Svg::sanitizeCss('@import url(//evil.com/x.css);')));
    }

    #[Test]
    public function it_strips_import_without_semicolon()
    {
        $this->assertSame('', trim(Svg::sanitizeCss("@import url('https://evil.com/x.css')")));
    }

    #[Test]
    public function it_neutralizes_external_url_in_property()
    {
        $this->assertSame(
            '.cls { background: url(); }',
            Svg::sanitizeCss('.cls { background: url(https://evil.com/beacon.gif); }')
        );
    }

    #[Test]
    public function it_neutralizes_protocol_relative_url_in_property()
    {
        $this->assertSame(
            '.cls { background: url(); }',
            Svg::sanitizeCss('.cls { background: url(//evil.com/x); }')
        );
    }

    #[Test]
    public function it_neutralizes_quoted_external_url()
    {
        $this->assertSame(
            '.cls { background: url(); }',
            Svg::sanitizeCss('.cls { background: url("http://evil.com/x"); }')
        );
    }

    #[Test]
    public function it_preserves_normal_css()
    {
        $css = '.cls-1 { fill: #333; stroke: red; }';

        $this->assertSame($css, Svg::sanitizeCss($css));
    }

    #[Test]
    public function it_preserves_internal_url_references()
    {
        $css = '.cls { fill: url(#myGradient); }';

        $this->assertSame($css, Svg::sanitizeCss($css));
    }

    #[Test]
    public function it_preserves_data_uris()
    {
        $css = '.cls { background: url(data:image/png;base64,abc123); }';

        $this->assertSame($css, Svg::sanitizeCss($css));
    }

    #[Test]
    public function it_handles_mixed_legitimate_and_malicious_css()
    {
        $css = ".cls-1 { fill: #333; }\n@import url(\"https://evil.com/track.css\");\n.cls-2 { stroke: url(#grad); background: url(https://evil.com/bg.gif); }";
        $expected = ".cls-1 { fill: #333; }\n\n.cls-2 { stroke: url(#grad); background: url(); }";

        $this->assertSame($expected, Svg::sanitizeCss($css));
    }

    #[Test]
    public function it_strips_font_face_with_external_src()
    {
        $css = '@font-face { font-family: "x"; src: url("https://evil.com/font.woff"); }';
        $expected = '@font-face { font-family: "x"; src: url(); }';

        $this->assertSame($expected, Svg::sanitizeCss($css));
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
