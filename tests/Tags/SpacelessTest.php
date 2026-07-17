<?php

namespace Tests\Tags;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Parse;
use Tests\TestCase;

class SpacelessTest extends TestCase
{
    #[Test]
    public function it_strips_whitespace_between_tags()
    {
        $html = '<ul>
            <li>One</li>
            <li>Two</li>
        </ul>';

        $this->assertEquals(
            '<ul><li>One</li><li>Two</li></ul>',
            $this->tag('{{ spaceless }}'.$html.'{{ /spaceless }}')
        );
    }

    #[Test]
    public function it_does_not_glue_words_separated_by_a_line_break()
    {
        $html = "<p>\nHello\nWorld\n</p>";

        $this->assertEquals(
            '<p>Hello World</p>',
            $this->tag('{{ spaceless }}'.$html.'{{ /spaceless }}')
        );
    }

    #[Test]
    public function it_does_not_corrupt_an_attribute_value_containing_a_greater_than_sign()
    {
        $html = '<div title="a > b">  <span>Hi</span>  </div>';

        $this->assertEquals(
            '<div title="a > b"><span>Hi</span></div>',
            $this->tag('{{ spaceless }}'.$html.'{{ /spaceless }}')
        );
    }

    #[Test]
    public function it_leaves_script_content_untouched()
    {
        $html = "<script>\n// comment\nalert(1);\n</script>";

        $this->assertEquals(
            $html,
            $this->tag('{{ spaceless }}'.$html.'{{ /spaceless }}')
        );
    }

    #[Test]
    public function it_leaves_pre_content_untouched()
    {
        $html = "<pre>function foo() {\n    return 1;\n}</pre>";

        $this->assertEquals(
            $html,
            $this->tag('{{ spaceless }}'.$html.'{{ /spaceless }}')
        );
    }

    #[Test]
    public function it_leaves_style_content_untouched()
    {
        $html = "<style>\n.foo {\n    color: hotpink;\n}\n</style>";

        $this->assertEquals(
            $html,
            $this->tag('{{ spaceless }}'.$html.'{{ /spaceless }}')
        );
    }

    #[Test]
    public function it_leaves_textarea_content_untouched()
    {
        $html = "<textarea>\n    Some   text.\n</textarea>";

        $this->assertEquals(
            $html,
            $this->tag('{{ spaceless }}'.$html.'{{ /spaceless }}')
        );
    }

    #[Test]
    public function it_protects_elements_regardless_of_tag_name_case()
    {
        $html = "<SCRIPT>\nalert(1);\n</SCRIPT>";

        $this->assertEquals(
            $html,
            $this->tag('{{ spaceless }}'.$html.'{{ /spaceless }}')
        );
    }

    #[Test]
    public function it_still_strips_whitespace_around_a_protected_element()
    {
        $html = "<div>\n    <script>\n    alert(1);\n    </script>\n</div>";

        $this->assertEquals(
            "<div><script>\n    alert(1);\n    </script></div>",
            $this->tag('{{ spaceless }}'.$html.'{{ /spaceless }}')
        );
    }

    #[Test]
    public function it_restores_multiple_protected_elements_independently()
    {
        $html = "<script>\n    var a = 1;\n</script>\n<pre>   x   y   </pre>";

        $this->assertEquals(
            "<script>\n    var a = 1;\n</script> <pre>   x   y   </pre>",
            $this->tag('{{ spaceless }}'.$html.'{{ /spaceless }}')
        );
    }

    #[Test]
    public function it_handles_an_attribute_value_containing_a_single_quote()
    {
        $html = "<div title='a > b'>  <span>Hi</span>  </div>";

        $this->assertEquals(
            "<div title='a > b'><span>Hi</span></div>",
            $this->tag('{{ spaceless }}'.$html.'{{ /spaceless }}')
        );
    }

    #[Test]
    public function it_strips_whitespace_around_self_closing_tags()
    {
        $html = '<div>  <img src="foo.jpg" />  <br />  </div>';

        $this->assertEquals(
            '<div><img src="foo.jpg" /><br /></div>',
            $this->tag('{{ spaceless }}'.$html.'{{ /spaceless }}')
        );
    }

    #[Test]
    public function it_collapses_tabs_and_multiple_spaces_within_text()
    {
        $html = "<p>Hello\t\t  World</p>";

        $this->assertEquals(
            '<p>Hello World</p>',
            $this->tag('{{ spaceless }}'.$html.'{{ /spaceless }}')
        );
    }

    #[Test]
    public function it_strips_whitespace_after_an_opening_tag_and_before_a_closing_tag()
    {
        $html = '<div>   Hello   </div>';

        $this->assertEquals(
            '<div>Hello</div>',
            $this->tag('{{ spaceless }}'.$html.'{{ /spaceless }}')
        );
    }

    #[Test]
    public function it_leaves_whitespace_between_attributes_untouched()
    {
        $html = "<div\n    class=\"foo\"\n    id=\"bar\">Hi</div>";

        $this->assertEquals(
            $html,
            $this->tag('{{ spaceless }}'.$html.'{{ /spaceless }}')
        );
    }

    #[Test]
    public function it_does_not_glue_prose_around_an_inline_tag()
    {
        $html = '<p>Check out <a href="#">this link</a> for more info.</p>';

        $this->assertEquals(
            $html,
            $this->tag('{{ spaceless }}'.$html.'{{ /spaceless }}')
        );
    }

    #[Test]
    public function it_returns_an_empty_string_when_content_is_only_whitespace()
    {
        $html = "   \n   \t  ";

        $this->assertEquals(
            '',
            $this->tag('{{ spaceless }}'.$html.'{{ /spaceless }}')
        );
    }

    private function tag($tag): string
    {
        $data = [];

        return (string) Parse::template($tag, $data, trusted: true);
    }
}
