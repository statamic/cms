<?php

namespace Tests\Tags;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Parse;
use Tests\TestCase;

class SpacelessTest extends TestCase
{
    private function tag($tag, $data = [])
    {
        return (string) Parse::template($tag, $data, trusted: true);
    }

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
}
